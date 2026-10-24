<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Quotation — {{ $quote->quote_reference }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            background: #FFFFFF;
            color: #1A1615;
            font-size: 8.5pt;
            line-height: 1.45;
            -webkit-print-color-adjust: exact;
        }

        body {
            margin: 28pt 36pt 24pt 36pt;
        }

        .page-container {
            width: 100%;
        }

        /* ─── LUXURY ACCENT BORDER (TOP GOLD BAND) ─── */
        .top-luxury-bar {
            width: 100%;
            height: 4pt;
            background: #9A7B4F;
            margin-bottom: 18pt;
        }

        /* ─── TYPOGRAPHY TOKENS ─── */
        .text-muted { color: #78716C; }
        .text-dark  { color: #1A1615; }
        .text-gold  { color: #9A7B4F; }
        .font-bold  { font-weight: bold; }
        .uppercase  { text-transform: uppercase; }
        .tracking-wide { letter-spacing: 1.5pt; }
        .tracking-wider { letter-spacing: 2pt; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ─── HEADER SECTION ─── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14pt;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }

        .brand-title {
            font-size: 16pt;
            font-weight: bold;
            color: #1A1615;
            letter-spacing: 2.5pt;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .brand-tagline {
            font-size: 6.8pt;
            font-weight: bold;
            color: #9A7B4F;
            letter-spacing: 1.4pt;
            text-transform: uppercase;
            margin-top: 2pt;
            margin-bottom: 6pt;
        }
        .brand-meta {
            font-size: 7.5pt;
            color: #57534E;
            line-height: 1.5;
        }
        .brand-meta strong {
            color: #1A1615;
        }

        .invoice-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1A1615;
            letter-spacing: 2pt;
            text-transform: uppercase;
            text-align: right;
            line-height: 1.1;
        }
        .invoice-subtitle {
            font-size: 6.8pt;
            color: #78716C;
            letter-spacing: 1.2pt;
            text-transform: uppercase;
            text-align: right;
            margin-top: 2pt;
            margin-bottom: 6pt;
        }

        .invoice-meta-box {
            text-align: right;
            font-size: 7.8pt;
            line-height: 1.6;
            color: #57534E;
        }
        .invoice-meta-box strong {
            color: #1A1615;
        }

        .badge-status {
            display: inline-block;
            background: #1A1615;
            color: #FAF8F5;
            font-size: 6.8pt;
            font-weight: bold;
            letter-spacing: 1.4pt;
            text-transform: uppercase;
            padding: 3pt 8pt 2pt 8pt;
            border-radius: 3pt;
            margin-top: 5pt;
        }

        /* ─── ELEGANT DIVIDER ─── */
        .gold-hairline {
            width: 100%;
            height: 1pt;
            background: #E5DFD3;
            margin-bottom: 14pt;
        }

        /* ─── CLIENT & DISPATCH INFORMATION ─── */
        .info-container {
            width: 100%;
            border: 1pt solid #E7E2D7;
            background: #FAF8F5;
            border-radius: 4pt;
            margin-bottom: 14pt;
            border-collapse: collapse;
        }
        .info-container td {
            width: 50%;
            padding: 10pt 14pt;
            vertical-align: top;
        }
        .info-container td:first-child {
            border-right: 1pt solid #E7E2D7;
        }

        .section-label {
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5pt;
            color: #9A7B4F;
            margin-bottom: 4pt;
        }
        .person-name {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1A1615;
            margin-bottom: 2pt;
        }
        .address-text {
            font-size: 7.8pt;
            color: #44403C;
            line-height: 1.5;
        }

        /* ─── LINE ITEMS TABLE ─── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14pt;
        }
        .items-table thead th {
            background: #1A1615;
            color: #FFFFFF;
            font-size: 6.8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.3pt;
            padding: 6.5pt 8pt;
            border: none;
        }
        .items-table tbody td {
            padding: 7.5pt 8pt;
            border-bottom: 1pt solid #EFECE6;
            font-size: 8pt;
            color: #1A1615;
            vertical-align: top;
        }
        .items-table tbody tr:nth-child(even) td {
            background: #FAF8F5;
        }

        .item-name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1A1615;
            margin-bottom: 2pt;
        }
        .item-spec-pill {
            display: inline-block;
            font-size: 6.8pt;
            color: #6B655D;
            background: #EDE8DE;
            padding: 1.5pt 5pt;
            border-radius: 2pt;
            margin-right: 3pt;
            margin-top: 2pt;
        }

        /* ─── BOTTOM SPLIT: ADVANCE CARD & TOTALS ─── */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12pt;
        }
        .bottom-table td {
            vertical-align: top;
            padding: 0;
        }

        .deposit-card {
            border: 1pt solid #E7E2D7;
            background: #FAF8F5;
            border-radius: 4pt;
            padding: 9pt 11pt;
            width: 92%;
        }
        .deposit-header {
            font-size: 6.8pt;
            font-weight: bold;
            color: #9A7B4F;
            letter-spacing: 1.3pt;
            text-transform: uppercase;
            margin-bottom: 4pt;
        }
        .deposit-amount {
            font-size: 12pt;
            font-weight: bold;
            color: #1A1615;
            margin-bottom: 3pt;
        }
        .deposit-body {
            font-size: 7.2pt;
            color: #57534E;
            line-height: 1.5;
        }

        /* Right: Financial Totals Table */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 3.5pt 0;
            font-size: 8pt;
        }
        .totals-table td.lbl {
            text-align: right;
            color: #6B655D;
            padding-right: 12pt;
        }
        .totals-table td.val {
            text-align: right;
            font-weight: bold;
            color: #1A1615;
            width: 85pt;
        }
        .totals-table tr.grand-total td {
            border-top: 1.5pt solid #1A1615;
            border-bottom: 2pt double #1A1615;
            padding: 6pt 0;
            font-size: 10pt;
            font-weight: bold;
            color: #1A1615;
        }
        .totals-table tr.grand-total td.lbl {
            color: #1A1615;
            letter-spacing: 1pt;
            text-transform: uppercase;
        }

        /* ─── TERMS & NOTES ─── */
        .notes-card {
            background: #FAF8F5;
            border: 1pt solid #E7E2D7;
            border-radius: 4pt;
            padding: 8pt 10pt;
            margin-top: 10pt;
            font-size: 7.2pt;
            color: #57534E;
            line-height: 1.5;
        }

        /* ─── FOOTER & DECLARATIONS ─── */
        .footer-declaration {
            border-top: 1pt solid #E7E2D7;
            padding-top: 8pt;
            margin-top: 10pt;
            font-size: 6.8pt;
            color: #8E877D;
            text-align: center;
            line-height: 1.5;
        }
        .footer-declaration strong {
            color: #57534E;
        }
    </style>
</head>
<body>
<div class="page-container">

    @php
        $brandName    = \App\Models\SiteSetting::get('invoice_brand_name', 'Solitaire Fine Jewels');
        $brandTagline = \App\Models\SiteSetting::get('invoice_brand_tagline', 'Certified Diamonds & Bespoke Fine Jewellery Atelier');
        $address      = \App\Models\SiteSetting::get('invoice_address', 'Atelier Plaza, Fine Arts Quarter, Mumbai, India');
        $email        = \App\Models\SiteSetting::get('invoice_email', 'concierge@solitairefinejewels.com');
        $phone        = \App\Models\SiteSetting::get('invoice_phone', '+91 98201 45678');
        $website      = \App\Models\SiteSetting::get('invoice_website', 'www.solitairefinejewels.com');
        $gstin        = trim(\App\Models\SiteSetting::get('invoice_gstin', ''));
        $footerNote   = \App\Models\SiteSetting::get('invoice_footer_note', 'Thank you for choosing Solitaire Fine Jewels Atelier. Quotation valid as specified.');
        
        $subtotal     = $quote->subtotal ?? $quote->items->sum('total');
        $depositAmt   = ($quote->deposit_type?->value === 'percentage')
            ? ($quote->total_amount * $quote->deposit_amount / 100)
            : $quote->deposit_amount;
    @endphp

    {{-- Top Decorative Gold Band --}}
    <div class="top-luxury-bar"></div>

    {{-- ── HEADER: BRAND IDENTITY & QUOTE METADATA ── --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 55%;">
                <div class="brand-title">{{ $brandName }}</div>
                <div class="brand-tagline">{{ $brandTagline }}</div>
                <div class="brand-meta">
                    {!! nl2br(e($address)) !!}<br>
                    @if(!empty($gstin))
                        <strong>GSTIN:</strong> {{ $gstin }} &bull; 
                    @endif
                    <strong>Tel:</strong> {{ $phone }}<br>
                    <strong>Email:</strong> {{ $email }} &bull; <strong>Web:</strong> {{ $website }}
                </div>
            </td>

            <td style="width: 45%;">
                <div class="invoice-title">Quotation</div>
                <div class="invoice-subtitle">Official Atelier Proposal</div>
                <div class="invoice-meta-box">
                    <strong>Quote Ref:</strong> {{ $quote->quote_reference }}<br>
                    <strong>Request Ref:</strong> {{ $quote->request->public_reference }}<br>
                    <strong>Issue Date:</strong> {{ $quote->created_at->format('d M Y') }}<br>
                    @if($quote->valid_until)
                        <strong>Valid Until:</strong> {{ \Carbon\Carbon::parse($quote->valid_until)->format('d M Y') }}<br>
                    @endif
                    <span class="badge-status">{{ $quote->status->getLabel() }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="gold-hairline"></div>

    {{-- ── CLIENT & PROJECT REQUIREMENTS ── --}}
    <table class="info-container" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="section-label">Prepared For (Collector / Client)</div>
                <div class="person-name">{{ $quote->request->name }}</div>
                <div class="address-text">
                    <strong>Email:</strong> {{ $quote->request->email }}<br>
                    @if($quote->request->phone)
                        <strong>Phone:</strong> {{ $quote->request->phone }}<br>
                    @endif
                    @if($quote->request->whatsapp)
                        <strong>WhatsApp:</strong> {{ $quote->request->whatsapp }}<br>
                    @endif
                    <strong>Client Reference:</strong> {{ $quote->request->public_reference }}
                </div>
            </td>
            <td>
                <div class="section-label">Artisan Scope &amp; Specifications</div>
                <div class="person-name">{{ ucfirst(str_replace('_', ' ', $quote->request->project_type)) }}</div>
                <div class="address-text">
                    @if($quote->request->width && $quote->request->height)
                        <strong>Dimensions:</strong> {{ $quote->request->width }} × {{ $quote->request->height }}
                        @if($quote->request->depth) × {{ $quote->request->depth }}@endif
                        {{ strtoupper($quote->request->unit ?? 'cm') }}<br>
                    @endif
                    @if($quote->request->preferred_style)
                        <strong>Design Style:</strong> {{ $quote->request->preferred_style }}<br>
                    @endif
                    @if($quote->request->preferred_colors)
                        <strong>Metal / Tone Palette:</strong> {{ $quote->request->preferred_colors }}<br>
                    @endif
                    @if($quote->estimated_completion)
                        <strong>Estimated Delivery:</strong> {{ $quote->estimated_completion }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- ── LINE ITEMS TABLE ── --}}
    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 24pt;" class="text-center">#</th>
                <th>Artisan Item / Crafting Scope</th>
                <th style="width: 36pt;" class="text-center">Qty</th>
                <th style="width: 80pt;" class="text-right">Unit Price</th>
                <th style="width: 85pt;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $i => $item)
            <tr>
                <td class="text-center text-muted">{{ $i + 1 }}</td>
                <td>
                    <div class="item-name">{{ $item->description }}</div>
                    <span class="item-spec-pill">Handcrafted Atelier</span>
                </td>
                <td class="text-center font-bold">{{ $item->quantity }}</td>
                <td class="text-right text-muted">&#8377; {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right font-bold">&#8377; {{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── BOTTOM SPLIT: DEPOSIT CARD & TOTALS ── --}}
    <table class="bottom-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 52%;">
                <div class="deposit-card">
                    <div class="deposit-header">Advance Deposit Required To Initiate</div>
                    <div class="deposit-amount">&#8377; {{ number_format($depositAmt, 2) }}</div>
                    <div class="deposit-body">
                        @if($quote->deposit_type?->value === 'percentage')
                            {{ $quote->deposit_amount }}% required to procure certified gems and commence casting.
                        @else
                            Fixed advance deposit required to schedule atelier bench time.
                        @endif
                        <br><strong>Authenticity:</strong> All creations are BIS Hallmarked with certificate of authenticity.
                    </div>
                </div>
            </td>

            <td style="width: 48%;">
                <table class="totals-table" cellpadding="0" cellspacing="0">
                    @if($quote->shipping_amount > 0 || $quote->tax_amount > 0 || $quote->discount_amount > 0)
                    <tr>
                        <td class="lbl">Subtotal</td>
                        <td class="val">&#8377; {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @endif
                    @if($quote->shipping_amount > 0)
                    <tr>
                        <td class="lbl">Insured Shipping</td>
                        <td class="val">&#8377; {{ number_format($quote->shipping_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($quote->tax_amount > 0)
                    <tr>
                        <td class="lbl">Taxes (GST 3%)</td>
                        <td class="val">&#8377; {{ number_format($quote->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($quote->discount_amount > 0)
                    <tr>
                        <td class="lbl" style="color: #059669;">Privilege Courtesy</td>
                        <td class="val" style="color: #059669;">- &#8377; {{ number_format($quote->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total">
                        <td class="lbl">Grand Total (INR)</td>
                        <td class="val">&#8377; {{ number_format($quote->total_amount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if($quote->notes)
    <div class="notes-card">
        <strong style="color: #1A1615; text-transform: uppercase; font-size: 6.8pt; letter-spacing: 1.2pt;">Terms &amp; Artisan Notes:</strong><br>
        {{ $quote->notes }}
    </div>
    @endif

    {{-- ── FOOTER DECLARATION ── --}}
    <div class="footer-declaration">
        <strong>{{ $footerNote }}</strong><br>
        Quotation valid until {{ $quote->valid_until ? \Carbon\Carbon::parse($quote->valid_until)->format('d M Y') : 'valid date' }}. Certified diamonds and BIS hallmarked precious metals.<br>
        {{ $brandName }} Atelier &middot; Handcrafted Fine Jewellery &middot; Computer-Generated Legal Quote
    </div>

</div>
</body>
</html>
