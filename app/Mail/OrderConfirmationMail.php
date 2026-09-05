<?php

namespace App\Mail;

use App\Http\Controllers\OrderInvoiceController;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Order Confirmation [{$this->order->order_reference}] & Tax Invoice — Maison Résine Atelier",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     * Automatically generates and attaches the official Tax Invoice PDF.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        try {
            $pdf = OrderInvoiceController::makePdf($this->order);
            
            return [
                Attachment::fromData(
                    fn () => $pdf->output(),
                    "Invoice-{$this->order->order_reference}.pdf"
                )->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            Log::warning("Could not attach invoice PDF to confirmation email for order {$this->order->order_reference}: " . $e->getMessage());
            return [];
        }
    }
}
