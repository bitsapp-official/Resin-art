<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\RefundRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeRefundService
{
    /**
     * Process an actual refund via Stripe API for a given RefundRequest record.
     *
     * @param RefundRequest $refundRequest
     * @return array
     * @throws \Exception
     */
    public static function processRefund(RefundRequest $refundRequest): array
    {
        // 1. Idempotency: If already refunded with a Stripe refund ID, do not repeat
        if (!empty($refundRequest->stripe_refund_id) && $refundRequest->status === 'COMPLETED') {
            Log::info("RefundRequest #{$refundRequest->id} already processed via Stripe ID: {$refundRequest->stripe_refund_id}.");
            return [
                'success'          => true,
                'already_refunded' => true,
                'stripe_refund_id' => $refundRequest->stripe_refund_id,
            ];
        }

        // 2. Strict Business Rule: Enforce Admin Approval before processing online Stripe refund
        if ($refundRequest->status !== 'APPROVED') {
            throw new \Exception("Refund cannot be processed: Refund Request #{$refundRequest->id} must be APPROVED by Admin first (current status: {$refundRequest->status}).");
        }

        $order = $refundRequest->order;
        if (!$order) {
            throw new \Exception("Cannot process refund: No linked order found for RefundRequest #{$refundRequest->id}.");
        }

        $refundAmount = (float) $refundRequest->amount;
        if ($refundAmount <= 0) {
            throw new \Exception("Invalid refund amount: ₹{$refundAmount}. Amount must be greater than zero.");
        }

        // 2. Identify the authoritative Stripe payment identifier
        $paymentReference = self::resolveStripePaymentReference($order);
        if (!$paymentReference) {
            throw new \Exception("Cannot process refund: Order {$order->order_reference} does not have a valid Stripe payment reference.");
        }

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            throw new \Exception("Stripe secret key is not configured in environment.");
        }

        $stripe = app()->bound(StripeClient::class)
            ? app(StripeClient::class)
            : new StripeClient($stripeSecret);

        // 3. Resolve to payment_intent or charge
        $refundParams = [
            'amount'   => intval(round($refundAmount * 100)), // in smallest currency unit (paise)
            'reason'   => 'requested_by_customer',
            'metadata' => [
                'order_reference'   => $order->order_reference,
                'refund_request_id' => (string) $refundRequest->id,
            ],
        ];

        if (str_starts_with($paymentReference, 'pi_')) {
            $refundParams['payment_intent'] = $paymentReference;
        } elseif (str_starts_with($paymentReference, 'ch_')) {
            $refundParams['charge'] = $paymentReference;
        } elseif (str_starts_with($paymentReference, 'cs_')) {
            // Retrieve Checkout Session to resolve its PaymentIntent
            $session = $stripe->checkout->sessions->retrieve($paymentReference);
            if (!empty($session->payment_intent)) {
                $refundParams['payment_intent'] = $session->payment_intent;
            } else {
                throw new \Exception("Stripe Checkout Session {$paymentReference} does not contain an associated PaymentIntent.");
            }
        } else {
            $refundParams['payment_intent'] = $paymentReference;
        }

        // 4. Call Stripe Refund API with idempotency key
        try {
            $stripeRefund = $stripe->refunds->create(
                $refundParams,
                ['idempotency_key' => 'refund_req_' . $refundRequest->id . '_' . intval(round($refundAmount * 100))]
            );
        } catch (\Exception $e) {
            Log::error("Stripe Refund API failed for Order {$order->order_reference}: " . $e->getMessage());
            throw new \Exception("Stripe Refund Failed: " . $e->getMessage());
        }

        // 5. Update Database atomically only upon verified Stripe success
        DB::transaction(function () use ($refundRequest, $order, $stripeRefund, $refundAmount) {
            $refundRequest->update([
                'status'               => 'COMPLETED',
                'stripe_refund_id'     => $stripeRefund->id,
                'stripe_refund_status' => $stripeRefund->status,
                'refunded_at'          => now(),
                'admin_notes'          => trim(($refundRequest->admin_notes ? $refundRequest->admin_notes . "\n" : '') . "Stripe Refund ID: {$stripeRefund->id} (Status: {$stripeRefund->status})"),
            ]);

            // Update Payment records
            Payment::where('order_id', $order->id)->update([
                'status' => 'refunded',
            ]);

            // If full refund, mark order payment_status as refunded
            $order->update([
                'payment_status' => 'refunded',
                'status'         => Order::STATUS_CANCELLED,
                'canceled_at'    => $order->canceled_at ?? now(),
                'cancel_reason'  => $order->cancel_reason ?? 'Order cancelled and refunded via Stripe.',
            ]);

            // Customer in-app notification
            if ($order->user_id) {
                try {
                    \App\Models\CustomerNotification::create([
                        'user_id' => $order->user_id,
                        'title'   => 'Refund Processed via Stripe',
                        'message' => "Your refund of ₹" . number_format($refundAmount, 2) . " for order {$order->order_reference} has been issued via Stripe (Refund ID: {$stripeRefund->id}).",
                        'type'    => 'order',
                        'data'    => ['order_reference' => $order->order_reference],
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("Customer in-app notification error on refund: " . $e->getMessage());
                }
            }

            Log::info("Order {$order->order_reference} successfully refunded via Stripe ID {$stripeRefund->id} for ₹{$refundAmount}.");
        });

        return [
            'success'          => true,
            'stripe_refund_id' => $stripeRefund->id,
            'status'           => $stripeRefund->status,
            'amount'           => $refundAmount,
        ];
    }

    /**
     * Resolve the Stripe reference from order or payments table.
     */
    private static function resolveStripePaymentReference(Order $order): ?string
    {
        if (!empty($order->payment_reference)) {
            return $order->payment_reference;
        }

        $payment = Payment::where('order_id', $order->id)
            ->where('provider', 'stripe')
            ->orderBy('id', 'desc')
            ->first();

        return $payment?->payment_reference;
    }
}
