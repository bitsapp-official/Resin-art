<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $previousStatus = ''
    ) {}

    public function envelope(): Envelope
    {
        $statusLabel = $this->order->status_label;

        $subjectMap = [
            Order::STATUS_CONFIRMED  => "Your order [{$this->order->order_reference}] is confirmed",
            Order::STATUS_PROCESSING => "Your order [{$this->order->order_reference}] is now processing",
            Order::STATUS_SHIPPED    => "Your order [{$this->order->order_reference}] has been shipped",
            Order::STATUS_DELIVERED  => "Your order [{$this->order->order_reference}] has been delivered",
            Order::STATUS_CANCELLED  => "Order [{$this->order->order_reference}] has been cancelled",
        ];

        $subject = $subjectMap[$this->order->status]
            ?? "Order Update: [{$this->order->order_reference}] is {$statusLabel}";

        return new Envelope(
            subject: "{$subject} — Maison Résine",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.status_updated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
