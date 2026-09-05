<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt Invoice — {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #FFFFFF;
            color: #1C1917;
            font-size: 10px;
            line-height: 1.5;
            width: 100%;
        }

        .page {
            padding: 32px 38px 24px 38px;
        }

        /* ─── HEADER ─── */
        .header-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            padding: 0 0 10px 0;
        }
        
        .brand-name {
            font-size: 19px;
            font-weight: bold;
            color: #1C1917;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .brand-tagline {
            font-size: 8px;
            color: #8E877D;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .brand-contact {
            font-size: 8.5px;
            color: #57534E;
            margin-top: 8px;
            line-height: 1.6;
        }

        .doc-title {
            font-size: 20px;
            font-weight: bold;
            color: #1C1917;
            letter-spacing: 1.5px;
            text-align: right;
            text-transform: uppercase;
        }
        .doc-meta {
            font-size: 8.5px;
            color: #78716C;
            margin-top: 5px;
            line-height: 1.7;
            text-align: right;
        }
        .doc-meta strong { color: #1C1917; }
        
        .paid-badge-container {
            text-align: right;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        .paid-badge {
            display: inline-block;
            background: #1C1917;
            color: #FAF8F5;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 4px 12px 3px 12px;
            line-height: 10px;
            border-radius: 10px;
            text-align: center;
            vertical-align: middle;
        }

        .header-divider {
            width: 100%;
            height: 2px;
            background-color: #1C1917;
            margin-top: 8px;
            margin-bottom: 20px;
            clear: both;
        }

        /* ─── ADDRESSES TABLE ─── */
        .info-table {
            width: 100%;
            border: 1px solid #E5DFD3;
            border-radius: 6px;
            background: #FAF8F5;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 12px 16px;
            vertical-align: top;
            width: 50%;
        }
        .info-table td + td { border-left: 1px solid #E5DFD3; }
        
        .info-label {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #8E877D;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 9.5px;
            color: #1C1917;
            line-height: 1.55;
        }

        /* ─── ITEMS TABLE ─── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #1C1917;
            color: #FAF8F5;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
        }
        .items-table th.right { text-align: right; }
        .items-table th.center { text-align: center; }

        .items-table td {
            padding: 10px 10px;
            border-bottom: 1px solid #E5DFD3;
            font-size: 9px;
            color: #1C1917;
            vertical-align: top;
        }
        .items-table tr:nth-child(even) td {
            background: #FAF8F5;
        }
        .items-table td.right { text-align: right; }
        .items-table td.center { text-align: center; }

        /* ─── TOTALS SECTION ─── */
        .totals-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .totals-table td { vertical-align: top; }

        .payment-info-box {
            border: 1px solid #E5DFD3;
            background: #FAF8F5;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 8.5px;
            line-height: 1.65;
            color: #57534E;
            width: 92%;
        }
        .payment-info-box strong { color: #1C1917; }

        .summary-box-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-box-table td {
            padding: 4px 6px;
            font-size: 9px;
        }
        .summary-box-table td.label {
            text-align: right;
            color: #78716C;
        }
        .summary-box-table td.val {
            text-align: right;
            font-weight: bold;
            color: #1C1917;
            width: 105px;
        }
        .summary-box-table tr.total-row td {
            border-top: 1.5px solid #1C1917;
            padding-top: 7px;
            font-size: 11px;
            font-weight: bold;
            color: #1C1917;
        }

        /* ─── FOOTER ─── */
        .footer {
            border-top: 1px dashed #D9D2C5;
            padding-top: 12px;
            margin-top: 16px;
            font-size: 7.5px;
            color: #8E877D;
            text-align: center;
            line-height: 1.55;
        }
    </style>
</head>
<body>
<div class="page">

    @php
        $brandName    = \App\Models\SiteSetting::get('invoice_brand_name', 'Maison Résine');
        $brandTagline = \App\Models\SiteSetting::get('invoice_brand_tagline', 'Haute Résine Atelier · Art Contemporain');
        $address      = \App\Models\SiteSetting::get('invoice_address', '14 rue des Étoiles, 33000 Bordeaux, France');
        $email        = \App\Models\SiteSetting::get('invoice_email', 'atelier@maisonresine.com');
        $phone        = \App\Models\SiteSetting::get('invoice_phone', '+91 98765 43210');
        $website      = \App\Models\SiteSetting::get('invoice_website', 'www.maisonresine.com');
        $footerNote   = \App\Models\SiteSetting::get('invoice_footer_note', 'Thank you for choosing Maison Résine. This is an official payment receipt & tax invoice.');
        
        $balanceDue   = max(0, $invoice->total_amount - $invoice->paid_amount);
        $statusLabel  = match(strtolower($invoice->payment_status)) {
            'fully_paid', 'paid' => 'PAID',
            'advance_paid', 'partially_paid' => 'PARTIAL PAID',
            'unpaid' => 'UNPAID',
            default => str_replace('_', ' ', strtoupper($invoice->payment_status)),
        };
        $isPaid       = in_array($invoice->payment_status, ['advance_paid', 'fully_paid', 'paid', 'partially_paid']);
        $docTitle     = $isPaid ? 'Payment Receipt' : 'Tax Invoice';
    @endphp

    <!-- Header Table -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 54%;">
                <div class="brand-name">{{ $brandName }}</div>
                <div class="brand-tagline">{{ $brandTagline }}</div>
                <div class="brand-contact">
                    {!! nl2br(e($address)) !!}<br>
                    {{ $email }} &bull; {{ $phone }}<br>
                    {{ $website }}
                </div>
            </td>
            <td style="width: 46%;">
                <div class="doc-title">{{ $docTitle }}</div>
                <div class="doc-meta">
                    <strong>{{ $isPaid ? 'Receipt No:' : 'Invoice No:' }}</strong> {{ $invoice->invoice_number }}<br>
                    @if($invoice->customRequest)
                        <strong>Reference Code:</strong> {{ $invoice->customRequest->public_reference }}<br>
                    @endif
                    <strong>Date:</strong> {{ $invoice->invoice_date->format('d F Y') }}<br>
                    <strong>Payment Method:</strong> {{ strtoupper($invoice->payment_method ?: 'Direct Payment') }}
                </div>
                <div class="paid-badge-container">
                    <span class="paid-badge">{{ $statusLabel }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Header Divider Line -->
    <div class="header-divider"></div>

    <!-- Client & Destination Info -->
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="info-label">Billed To (Collector / Buyer)</div>
                <div class="info-value">
                    <strong>{{ $invoice->client_name }}</strong><br>
                    @if($invoice->client_email)
                        <strong>Email:</strong> {{ $invoice->client_email }}<br>
                    @endif
                    @if($invoice->client_phone)
                        <strong>Phone / WhatsApp:</strong> {{ $invoice->client_phone }}
                    @endif
                </div>
            </td>
            <td>
                <div class="info-label">Shipping / Destination Details</div>
                <div class="info-value">
                    <strong>Destination Address:</strong> {{ $invoice->client_address ?: 'Custom Delivery' }}<br>
                    @if($invoice->customRequest)
                        <strong>Request Ref:</strong> {{ $invoice->customRequest->public_reference }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 32px;" class="center">#</th>
                <th>Artwork / Order Item Description</th>
                <th style="width: 50px;" class="center">Qty</th>
                <th style="width: 95px;" class="right">Unit Price</th>
                <th style="width: 105px;" class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($invoice->items) && is_array($invoice->items) && count($invoice->items) > 0)
                @foreach($invoice->items as $idx => $item)
                    @php
                        $qty = (float)($item['qty'] ?? 1);
                        $unitPrice = (float)($item['unit_price'] ?? 0);
                        $rowTotal = $qty * $unitPrice;
                    @endphp
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $item['name'] ?? '' }}</strong>
                            @if(!empty($item['description']))
                                <div style="font-size: 8px; color: #78716C; margin-top: 4px;">
                                    {{ $item['description'] }}
                                </div>
                            @endif
                        </td>
                        <td class="center">{{ (int)$qty }}</td>
                        <td class="right">{{ $invoice->currency_symbol }} {{ number_format($unitPrice, 2) }}</td>
                        <td class="right">{{ $invoice->currency_symbol }} {{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="center">1</td>
                    <td>
                        <strong>{{ $invoice->item_title ?: 'Bespoke Resin Artwork' }}</strong>
                        @if($invoice->item_description)
                            <div style="font-size: 8px; color: #78716C; margin-top: 4px;">
                                {{ $invoice->item_description }}
                            </div>
                        @endif
                    </td>
                    <td class="center">1</td>
                    <td class="right">{{ $invoice->currency_symbol }} {{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="right">{{ $invoice->currency_symbol }} {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    @php
        $grandTotal = $invoice->total_amount;
        $gstTax     = round(($grandTotal * 18) / 118, 2);
        $subtotal   = $grandTotal - $gstTax;
        $isFullyPaid = ($invoice->payment_status === 'fully_paid');
    @endphp

    <!-- Totals and Payment Summary -->
    <table class="totals-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 52%;">
                <div class="payment-info-box">
                    <div style="font-size: 7.5px; text-transform: uppercase; letter-spacing: 1.2px; font-weight: bold; color: #8E877D; margin-bottom: 3px;">
                        Payment Verification &amp; Authenticity
                    </div>
                    @if(!empty($invoice->payment_reference))
                        <strong>Transaction Reference:</strong> {{ $invoice->payment_reference }}<br>
                    @endif
                    <strong>Payment Mode:</strong> {{ strtoupper($invoice->payment_method ?: 'Direct Bank Transfer') }}<br>
                    <strong>Payment Status:</strong> {{ $isFullyPaid ? 'FULLY PAID (100%)' : 'UNPAID / PENDING' }}<br>
                    <em>Every artwork is accompanied by an embossed physical Certificate of Authenticity.</em>
                </div>
            </td>
            <td style="width: 48%;">
                <table class="summary-box-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="label">Subtotal (Net)</td>
                        <td class="val">{{ $invoice->currency_symbol }} {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Taxes (GST 18% Included)</td>
                        <td class="val">{{ $invoice->currency_symbol }} {{ number_format($gstTax, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label" style="color: #1C1917; font-weight: bold;">Grand Total (INR)</td>
                        <td class="val">{{ $invoice->currency_symbol }} {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer / Terms -->
    <div class="footer">
        <strong>{{ $footerNote }}</strong><br>
        All prices are inclusive of 18% GST (CGST 9% + SGST 9%). This document is an official computer-generated tax invoice and payment receipt.<br>
        {{ $brandName }} Atelier &middot; Handcrafted Luxury Resin Art &middot; All Rights Reserved.
    </div>

</div>
</body>
</html>
