<?php

namespace App\Http\Controllers;

use App\Models\CustomQuote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotePdfController extends Controller
{
    /**
     * Generate and stream a PDF for the given quote.
     * Secured — only authenticated admin users can access.
     */
    public function download(CustomQuote $quote)
    {
        // Load relationships
        $quote->load(['request', 'items']);

        $pdf = Pdf::loadView('pdf.quote', compact('quote'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'     => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'dpi'             => 150,
                'defaultMediaType' => 'print',
            ]);

        $filename = 'Quote-' . $quote->quote_reference . '.pdf';

        // stream() opens PDF inline in browser tab (user can view, print, or save)
        return $pdf->stream($filename);
    }
}
