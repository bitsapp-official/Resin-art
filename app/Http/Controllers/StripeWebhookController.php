<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhook notifications.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        // Verify webhook signature if secret is configured
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
            // If webhook secret not configured yet in local test, parse JSON payload directly
            $eventData = json_decode($payload, true);
            if (!$eventData || !isset($eventData['type'])) {
                return response()->json(['error' => 'Malformed payload'], 400);
            }
            $event = (object) [
                'type' => $eventData['type'],
                'data' => (object) [
                    'object' => json_decode(json_encode($eventData['data']['object'] ?? [])),
                ],
            ];
            Log::info("Stripe Webhook processed without signature verification (local development mode).");
        }

        Log::info("Stripe Webhook Event Received: " . $event->type);

        switch ($event->type) {
            case 'checkout.session.completed':
            case 'checkout.session.async_payment_succeeded':
                $session = $event->data->object;

                // Extract order reference from metadata or client_reference_id
                $orderReference = $session->metadata->order_reference ?? $session->client_reference_id ?? null;

                if ($orderReference) {
                    $order = Order::where('order_reference', $orderReference)->first();

                    if ($order) {
                        $paymentReference = $session->payment_intent ?? $session->id;
                        OrderFulfillmentService::fulfill($order, $paymentReference, (array) $session);
                        Log::info("Webhook successfully fulfilled order: {$orderReference} (Event: {$event->type})");
                    } else {
                        Log::warning("Webhook received for unknown order reference: {$orderReference}");
                    }
                }
                break;

            case 'checkout.session.async_payment_failed':
                $session = $event->data->object;
                $orderReference = $session->metadata->order_reference ?? $session->client_reference_id ?? null;

                if ($orderReference) {
                    $order = Order::where('order_reference', $orderReference)->first();
                    if ($order && $order->payment_status !== 'paid') {
                        $order->update([
                            'payment_status' => 'failed',
                            'cancel_reason'  => 'Asynchronous bank payment failed or expired.',
                        ]);
                        Log::warning("Async payment failed for order {$orderReference}");
                    }
                }
                break;

            case 'payment_intent.payment_failed':
                $intent = $event->data->object;
                $orderReference = $intent->metadata->order_reference ?? null;

                if ($orderReference) {
                    $order = Order::where('order_reference', $orderReference)->first();
                    if ($order && $order->payment_status !== 'paid') {
                        $order->update([
                            'payment_status' => 'failed',
                            'cancel_reason'  => $intent->last_payment_error->message ?? 'Card payment declined by issuer.',
                        ]);
                        Log::warning("Payment intent failed for order {$orderReference}: " . ($intent->last_payment_error->message ?? 'Unknown error'));
                    }
                }
                break;

            case 'charge.refunded':
                $charge = $event->data->object;
                $paymentIntentId = $charge->payment_intent ?? $charge->id ?? null;

                if ($paymentIntentId) {
                    $payment = \App\Models\Payment::where('payment_reference', $paymentIntentId)->with('order')->first();
                    if ($payment && $payment->order) {
                        $payment->update(['status' => 'refunded']);
                        $payment->order->update(['payment_status' => 'refunded']);
                        Log::info("Webhook successfully marked order as refunded: {$payment->order->order_reference}");
                    }
                }
                break;

            default:
                Log::info("Stripe Webhook unhandled event type: " . $event->type);
                break;
        }

        return response()->json(['status' => 'success'], 200);
    }
}
