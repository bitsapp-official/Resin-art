<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Payment Receipt & Tax Invoice — {{ $invoice->invoice_number }}</title>
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
        $footerNote   = \App\Models\SiteSetting::get('invoice_footer_note', 'Thank you for choosing Solitaire Fine Jewels. This is an official verified payment receipt & tax invoice.');
        
        $balanceDue   = max(0, (float)$invoice->total_amount - (float)$invoice->paid_amount);
        $statusLabel  = match(strtolower($invoice->payment_status)) {
            'fully_paid', 'paid' => 'PAID IN FULL',
            'advance_paid', 'partially_paid' => 'PARTIAL PAYMENT RECORDED',
            'unpaid' => 'PAYMENT PENDING',
            default => str_replace('_', ' ', strtoupper($invoice->payment_status)),
        };
        $isPaid       = in_array($invoice->payment_status, ['advance_paid', 'fully_paid', 'paid', 'partially_paid']);
        $docTitle     = $isPaid ? 'Payment Receipt' : 'Tax Invoice';

        $grandTotal = (float)$invoice->total_amount;
        $gstTax     = round(($grandTotal * 3) / 103, 2);
        $subtotal   = $grandTotal - $gstTax;
        $currSymbol = $invoice->currency_symbol ?: '₹';
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
                <div class="invoice-title">{{ $docTitle }}</div>
                <div class="invoice-subtitle">Official Proof of Payment</div>
                <div class="invoice-meta-box">
                    <strong>{{ $isPaid ? 'Receipt No:' : 'Invoice No:' }}</strong> {{ $invoice->invoice_number }}<br>
                    @if($invoice->customRequest)
                        <strong>Reference Code:</strong> {{ $invoice->customRequest->public_reference }}<br>
                    @endif
                    <strong>Issue Date:</strong> {{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : now()->format('d M Y') }}<br>
                    <strong>Payment Mode:</strong> {{ strtoupper($invoice->payment_method ?: 'Direct Bank Transfer') }}<br>
                    <span class="badge-paid">{{ $statusLabel }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="gold-hairline"></div>

    {{-- ── BUYER (COLLECTOR) & SHIPPING DESTINATION ── --}}
    <table class="info-container" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="section-label">Billed To (Collector / Buyer)</div>
                <div class="person-name">
                    {{ $invoice->client_name ?: 'Valued Collector' }}
                </div>
                <div class="address-text">
                    @if($invoice->client_address)
                        {!! nl2br(e($invoice->client_address)) !!}<br>
                    @endif
                    @if($invoice->client_email)
                        <strong>Email:</strong> {{ $invoice->client_email }}<br>
                    @endif
                    @if($invoice->client_phone)
                        <strong>Contact Phone / WA:</strong> {{ $invoice->client_phone }}
                    @endif
                </div>
            </td>
            <td>
                <div class="section-label">Shipping / Delivery Destination</div>
                <div class="person-name">
                    {{ $invoice->client_name ?: 'Valued Collector' }}
                </div>
                <div class="address-text">
                    <strong>Destination:</strong> {{ $invoice->client_address ?: 'Direct Concierge Delivery' }}<br>
                    @if($invoice->customRequest)
                        <strong>Project Type:</strong> {{ ucfirst(str_replace('_', ' ', $invoice->customRequest->project_type ?? 'Bespoke Commission')) }}<br>
                        <strong>Custom Ref:</strong> {{ $invoice->customRequest->public_reference }}<br>
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
                <th>Artisan Creation &amp; Metallurgical Specifications</th>
                <th style="width: 36pt;" class="text-center">Qty</th>
                <th style="width: 80pt;" class="text-right">Unit Price</th>
                <th style="width: 85pt;" class="text-right">Subtotal</th>
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
                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <div class="item-name">{{ $item['name'] ?? 'Bespoke Fine Jewellery Piece' }}</div>
                            @if(!empty($item['description']))
                                <div style="font-size: 7.2pt; color: #78716C; margin-top: 2pt;">
                                    {{ $item['description'] }}
                                </div>
                            @endif
                        </td>
                        <td class="text-center font-bold">{{ (int)$qty }}</td>
                        <td class="text-right text-muted">{{ $currSymbol }} {{ number_format($unitPrice, 2) }}</td>
                        <td class="text-right font-bold">{{ $currSymbol }} {{ number_format($rowTotal, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="text-center text-muted">1</td>
                    <td>
                        <div class="item-name">{{ $invoice->item_title ?: 'Bespoke Fine Jewellery Commission' }}</div>
                        @if($invoice->item_description)
                            <div style="font-size: 7.2pt; color: #78716C; margin-top: 2pt;">
                                {{ $invoice->item_description }}
                            </div>
                        @else
                            <div style="font-size: 7.2pt; color: #78716C; margin-top: 2pt;">
                                Certified fine diamond jewellery creation handcrafted in our master atelier.
                            </div>
                        @endif
                    </td>
                    <td class="text-center font-bold">1</td>
                    <td class="text-right text-muted">{{ $currSymbol }} {{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="text-right font-bold">{{ $currSymbol }} {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            @endif
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
                        @if(!empty($invoice->payment_reference))
                            <strong>Transaction ID:</strong> {{ $invoice->payment_reference }}<br>
                        @endif
                        <strong>Payment Status:</strong> {{ $invoice->payment_status === 'fully_paid' ? 'PAID IN FULL (100%)' : 'ADVANCE / PARTIAL' }}
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
                        <td class="lbl">Taxable Value (Net)</td>
                        <td class="val">{{ $currSymbol }} {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Taxes (GST 3% Included)</td>
                        <td class="val">{{ $currSymbol }} {{ number_format($gstTax, 2) }}</td>
                    </tr>
                    @if($invoice->paid_amount > 0 && $invoice->paid_amount < $invoice->total_amount)
                        <tr>
                            <td class="lbl" style="color: #059669;">Amount Paid to Date</td>
                            <td class="val" style="color: #059669;">- {{ $currSymbol }} {{ number_format($invoice->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl" style="color: #DC2626; font-weight: bold;">Outstanding Balance</td>
                            <td class="val" style="color: #DC2626;">{{ $currSymbol }} {{ number_format($balanceDue, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td class="lbl">Grand Total (INR)</td>
                        <td class="val">{{ $currSymbol }} {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ── FOOTER DECLARATION ── --}}
    <div class="footer-declaration">
        <strong>{{ $footerNote }}</strong><br>
        All precious jewellery pieces are BIS Hallmarked and accompanied by authentic certification cards.<br>
        {{ $brandName }} Atelier &middot; Handcrafted Fine Jewellery &middot; Computer-Generated Legal Document
    </div>

</div>
</body>
</html>
