<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return view('account.orders.index', compact('orders'));
    }

    public function show(string $identifier)
    {
        $order = Order::where('order_reference', trim($identifier))
            ->orWhere('id', trim($identifier))
            ->firstOrFail();

        $this->authorize('view', $order);

        $order->load(['items.product', 'payments', 'returnRequests', 'refundRequests']);

        return view('account.orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order)
    {
        $hours = (int) config('atelier.cancellation_hours', env('ORDER_CANCELLATION_HOURS', 3));

        if (!$order->is_cancellable) {
            return back()->with('error', "Orders can only be cancelled within {$hours} hours of placement as bespoke crafting begins immediately.");
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $order->update([
            'status' => 'CANCELLED',
            'canceled_at' => now(),
            'cancel_reason' => $request->reason,
        ]);

        $refundAmount = (float) ($order->grand_total ?? $order->total_amount ?? $order->subtotal ?? 0);

        // Automatically create a Refund Request so admin can review and process refund
        $refundRequest = \App\Models\RefundRequest::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'amount' => $refundAmount,
            'reason' => "Order Cancellation: " . ($request->reason ?: 'Cancelled by customer'),
            'status' => 'REQUESTED',
        ]);

        try {
            \App\Services\AdminNotificationService::newReturnOrRefund('refund', $refundRequest);
        } catch (\Throwable $e) {
            // Ignore notification failure
        }

        try {
            if (!empty($order->email)) {
                \Illuminate\Support\Facades\Mail::to($order->email)->send(new \App\Mail\OrderStatusUpdatedMail($order));
            }
        } catch (\Throwable $e) {
            // Ignore email failure
        }

        return back()->with('success', "Order has been cancelled within the {$hours}-hour window. A refund request has been logged for processing.");
    }
}
