<?php

namespace App\Filament\Resources\RefundRequestResource\Pages;

use App\Filament\Resources\RefundRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditRefundRequest extends EditRecord
{
    protected static string $resource = RefundRequestResource::class;

    protected function afterSave(): void
    {
        $refund = $this->record;

        // If refund is marked completed, update order payment_status to 'refunded'
        if ($refund->status === 'COMPLETED' && $refund->order) {
            $refund->order->update(['payment_status' => 'refunded']);
        }

        // Notify customer in-app
        if ($refund->user_id) {
            \App\Models\CustomerNotification::create([
                'user_id' => $refund->user_id,
                'title' => "Refund Status Update [#" . ($refund->order?->order_reference ?? $refund->id) . "]",
                'message' => "Your refund request for ₹" . number_format($refund->amount, 2) . " is now: " . str_replace('_', ' ', $refund->status),
                'type' => 'refund_update',
                'action_url' => route('account.refunds.index'),
            ]);
        }
    }
}
