<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    /**
     * Generate and stream PDF receipt for an Invoice record.
     */
    public function download(Invoice $invoice)
    {
        $invoice->load('customRequest');

        $pdf = Pdf::loadView('pdf.invoice_receipt', compact('invoice'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'dpi'                  => 150,
                'defaultMediaType'     => 'print',
            ]);

        $filename = 'Receipt-' . $invoice->invoice_number . '.pdf';

        return $pdf->stream($filename);
    }
}
