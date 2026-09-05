<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice — {{ $order->order_reference }}</title>
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

        .item-sku {
            font-size: 7.5px;
            color: #8E877D;
            margin-top: 2px;
            font-family: 'DejaVu Sans', monospace;
        }

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
        $phone        = \App\Models\SiteSetting::get('invoice_phone', '+91 98201 45678');
        $website      = \App\Models\SiteSetting::get('invoice_website', 'www.maisonresine.com');
        $gstin        = trim(\App\Models\SiteSetting::get('invoice_gstin', ''));
        $taxLabel     = \App\Models\SiteSetting::get('invoice_tax_label', 'Estimated Taxes (GST 5%)');
        $authNote     = \App\Models\SiteSetting::get('invoice_authenticity_note', 'Every artwork is accompanied by an embossed physical Certificate of Authenticity.');
        $footerNote   = \App\Models\SiteSetting::get('invoice_footer_note', 'Thank you for choosing Maison Résine. This is a computer-generated tax invoice.');
    @endphp

    <!-- Header Table -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 54%;">
                <div class="brand-name">{{ $brandName }}</div>
                <div class="brand-tagline">{{ $brandTagline }}</div>
                <div class="brand-contact">
                    {!! nl2br(e($address)) !!}<br>
                    @if(!empty($gstin))
                        <strong>GSTIN / Tax ID:</strong> {{ $gstin }} &bull; 
                    @endif
                    {{ $email }}<br>
                    {{ $website }}
                </div>
            </td>
            <td style="width: 46%;">
                <div class="doc-title">Tax Invoice</div>
                <div class="doc-meta">
                    <strong>Invoice No:</strong> INV-{{ str_replace('MR-', '', $order->order_reference) }}<br>
                    <strong>Order Reference:</strong> {{ $order->order_reference }}<br>
                    <strong>Date:</strong> {{ $order->created_at->format('d F Y') }}<br>
                    <strong>Payment Method:</strong> {{ strtoupper($order->payment_method ?? 'ONLINE') }}
                </div>
                <div class="paid-badge-container">
                    <span class="paid-badge">{{ strtoupper($order->payment_status ?? 'PAID') }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Elegant Header Divider Line with Clear Separation -->
    <div class="header-divider"></div>

    <!-- Billing & Shipping Information -->
    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="info-label">Billed To (Collector / Buyer)</div>
                <div class="info-value">
                    <strong>{{ $order->billing_address_snapshot['full_name'] ?? ($order->shipping_address_snapshot['full_name'] ?? 'Valued Collector') }}</strong><br>
                    {{ $order->billing_address_snapshot['address_line_1'] ?? ($order->shipping_address_snapshot['address_line_1'] ?? '') }}
                    @if(!empty($order->billing_address_snapshot['address_line_2'] ?? $order->shipping_address_snapshot['address_line_2']))
                        , {{ $order->billing_address_snapshot['address_line_2'] ?? $order->shipping_address_snapshot['address_line_2'] }}
                    @endif<br>
                    {{ $order->billing_address_snapshot['city'] ?? ($order->shipping_address_snapshot['city'] ?? '') }}, 
                    {{ $order->billing_address_snapshot['state'] ?? ($order->shipping_address_snapshot['state'] ?? '') }} 
                    {{ $order->billing_address_snapshot['postal_code'] ?? ($order->shipping_address_snapshot['postal_code'] ?? '') }}<br>
                    {{ $order->billing_address_snapshot['country'] ?? ($order->shipping_address_snapshot['country'] ?? 'India') }}<br>
                    <strong>Email:</strong> {{ $order->email }}<br>
                    <strong>Contact Phone:</strong> {{ $order->billing_address_snapshot['phone'] ?? ($order->shipping_address_snapshot['phone'] ?? 'N/A') }}
                </div>
            </td>
            <td>
                <div class="info-label">Destination Crate (Shipping Delivery)</div>
                <div class="info-value">
                    <strong>{{ $order->shipping_address_snapshot['full_name'] ?? 'Valued Collector' }}</strong><br>
                    {{ $order->shipping_address_snapshot['address_line_1'] ?? '' }}
                    @if(!empty($order->shipping_address_snapshot['address_line_2']))
                        , {{ $order->shipping_address_snapshot['address_line_2'] }}
                    @endif<br>
                    {{ $order->shipping_address_snapshot['city'] ?? '' }}, 
                    {{ $order->shipping_address_snapshot['state'] ?? '' }} 
                    {{ $order->shipping_address_snapshot['postal_code'] ?? '' }}<br>
                    {{ $order->shipping_address_snapshot['country'] ?? 'India' }}<br>
                    @if(!empty($order->shipping_address_snapshot['phone']))
                        <strong>Delivery Contact:</strong> {{ $order->shipping_address_snapshot['phone'] }}<br>
                    @endif
                    @if($order->courier)
                        <strong>Courier:</strong> {{ $order->courier }} &bull; <strong>Tracking:</strong> {{ $order->tracking_number ?? 'Pending' }}
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
                <th>Art Piece Description</th>
                <th style="width: 60px;" class="center">Qty</th>
                <th style="width: 95px;" class="right">Unit Price</th>
                <th style="width: 105px;" class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $idx => $item)
                <tr>
                    <td class="center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->sku)
                            <div class="item-sku">SKU: {{ $item->sku }}</div>
                        @endif
                        @if(!empty($item->options) && is_array($item->options))
                            <div class="item-sku">
                                @foreach($item->options as $k => $v)
                                    {{ ucfirst($k) }}: {{ is_array($v) ? implode(', ', $v) : $v }}@if(!$loop->last) &bull; @endif
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">&#8377; {{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">&#8377; {{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals and Payment Summary -->
    <table class="totals-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 52%;">
                <div class="payment-info-box">
                    <div style="font-size: 7.5px; text-transform: uppercase; letter-spacing: 1.2px; font-weight: bold; color: #8E877D; margin-bottom: 3px;">
                        Payment Verification &amp; Authenticity
                    </div>
                    <strong>Transaction Reference:</strong> {{ $order->payment_reference ?? 'VERIFIED_PAYMENT' }}<br>
                    <strong>Payment Mode:</strong> {{ strtoupper($order->payment_method ?? 'ONLINE') }} &bull; <strong>Status:</strong> Successful<br>
                    <em>{{ $authNote }}</em>
                </div>
            </td>
            <td style="width: 48%;">
                <table class="summary-box-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="val">&#8377; {{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->discount > 0)
                        <tr>
                            <td class="label">Atelier Privilege Discount</td>
                            <td class="val" style="color: #059669;">- &#8377; {{ number_format($order->discount, 2) }}</td>
                        </tr>
                    @endif
                    @if($order->tax > 0)
                        <tr>
                            <td class="label">{{ $taxLabel }}</td>
                            <td class="val">&#8377; {{ number_format($order->tax, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">White-Glove Crated Shipping</td>
                        <td class="val" style="color: #059669;">
                            @if($order->shipping_fee > 0)
                                &#8377; {{ number_format($order->shipping_fee, 2) }}
                            @else
                                Complimentary
                            @endif
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td class="label" style="color: #1C1917; font-weight: bold;">Grand Total (INR)</td>
                        <td class="val">&#8377; {{ number_format($order->grand_total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer / Terms -->
    <div class="footer">
        <strong>{{ $footerNote }}</strong><br>
        This document is a computer-generated tax invoice and requires no physical signature.<br>
        {{ $brandName }} Atelier &middot; Handcrafted Resin Art &amp; Custom Orders &middot; All Rights Reserved.
    </div>

</div>
</body>
</html>
