<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\StripeWebhookEvent;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhook notifications with durable idempotency.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        // 1. Signature Verification
        if (!empty($webhookSecret) && $webhookSecret !== 'whsec_your_webhook_secret') {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } catch (\UnexpectedValueException $e) {
                Log::error("Stripe Webhook Invalid Payload: " . $e->getMessage());
                return response()->json(['error' => 'Invalid payload'], 400);
            } catch (SignatureVerificationException $e) {
                Log::error("Stripe Webhook Signature Verification Failed: " . $e->getMessage());
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        } else {
            // Development / test fallback when secret is unset
            $eventData = json_decode($payload, true);
            if (!$eventData || !isset($eventData['type'])) {
                return response()->json(['error' => 'Malformed payload'], 400);
            }
            $event = (object) [
                'id'   => $eventData['id'] ?? null,
                'type' => $eventData['type'],
                'data' => (object) [
                    'object' => json_decode(json_encode($eventData['data']['object'] ?? [])),
                ],
            ];
            Log::info("Stripe Webhook processed in local environment mode.");
        }

        $eventId = $event->id ?? null;
        $eventType = $event->type ?? 'unknown';

        Log::info("Stripe Webhook Received: {$eventType} [Event ID: {$eventId}]");

        // 2. Durable Idempotency Check
        if ($eventId && StripeWebhookEvent::hasBeenProcessed($eventId)) {
            Log::info("Stripe Webhook {$eventId} ({$eventType}) already processed. Skipping.");
            return response()->json([
                'status'   => 'already_processed',
                'event_id' => $eventId,
            ], 200);
        }

        // 3. Process Specific Event Types
        switch ($eventType) {
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
                $session = $event->data->object;
                $orderReference = $session->metadata->order_reference ?? $session->client_reference_id ?? null;

                if ($orderReference) {
                    $order = Order::where('order_reference', $orderReference)->first();
                    if ($order) {
                        $paymentReference = $session->payment_intent ?? $session->id;
                        OrderFulfillmentService::fulfill($order, $paymentReference, (array) $session);
                        Log::info("Webhook fulfilled order: {$orderReference}");
                    } else {
                        Log::warning("Webhook received for unknown order reference: {$orderReference}");
                    }
                }
                break;

            case 'payment_intent.succeeded':
                $intent = $event->data->object;
                $orderReference = $intent->metadata->order_reference ?? null;

                if ($orderReference) {
                    $order = Order::where('order_reference', $orderReference)->first();
                    if ($order) {
                        OrderFulfillmentService::fulfill($order, $intent->id, (array) $intent);
                        Log::info("Webhook payment_intent.succeeded fulfilled order: {$orderReference}");
                    }
                }
                break;

            case 'payment_intent.payment_failed':
            case 'checkout.session.async_payment_failed':
                $object = $event->data->object;
                $orderReference = $object->metadata->order_reference ?? $object->client_reference_id ?? null;

                if ($orderReference) {
                    $order = Order::where('order_reference', $orderReference)->first();
                    if ($order && $order->payment_status !== 'paid') {
                        $errorMessage = $object->last_payment_error->message ?? 'Card payment was declined.';
                        $order->update([
                            'payment_status' => 'failed',
                            'cancel_reason'  => $errorMessage,
                        ]);
                        Log::warning("Payment failed for order {$orderReference}: {$errorMessage}");
                    }
                }
                break;

            case 'charge.refunded':
                $charge = $event->data->object;
                $paymentRef = $charge->payment_intent ?? $charge->id ?? null;

                if ($paymentRef) {
                    $payment = Payment::where('payment_reference', $paymentRef)->with('order')->first();
                    if ($payment && $payment->order) {
                        $payment->update(['status' => 'refunded']);
                        $payment->order->update([
                            'payment_status' => 'refunded',
                            'status'         => Order::STATUS_CANCELLED,
                            'cancel_reason'  => 'Payment refunded via Stripe.',
                        ]);
                        Log::info("Webhook marked order as refunded: {$payment->order->order_reference}");
                    }
                }
                break;

            case 'charge.dispute.created':
                $dispute = $event->data->object;
                $chargeId = $dispute->charge ?? null;
                Log::warning("Stripe Dispute Created for charge: {$chargeId}, dispute ID: {$dispute->id}");

                if ($chargeId) {
                    $payment = Payment::where('payment_reference', $chargeId)->with('order')->first();
                    if ($payment && $payment->order) {
                        $payment->update(['status' => 'disputed']);
                        Log::warning("Order {$payment->order->order_reference} flagged with payment dispute.");
                    }
                }
                break;

            default:
                Log::info("Stripe Webhook unhandled event type: {$eventType}");
                break;
        }

        // 4. Record Event in Durable Idempotency Table
        if ($eventId) {
            try {
                StripeWebhookEvent::recordProcessed($eventId, $eventType, (array) ($event->data->object ?? []));
            } catch (\Throwable $e) {
                Log::warning("Failed to record webhook event {$eventId}: " . $e->getMessage());
            }
        }

        return response()->json([
            'status'   => 'success',
            'event_id' => $eventId,
        ], 200);
    }
}
