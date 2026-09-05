<?php

namespace App\Services;

use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class AdminNotificationService
{
    /**
     * Notify all admin users.
     */
    public static function notifyAdmins(Notification $notification): void
    {
        // Find all admin users
        $admins = User::where('is_admin', true)
            ->orWhere('id', 1)
            ->orWhere('email', 'admin@maisonresine.com')
            ->get();

        if ($admins->isEmpty()) {
            $admins = User::limit(1)->get();
        }

        foreach ($admins as $admin) {
            $notification->sendToDatabase($admin);
        }
    }

    /**
     * Stretched invisible full-card click action.
     */
    protected static function makeClickAction(string $url): Action
    {
        return Action::make('open')
            ->label('')
            ->url($url)
            ->markAsRead()
            ->extraAttributes([
                'class' => 'stretched-link absolute inset-0 z-10 opacity-0 cursor-pointer w-full h-full block',
                'style' => 'position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; z-index: 10;',
            ]);
    }

    /**
     * Send clean new order notification.
     */
    public static function newOrder(\App\Models\Order $order): void
    {
        $patron = $order->shipping_address_snapshot['full_name'] ?? ($order->user->name ?? 'Patron');
        $itemCount = $order->items->count();

        $notification = Notification::make()
            ->title('New Order #' . ($order->order_reference ?: $order->id))
            ->icon('heroicon-o-shopping-bag')
            ->iconColor('success')
            ->body("{$patron} placed an order for ₹" . number_format($order->grand_total, 2) . " ({$itemCount} items).")
            ->actions([
                self::makeClickAction('/admin/orders/' . $order->id . '/edit'),
            ]);

        self::notifyAdmins($notification);
    }

    /**
     * Send clean custom order inquiry notification.
     */
    public static function newCustomRequest(\App\Models\CustomRequest $request): void
    {
        $notification = Notification::make()
            ->title('New Custom Order Request')
            ->icon('heroicon-o-sparkles')
            ->iconColor('warning')
            ->body("Inquiry from {$request->name} for {$request->project_type}.")
            ->actions([
                self::makeClickAction('/admin/custom-requests/' . $request->id . '/edit'),
            ]);

        self::notifyAdmins($notification);
    }

    /**
     * Send clean contact inquiry notification.
     */
    public static function newContactInquiry(\App\Models\ContactInquiry $inquiry): void
    {
        $notification = Notification::make()
            ->title('New Contact Message: ' . $inquiry->subject)
            ->icon('heroicon-o-envelope')
            ->iconColor('info')
            ->body("From {$inquiry->name} ({$inquiry->email}): " . \Illuminate\Support\Str::limit($inquiry->message, 80))
            ->actions([
                self::makeClickAction('/admin/contact-inquiries/' . $inquiry->id),
            ]);

        self::notifyAdmins($notification);
    }

    /**
     * Send clean support ticket notification.
     */
    public static function newSupportTicket(\App\Models\SupportTicket $ticket): void
    {
        $customer = $ticket->user->name ?? 'Patron';

        $notification = Notification::make()
            ->title('New Support Ticket #' . $ticket->ticket_number)
            ->icon('heroicon-o-lifebuoy')
            ->iconColor('danger')
            ->body("{$ticket->subject} from {$customer}.")
            ->actions([
                self::makeClickAction('/admin/support-tickets/' . $ticket->id . '/edit'),
            ]);

        self::notifyAdmins($notification);
    }

    /**
     * Send clean return/refund request notification.
     */
    public static function newReturnOrRefund(string $type, $record): void
    {
        $orderRef = $record->order->order_reference ?? $record->order_id;
        $reason = \Illuminate\Support\Str::limit($record->reason ?? '', 60);

        $notification = Notification::make()
            ->title('New ' . ucfirst($type) . ' Request')
            ->icon('heroicon-o-arrow-path')
            ->iconColor('danger')
            ->body("Order #{$orderRef} - {$reason}")
            ->actions([
                self::makeClickAction('/admin/' . ($type === 'refund' ? 'refund-requests' : 'return-requests') . '/' . $record->id . '/edit'),
            ]);

        self::notifyAdmins($notification);
    }
}
