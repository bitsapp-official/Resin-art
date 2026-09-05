<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\CustomerNotification;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('download_invoice')
                ->label('Tax Invoice (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->url(fn () => route('admin.orders.pdf', $this->record))
                ->openUrlInNewTab(),

            \Filament\Actions\Action::make('tracking_link')
                ->label('Track Order')
                ->icon('heroicon-o-map-pin')
                ->color('gray')
                ->url(fn () => route('tracking.index', ['order_reference' => $this->record->order_reference, 'email' => $this->record->email]))
                ->openUrlInNewTab(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Auto-dismiss admin bell notification for this order when opened
        $this->dismissRelatedNotification();
    }

    /**
     * Delete any admin bell notification that links to this order.
     */
    protected function dismissRelatedNotification(): void
    {
        try {
            $admins = User::where('is_admin', true)
                ->orWhere('id', 1)
                ->get();

            $urlSuffix = '/admin/orders/' . $this->record->id . '/edit';

            foreach ($admins as $admin) {
                $admin->notifications()
                    ->where('data->format', 'filament')
                    ->get()
                    ->filter(function ($n) use ($urlSuffix) {
                        $actions = data_get($n->data, 'actions', []);
                        foreach ($actions as $action) {
                            $url = data_get($action, 'url', '');
                            if (str_ends_with($url, $urlSuffix)) {
                                return true;
                            }
                        }
                        return false;
                    })
                    ->each(fn ($n) => $n->delete());
            }
        } catch (\Throwable) {
            // Silent fail
        }
    }

    protected function afterSave(): void
    {
        $order = $this->record;

        // 1. Create In-App Customer Notification if user account exists
        if ($order->user_id) {
            CustomerNotification::create([
                'user_id' => $order->user_id,
                'title' => "Order Status Update [{$order->order_reference}]",
                'message' => "Your order status is now: " . str_replace('_', ' ', $order->status),
                'type' => 'order_status',
                'action_url' => route('account.orders.show', $order->id),
            ]);
        }

        // 2. Send Status Update Email to Customer
        try {
            if (!empty($order->email)) {
                Mail::to($order->email)->send(new OrderStatusUpdatedMail($order));
            }
        } catch (\Throwable $e) {
            Log::warning("Order status email notification failed for {$order->order_reference}: " . $e->getMessage());
        }
    }
}
