<?php

namespace App\Services;

use App\Mail\OrderConfirmationMail;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderFulfillmentService
{
    /**
     * Fulfill an order idempotently upon verified payment confirmation.
     *
     * @param Order $order
     * @param string $paymentReference Stripe Payment Intent ID or Session ID
     * @param array $paymentPayload Raw gateway payload metadata
     * @return Order
     */
    public static function fulfill(Order $order, string $paymentReference, array $paymentPayload = []): Order
    {
        return DB::transaction(function () use ($order, $paymentReference, $paymentPayload) {
            // 1. Lock order row to prevent race conditions across webhook & sync redirect
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if (!$lockedOrder) {
                return $order;
            }

            // 2. Idempotency check: If already marked paid/confirmed, do not duplicate actions
            if ($lockedOrder->payment_status === 'paid' && $lockedOrder->status === Order::STATUS_CONFIRMED) {
                Log::info("Order {$lockedOrder->order_reference} is already fulfilled. Skipping redundant fulfillment.");
                return $lockedOrder;
            }

            // 3. Transition Order State
            $lockedOrder->update([
                'status'            => Order::STATUS_CONFIRMED,
                'payment_status'    => 'paid',
                'payment_method'    => 'stripe',
                'payment_reference' => $paymentReference,
            ]);

            // 4. Safely record Payment log (or update if existing)
            Payment::updateOrCreate(
                [
                    'order_id'          => $lockedOrder->id,
                    'payment_reference' => $paymentReference,
                ],
                [
                    'provider' => 'stripe',
                    'amount'   => $lockedOrder->grand_total,
                    'currency' => config('services.stripe.currency', 'inr'),
                    'status'   => 'successful',
                    'payload'  => $paymentPayload,
                ]
            );

            // 5. Inventory Deduction Rules (Safely executed only once)
            foreach ($lockedOrder->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    if ($product->inventory_type === 'READY_TO_SHIP') {
                        // Prevent stock from going below 0
                        $decrementBy = min($item->quantity, $product->stock);
                        if ($decrementBy > 0) {
                            $product->decrement('stock', $decrementBy);
                        }

                        // Auto-convert to MADE_TO_ORDER if stock has run out
                        $product->refresh();
                        if ($product->stock <= 0) {
                            $product->update([
                                'inventory_type' => 'MADE_TO_ORDER',
                                'stock'          => 0,
                            ]);
                            Log::info("Product #{$product->id} ({$product->name}) stock reached 0. Automatically converted to MADE_TO_ORDER.");
                        }
                    }
                }
            }

            // 6. Send Customer In-App Notification (if user is registered)
            if ($lockedOrder->user_id) {
                try {
                    CustomerNotification::create([
                        'user_id' => $lockedOrder->user_id,
                        'title'   => 'Payment Received & Order Confirmed',
                        'message' => "Your payment of ₹" . number_format($lockedOrder->grand_total, 2) . " for order {$lockedOrder->order_reference} has been received. Our artisans have commenced preparation.",
                        'type'    => 'order',
                        'data'    => ['order_reference' => $lockedOrder->order_reference],
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("Customer in-app notification error: " . $e->getMessage());
                }
            }

            // 7. Dispatch Order Confirmation Email to Patron
            if (!empty($lockedOrder->email)) {
                try {
                    Mail::to($lockedOrder->email)->send(new OrderConfirmationMail($lockedOrder));
                } catch (\Throwable $e) {
                    Log::warning("Order confirmation email could not be sent to {$lockedOrder->email}: " . $e->getMessage());
                }
            }

            // 8. Dispatch Real-time Admin Notification Bell
            try {
                \App\Services\AdminNotificationService::newOrder($lockedOrder);
            } catch (\Throwable $e) {
                Log::warning("Admin order notification could not be sent: " . $e->getMessage());
            }

            Log::info("Order {$lockedOrder->order_reference} successfully fulfilled via Stripe ({$paymentReference}).");

            return $lockedOrder;
        });
    }
}
