<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quote {{ $quote->quote_reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            font-family: 'DejaVu Sans', sans-serif;
            background: #FFFFFF;
            color: #1C1917;
            font-size: 11px;
            line-height: 1.5;
            width: 100%;
        }

        .page {
            padding: 36px 40px 24px 40px;
        }

        /* ─── HEADER ─── */
        .header-table {
            width: 100%;
            border-bottom: 1px dashed #D9D2C5;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .header-table td { vertical-align: top; }
        .brand-name {
            font-size: 18px;
            font-weight: bold;
            color: #1C1917;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .brand-tagline {
            font-size: 8.5px;
            color: #8E877D;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .brand-contact {
            font-size: 9.5px;
            color: #57534E;
            margin-top: 8px;
            line-height: 1.7;
        }
        .doc-title {
            font-size: 22px;
            font-weight: bold;
            color: #1C1917;
            letter-spacing: 1px;
            text-align: right;
        }
        .doc-meta {
            font-size: 9.5px;
            color: #78716C;
            margin-top: 5px;
            line-height: 1.9;
            text-align: right;
        }
        .doc-meta strong { color: #1C1917; }
        .status-badge {
            display: inline-block;
            background: #1C1917;
            color: #FAF8F5;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 3px 9px;
            border-radius: 4px;
            margin-top: 6px;
        }

        /* ─── CUSTOMER + PROJECT INFO ─── */
        .info-table {
            width: 100%;
            border: 1px solid #E5DFD3;
            border-radius: 6px;
            background: #FDFAF6;
            margin-bottom: 20px;
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
            letter-spacing: 1.2px;
            color: #8E877D;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 11px;
            color: #1C1917;
            font-weight: 600;
            line-height: 1.5;
        }
        .info-sub {
            font-size: 9.5px;
            color: #78716C;
            margin-top: 2px;
        }

        /* ─── SECTION LABEL ─── */
        .section-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #8E877D;
            font-weight: bold;
            margin-bottom: 8px;
        }

        /* ─── LINE ITEMS ─── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .items-table thead th {
            background: #F5F2EC;
            color: #1C1917;
            border-bottom: 1.5px solid #1C1917;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            padding: 8px 10px;
            text-align: left;
        }
        .items-table thead th.right { text-align: right; }
        .items-table thead th.center { text-align: center; }
        .items-table tbody tr { border-bottom: 1px solid #F0EBE3; }
        .items-table tbody tr:nth-child(even) { background: #FAF8F5; }
        .items-table tbody td {
            padding: 9px 10px;
            font-size: 10.5px;
            color: #1C1917;
            vertical-align: middle;
        }
        .items-table tbody td.center { text-align: center; color: #57534E; }
        .items-table tbody td.right { text-align: right; font-weight: 600; }
        .items-table tbody td.unit { text-align: right; color: #57534E; }

        /* ─── TOTALS SECTION ─── */
        .totals-table {
            width: 100%;
            margin-bottom: 18px;
        }
        .totals-table td.spacer { width: 50%; }
        .totals-table td.content { width: 50%; vertical-align: top; }

        .subtotal-table {
            width: 100%;
            border-collapse: collapse;
        }
        .subtotal-table td {
            padding: 4px 0;
            font-size: 10px;
            color: #78716C;
        }
        .subtotal-table td.val {
            text-align: right;
            color: #1C1917;
            font-weight: 500;
        }
        .subtotal-table td.discount { color: #16a34a; }

        .grand-total-table {
            width: 100%;
            margin-top: 8px;
            border-top: 2px solid #1C1917;
            border-bottom: 2px solid #1C1917;
            padding: 8px 0;
        }
        .grand-total-table td {
            font-size: 11.5px;
            font-weight: bold;
            color: #1C1917;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .grand-total-table td.amount {
            text-align: right;
            font-size: 14px;
            color: #1C1917;
        }

        .deposit-table {
            width: 100%;
            margin-top: 10px;
            border: 1px solid #D9D2C5;
            border-radius: 6px;
            background: #FDFAF6;
            padding: 10px 12px;
        }

        /* ─── NOTES ─── */
        .notes-box {
            background: #FDFAF6;
            border: 1px solid #E5DFD3;
            border-radius: 6px;
            padding: 12px 16px;
            margin-top: 14px;
        }
        .notes-text { font-size: 10px; color: #57534E; line-height: 1.6; white-space: pre-line; }

        /* ─── FOOTER ─── */
        .footer-table {
            width: 100%;
            border-top: 1px solid #E5DFD3;
            padding-top: 12px;
            margin-top: 20px;
        }
        .footer-table td {
            font-size: 8.5px;
            color: #A8A29E;
            vertical-align: middle;
        }
        .valid-badge {
            display: inline-block;
            background: #FEF3C7;
            color: #92400E;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="page">

    <!-- ═══ HEADER ═══ -->
    <table class="header-table">
        <tr>
            <td style="width:55%;">
                <div class="brand-name">Maison Résine Atelier</div>
                <div class="brand-tagline">Custom Resin Artwork Studio</div>
                <div class="brand-contact">
                    Email: {{ config('mail.from.address', 'studio@maisonresine.com') }}<br>
                    @if(config('app.business_whatsapp')) WhatsApp: {{ config('app.business_whatsapp') }}<br> @endif
                    @if(config('app.business_gst')) GST: {{ config('app.business_gst') }} @endif
                </div>
            </td>
            <td style="width:45%; text-align:right;">
                <div class="doc-title">QUOTATION</div>
                <div class="doc-meta">
                    <strong>Quote Ref:</strong> {{ $quote->quote_reference }}<br>
                    <strong>Request Ref:</strong> {{ $quote->request->public_reference }}<br>
                    <strong>Date:</strong> {{ $quote->created_at->format('d M Y') }}<br>
                    @if($quote->valid_until)<strong>Valid Until:</strong> {{ \Carbon\Carbon::parse($quote->valid_until)->format('d M Y') }}<br>@endif
                </div>
                <div><span class="status-badge">{{ $quote->status->getLabel() }}</span></div>
            </td>
        </tr>
    </table>

    <!-- ═══ CUSTOMER + PROJECT ═══ -->
    <table class="info-table">
        <tr>
            <td>
                <div class="info-label">Prepared For</div>
                <div class="info-value">{{ $quote->request->name }}</div>
                <div class="info-sub">
                    {{ $quote->request->email }}<br>
                    @if($quote->request->phone){{ $quote->request->phone }}@endif
                    @if($quote->request->whatsapp) &nbsp;| WA: {{ $quote->request->whatsapp }}@endif
                </div>
            </td>
            <td>
                <div class="info-label">Artwork Project</div>
                <div class="info-value">{{ ucfirst(str_replace('_', ' ', $quote->request->project_type)) }}</div>
                <div class="info-sub">
                    @if($quote->request->width && $quote->request->height)
                        Size: {{ $quote->request->width }} × {{ $quote->request->height }}
                        @if($quote->request->depth) × {{ $quote->request->depth }}@endif
                        {{ strtoupper($quote->request->unit ?? 'cm') }}<br>
                    @endif
                    @if($quote->request->preferred_style)Style: {{ $quote->request->preferred_style }}<br>@endif
                    @if($quote->request->preferred_colors)Colors: {{ $quote->request->preferred_colors }}@endif
                </div>
            </td>
        </tr>
    </table>

    <!-- ═══ LINE ITEMS ═══ -->
    <div class="section-label">Scope of Work</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:55%">Description</th>
                <th class="center" style="width:10%">Qty</th>
                <th class="right" style="width:15%">Unit Price</th>
                <th class="right" style="width:15%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $i => $item)
            <tr>
                <td style="color:#A8A29E;">{{ $i + 1 }}</td>
                <td>{{ $item->description }}</td>
                <td class="center">{{ $item->quantity }}</td>
                <td class="unit">&#8377;{{ number_format($item->unit_price, 2) }}</td>
                <td class="right">&#8377;{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- ═══ TOTALS TABLE ═══ -->
    <table class="totals-table">
        <tr>
            <td class="spacer"></td>
            <td class="content">
                @php
                    $subtotal = $quote->subtotal ?? $quote->items->sum('total');
                @endphp
                <table class="subtotal-table">
                    @if($quote->shipping_amount > 0 || $quote->tax_amount > 0 || $quote->discount_amount > 0)
                    <tr>
                        <td>Subtotal</td>
                        <td class="val">&#8377;{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @endif
                    @if($quote->shipping_amount > 0)
                    <tr>
                        <td>Shipping &amp; Handling</td>
                        <td class="val">&#8377;{{ number_format($quote->shipping_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($quote->tax_amount > 0)
                    <tr>
                        <td>Tax (GST)</td>
                        <td class="val">&#8377;{{ number_format($quote->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($quote->discount_amount > 0)
                    <tr>
                        <td>Discount</td>
                        <td class="val discount">– &#8377;{{ number_format($quote->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                </table>

                <!-- Grand Total -->
                <table class="grand-total-table">
                    <tr>
                        <td>Grand Total</td>
                        <td class="amount">&#8377;{{ number_format($quote->total_amount, 2) }}</td>
                    </tr>
                </table>

                <!-- Deposit Box -->
                @php
                    $depositAmt = ($quote->deposit_type?->value === 'percentage')
                        ? ($quote->total_amount * $quote->deposit_amount / 100)
                        : $quote->deposit_amount;
                @endphp
                <table class="deposit-table">
                    <tr>
                        <td>
                            <div style="font-size: 7.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #8E877D; font-weight: bold; margin-bottom: 3px;">ADVANCE DEPOSIT REQUIRED</div>
                            <div style="font-size: 14px; font-weight: bold; color: #1C1917;">&#8377;{{ number_format($depositAmt, 2) }}</div>
                            <div style="font-size: 9px; color: #78716C; margin-top: 3px;">
                                @if($quote->deposit_type?->value === 'percentage')
                                    {{ $quote->deposit_amount }}% of total to begin production
                                @else
                                    Fixed advance to begin production
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if($quote->estimated_completion)
    <div class="section-label" style="margin-top: 8px;">Estimated Delivery</div>
    <div style="font-size: 10.5px; color: #1C1917; margin-bottom: 12px;">{{ $quote->estimated_completion }}</div>
    @endif

    @if($quote->notes)
    <div class="notes-box">
        <div class="section-label" style="margin-bottom: 4px;">Terms &amp; Notes</div>
        <div class="notes-text">{{ $quote->notes }}</div>
    </div>
    @endif

    <!-- ═══ FOOTER ═══ -->
    <table class="footer-table">
        <tr>
            <td style="width: 60%;">
                This is a system-generated quotation.<br>
                For queries, contact us on WhatsApp or Email.
            </td>
            <td style="width: 40%; text-align: right;">
                @if($quote->valid_until)
                    <span class="valid-badge">Valid until {{ \Carbon\Carbon::parse($quote->valid_until)->format('d M Y') }}</span><br>
                @endif
                Generated: {{ now()->format('d M Y, h:i A') }}
            </td>
        </tr>
    </table>

</div>
</body>
</html>
