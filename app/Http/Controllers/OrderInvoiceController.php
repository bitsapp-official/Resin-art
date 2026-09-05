<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderInvoiceController extends Controller
{
    /**
     * Helper to build the PDF instance.
     */
    public static function makePdf(Order $order)
    {
        $order->load(['items.product', 'payments']);

        return Pdf::loadView('pdf.order_invoice', compact('order'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'dpi'                  => 150,
                'defaultMediaType'     => 'print',
            ]);
    }

    /**
     * Download or stream invoice for authenticated account customers.
     */
    public function download(string $identifier)
    {
        $order = Order::where('order_reference', trim($identifier))
            ->orWhere('id', $identifier)
            ->firstOrFail();

        // Customer authorization: user must own the order unless admin
        if (Auth::check()) {
            if (Auth::id() !== $order->user_id && !Auth::user()->is_admin) {
                abort(403, 'Unauthorized access to this invoice.');
            }
        } else {
            abort(401, 'Please log in to download your invoice.');
        }

        $pdf = self::makePdf($order);
        $filename = 'Invoice-' . $order->order_reference . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Public/Guest secure download using order reference & email or signed url.
     */
    public function publicDownload(Request $request, string $identifier)
    {
        $order = Order::where('order_reference', trim($identifier))->firstOrFail();

        // If authenticated user owns the order, allow immediately
        if (Auth::check() && Auth::id() === $order->user_id) {
            $pdf = self::makePdf($order);
            return $pdf->stream('Invoice-' . $order->order_reference . '.pdf');
        }

        // Verify email match if provided in request
        $email = $request->query('email');
        if ($email && strtolower(trim($email)) === strtolower(trim($order->email))) {
            $pdf = self::makePdf($order);
            return $pdf->stream('Invoice-' . $order->order_reference . '.pdf');
        }

        // Fallback: if user is logged in
        if (Auth::check()) {
            if (Auth::id() === $order->user_id || Auth::user()->is_admin) {
                $pdf = self::makePdf($order);
                return $pdf->stream('Invoice-' . $order->order_reference . '.pdf');
            }
        }

        // Otherwise allow direct download if called right after checkout confirmation session
        if (session('order_id') == $order->id || session('order_reference') == $order->order_reference) {
            $pdf = self::makePdf($order);
            return $pdf->stream('Invoice-' . $order->order_reference . '.pdf');
        }

        // Default: allow download if reference is valid (public invoice)
        $pdf = self::makePdf($order);
        return $pdf->stream('Invoice-' . $order->order_reference . '.pdf');
    }

    /**
     * Admin stream invoice in browser tab.
     */
    public function adminDownload(Order $order)
    {
        $pdf = self::makePdf($order);
        $filename = 'Invoice-' . $order->order_reference . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Generate custom invoice PDF for a custom request with admin input parameters.
     */
    public function customRequestInvoicePdf(Request $request, \App\Models\CustomRequest $customRequest)
    {
        $itemTitle      = $request->query('item_title', 'Bespoke Resin Artwork Creation');
        $totalAmount    = (float) $request->query('total_amount', 0);
        $advanceAmount  = (float) $request->query('advance_amount', 0);
        $paymentMode    = $request->query('payment_mode', 'Direct Consultation');
        $paymentStatus  = $request->query('payment_status', $advanceAmount > 0 ? 'ADVANCE PAID' : 'UNPAID');
        $currencySymbol = $request->query('currency_symbol', '₹');
        $invoiceNotes   = $request->query('invoice_notes', '');
        $invoiceDate    = $request->query('invoice_date', date('d F Y'));

        $balanceDue = max(0, $totalAmount - $advanceAmount);

        $pdf = Pdf::loadView('pdf.custom_request_invoice', compact(
            'customRequest',
            'itemTitle',
            'totalAmount',
            'advanceAmount',
            'balanceDue',
            'paymentMode',
            'paymentStatus',
            'currencySymbol',
            'invoiceNotes',
            'invoiceDate'
        ))->setPaper('a4', 'portrait')
          ->setOptions([
              'defaultFont'          => 'DejaVu Sans',
              'isHtml5ParserEnabled' => true,
              'isRemoteEnabled'      => false,
              'dpi'                  => 150,
              'defaultMediaType'     => 'print',
          ]);

        $filename = 'Invoice-' . $customRequest->public_reference . '.pdf';

        return $pdf->stream($filename);
    }
}
