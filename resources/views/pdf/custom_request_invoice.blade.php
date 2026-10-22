<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bespoke Commission Invoice — {{ $customRequest->public_reference }}</title>
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
            width: 100%;
            height: 100%;
            -webkit-print-color-adjust: exact;
        }

        .page-container {
            padding: 24pt 32pt 20pt 32pt;
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

        .badge-paid {
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

        /* ─── BOTTOM SPLIT: CERTIFICATION & TOTALS ─── */
        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12pt;
        }
        .bottom-table td {
            vertical-align: top;
            padding: 0;
        }

        /* Left: Hallmarking & Authenticity Guarantee Card */
        .cert-card {
            border: 1pt solid #E7E2D7;
            background: #FAF8F5;
            border-radius: 4pt;
            padding: 9pt 11pt;
            width: 92%;
        }
        .cert-header {
            font-size: 6.8pt;
            font-weight: bold;
            color: #9A7B4F;
            letter-spacing: 1.3pt;
            text-transform: uppercase;
            margin-bottom: 4pt;
        }
        .cert-body {
            font-size: 7.2pt;
            color: #57534E;
            line-height: 1.55;
        }
        .cert-body strong {
            color: #1A1615;
        }
        .cert-icons-bar {
            margin-top: 6pt;
            padding-top: 5pt;
            border-top: 1pt dashed #E0D9CC;
            font-size: 6.5pt;
            color: #78716C;
            letter-spacing: 0.8pt;
            text-transform: uppercase;
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
        $authNote     = \App\Models\SiteSetting::get('invoice_authenticity_note', 'Every fine jewellery creation is accompanied by an official Certificate of Authenticity (IGI / GIA / BIS Hallmarked).');
        $footerNote   = \App\Models\SiteSetting::get('invoice_footer_note', 'Thank you for choosing Solitaire Fine Jewels. This is an official bespoke fine jewellery tax invoice.');
        
        $cleanStatus  = match(strtolower(str_replace(' ', '_', $paymentStatus))) {
            'fully_paid', 'paid' => 'PAID IN FULL',
            'advance_paid', 'partially_paid' => 'ADVANCE PAID',
            'unpaid' => 'PAYMENT PENDING',
            default => strtoupper($paymentStatus),
        };
        $currSymbol = $currencySymbol ?: '₹';
        $valTotal   = (float)$totalAmount;
        $gstTax     = round(($valTotal * 3) / 103, 2);
        $subtotal   = $valTotal - $gstTax;
    @endphp

    {{-- Top Decorative Gold Band --}}
    <div class="top-luxury-bar"></div>

    {{-- ── HEADER: BRAND IDENTITY & INVOICE METADATA ── --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            {{-- Brand Identity Left --}}
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

            {{-- Invoice Credentials Right --}}
            <td style="width: 45%;">
                <div class="invoice-title">Bespoke Invoice</div>
                <div class="invoice-subtitle">Custom Fine Jewellery Commission</div>
                <div class="invoice-meta-box">
                    <strong>Invoice No:</strong> INV-{{ str_replace('CR-', '', $customRequest->public_reference) }}<br>
                    <strong>Reference Code:</strong> {{ $customRequest->public_reference }}<br>
                    <strong>Issue Date:</strong> {{ $invoiceDate }}<br>
                    <strong>Payment Mode:</strong> {{ strtoupper($paymentMode ?: 'DIRECT BANK TRANSFER') }}<br>
                    <span class="badge-paid">{{ $cleanStatus }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="gold-hairline"></div>

    {{-- ── BUYER (COLLECTOR) & SHIPPING DESTINATION ── --}}
    <table class="info-container" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="section-label">Billed To (Collector / Client)</div>
                <div class="person-name">
                    {{ $customRequest->name ?: 'Valued Client' }}
                </div>
                <div class="address-text">
                    <strong>Email:</strong> {{ $customRequest->email }}<br>
                    <strong>Contact Phone:</strong> {{ $customRequest->whatsapp ?: ($customRequest->phone ?: 'N/A') }}<br>
                    @if($customRequest->shipping_address ?? false)
                        <strong>Address:</strong> {{ $customRequest->shipping_address }}<br>
                    @endif
                    <strong>Commission Type:</strong> Custom Private Bespoke
                </div>
            </td>
            <td>
                <div class="section-label">Commission &amp; Delivery Details</div>
                <div class="person-name">
                    {{ ucfirst(str_replace('_', ' ', $customRequest->project_type ?: 'Bespoke Fine Jewellery')) }}
                </div>
                <div class="address-text">
                    <strong>Destination:</strong> {{ $customRequest->city ?? 'Direct Concierge Delivery' }}<br>
                    @if($customRequest->budget_range)
                        <strong>Agreed Budget Range:</strong> {{ $customRequest->budget_range }}<br>
                    @endif
                    <strong>Delivery Standard:</strong> 100% Fully Insured Transit (Transit Guarded)
                </div>
            </td>
        </tr>
    </table>

    {{-- ── LINE ITEMS TABLE ── --}}
    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 24pt;" class="text-center">#</th>
                <th>Artisan Commission Description &amp; Specifications</th>
                <th style="width: 36pt;" class="text-center">Qty</th>
                <th style="width: 95pt;" class="text-right">Agreed Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center text-muted">1</td>
                <td>
                    <div class="item-name">{{ $itemTitle ?: 'Bespoke Fine Jewellery Commission' }}</div>
                    @if($customRequest->idea_description)
                        <div style="font-size: 7.2pt; color: #78716C; margin-top: 3pt; line-height: 1.45;">
                            {{ $customRequest->idea_description }}
                        </div>
                    @endif
                    <div style="margin-top: 4pt;">
                        <span class="item-spec-pill">Bespoke Atelier</span>
                        <span class="item-spec-pill">Handcrafted</span>
                        <span class="item-spec-pill">Certified Gems</span>
                    </div>
                </td>
                <td class="text-center font-bold">1</td>
                <td class="text-right font-bold">{{ $currSymbol }} {{ number_format($valTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── BOTTOM SPLIT: CERTIFICATION GUARANTEE + FINANCIAL TOTALS ── --}}
    <table class="bottom-table" cellpadding="0" cellspacing="0">
        <tr>
            {{-- Left: Authenticity & Hallmarking Card --}}
            <td style="width: 52%;">
                <div class="cert-card">
                    <div class="cert-header">Atelier Authenticity &amp; Hallmarking</div>
                    <div class="cert-body">
                        {{ $authNote }}<br>
                        <strong>Hallmarking:</strong> BIS Hallmark certified for precious metal purity.<br>
                        <strong>Diamonds:</strong> 100% Conflict-free, certified by leading gemological institutes.<br>
                        <strong>Payment Status:</strong> {{ $cleanStatus }}<br>
                        @if(!empty($invoiceNotes))
                            <strong>Atelier Note:</strong> {{ $invoiceNotes }}
                        @endif
                    </div>
                    <div class="cert-icons-bar">
                        BIS HALLMARKED &bull; IGI / GIA CERTIFIED &bull; 100% INSURED DISPATCH
                    </div>
                </div>
            </td>

            {{-- Right: Financial Breakdown --}}
            <td style="width: 48%;">
                <table class="totals-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="lbl">Total Project Value</td>
                        <td class="val">{{ $currSymbol }} {{ number_format($valTotal, 2) }}</td>
                    </tr>
                    @if($advanceAmount > 0)
                        <tr>
                            <td class="lbl" style="color: #059669;">Advance Received</td>
                            <td class="val" style="color: #059669;">- {{ $currSymbol }} {{ number_format($advanceAmount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td class="lbl">
                            {{ $balanceDue > 0 ? 'Balance Due' : 'Total Amount Paid' }}
                        </td>
                        <td class="val">
                            {{ $currSymbol }} {{ number_format($balanceDue > 0 ? $balanceDue : $valTotal, 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ── FOOTER DECLARATION ── --}}
    <div class="footer-declaration">
        <strong>{{ $footerNote }}</strong><br>
        All bespoke creations are individually hallmarked by the Bureau of Indian Standards and accompanied by gemological lab certs.<br>
        {{ $brandName }} Atelier &middot; Handcrafted Fine Jewellery &middot; Computer-Generated Legal Document
    </div>

</div>
</body>
</html>
