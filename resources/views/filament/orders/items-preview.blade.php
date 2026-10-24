@php
    $order = $order ?? ($record ?? (isset($getRecord) ? $getRecord() : null));
@endphp

@if(!$order || $order->items->isEmpty())
    <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
        No fine jewellery items found in this order.
    </div>
@else
<div class="order-preview-wrapper">
    <style>
        .order-preview-wrapper {
            border-radius: 12px;
            border: 1px solid #E5E7EB;
            background: #FFFFFF;
            overflow: hidden;
            width: 100%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        :is(.dark) .order-preview-wrapper {
            border-color: rgba(255, 255, 255, 0.08);
            background: #11141A;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }
        .order-preview-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }
        .order-preview-table thead tr {
            background: #F9FAFB;
            border-bottom: 1px solid #E5E7EB;
        }
        :is(.dark) .order-preview-table thead tr {
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .order-preview-table th {
            padding: 13px 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #6B7280;
        }
        :is(.dark) .order-preview-table th {
            color: #9CA3AF;
        }
        .order-preview-table tbody tr {
            border-bottom: 1px solid #F3F4F6;
            transition: background 0.15s ease;
        }
        :is(.dark) .order-preview-table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .order-preview-table tbody tr:hover {
            background: #FAFAFA;
        }
        :is(.dark) .order-preview-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        .order-preview-table td {
            padding: 18px 20px;
            vertical-align: middle;
        }

        /* Thumbnail */
        .order-item-thumb {
            width: 68px;
            height: 68px;
            min-width: 68px;
            max-width: 68px;
            border-radius: 10px;
            overflow: hidden;
            background: #F3F4F6;
            border: 1px solid rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        :is(.dark) .order-item-thumb {
            background: #181B22;
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        .order-item-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.25s ease;
        }
        .order-preview-table tbody tr:hover .order-item-thumb img {
            transform: scale(1.05);
        }

        /* Category & SKU */
        .order-category-tag {
            display: inline-flex;
            align-items: center;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #AD9575;
            background: rgba(173, 149, 117, 0.14);
            border: 1px solid rgba(173, 149, 117, 0.25);
            padding: 1px 7px;
            border-radius: 4px;
        }
        :is(.dark) .order-category-tag {
            color: #C5A880;
            background: rgba(173, 149, 117, 0.12);
            border-color: rgba(173, 149, 117, 0.25);
        }
        .order-sku-tag {
            display: inline-flex;
            align-items: center;
            font-size: 10px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #6B7280;
            background: #F3F4F6;
            border: 1px solid #E5E7EB;
            padding: 2px 7px;
            border-radius: 5px;
        }
        :is(.dark) .order-sku-tag {
            color: #9CA3AF;
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
        }

        /* Title */
        .order-item-name {
            font-size: 14.5px;
            font-weight: 600;
            color: #111827;
            letter-spacing: -0.01em;
            line-height: 1.35;
            margin-top: 5px;
            margin-bottom: 7px;
        }
        :is(.dark) .order-item-name {
            color: #F9FAFB;
        }

        /* Specifications Grid (No emojis, ultra-clean luxury tags) */
        .order-specs-grid {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .order-spec-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 11px;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            color: #374151;
            line-height: 1.25;
        }
        :is(.dark) .order-spec-chip {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            color: #E4E4E7;
        }
        .order-spec-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9CA3AF;
        }
        :is(.dark) .order-spec-label {
            color: #71717A;
        }
        .order-spec-val {
            font-weight: 600;
            color: #1F2937;
        }
        :is(.dark) .order-spec-val {
            color: #F4F4F5;
        }

        /* Quantity Badge */
        .order-qty-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            padding: 4px 12px;
            border-radius: 6px;
            background: #F3F4F6;
            border: 1px solid #E5E7EB;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            font-variant-numeric: tabular-nums;
        }
        :is(.dark) .order-qty-badge {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: #E4E4E7;
        }

        /* Price Typography */
        .order-price-val {
            font-size: 13.5px;
            font-weight: 500;
            color: #4B5563;
            font-variant-numeric: tabular-nums;
        }
        :is(.dark) .order-price-val {
            color: #A1A1AA;
        }
        .order-subtotal-val {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            font-variant-numeric: tabular-nums;
        }
        :is(.dark) .order-subtotal-val {
            color: #FFFFFF;
        }

        /* Footer & Totals */
        .order-preview-tfoot {
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
            font-size: 12.5px;
        }
        :is(.dark) .order-preview-tfoot {
            background: rgba(255, 255, 255, 0.02);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .order-preview-tfoot td {
            padding: 9px 20px;
        }
        .order-foot-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #6B7280;
        }
        :is(.dark) .order-foot-label {
            color: #9CA3AF;
        }
        .order-foot-val {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            font-variant-numeric: tabular-nums;
        }
        :is(.dark) .order-foot-val {
            color: #F3F4F6;
        }
        .order-grand-total-row {
            border-top: 1px solid #E5E7EB;
        }
        :is(.dark) .order-grand-total-row {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .order-grand-total-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.1em;
            color: #111827;
        }
        :is(.dark) .order-grand-total-label {
            color: #FFFFFF;
        }
        .order-grand-total-val {
            font-size: 16px;
            font-weight: 800;
            color: #B45309;
            font-variant-numeric: tabular-nums;
        }
        :is(.dark) .order-grand-total-val {
            color: #FBBF24;
        }
    </style>

    <table class="order-preview-table">
        <thead>
            <tr>
                <th style="padding-left: 20px;">Product Piece & Customizations</th>
                <th style="text-align: center; width: 130px;">Quantity</th>
                <th style="text-align: right; width: 160px;">Unit Price</th>
                <th style="text-align: right; width: 180px; padding-right: 20px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                @php
                    $options = is_array($item->options) ? $item->options : (json_decode($item->options, true) ?: []);
                    $color = $options['metal_color'] ?? ($options['color'] ?? null);
                    $purity = $options['gold_purity'] ?? ($options['purity'] ?? null);
                    $size = $options['size'] ?? null;
                    $diamondCarat = $options['diamond_carat'] ?? ($item->product?->diamond_carat ?? null);

                    // Category
                    $category = $item->product?->category?->name ?? ($item->product_snapshot['category']['name'] ?? ($item->product_snapshot['category'] ?? null));

                    // Image selection (match with chosen color if available)
                    $img = $item->primary_image_url;

                    // Direct link with exact customer options pre-selected
                    $queryParams = [];
                    if ($color) $queryParams['color'] = $color;
                    if ($purity) $queryParams['purity'] = $purity;
                    if ($size) $queryParams['size'] = $size;
                    $productSlug = $item->product?->slug ?? ($item->product_snapshot['slug'] ?? null);
                    $productUrl = null;
                    if ($productSlug) {
                        try {
                            $productUrl = route('shop.show', array_merge(['slug' => $productSlug], $queryParams));
                        } catch (\Throwable $e) {
                            $productUrl = url('/shop/' . urlencode($productSlug) . ($queryParams ? '?' . http_build_query($queryParams) : ''));
                        }
                    }

                    // Color Swatch Disc Style
                    $colorBg = '#D4AF37';
                    if ($color) {
                        $lc = strtolower($color);
                        if (str_contains($lc, 'rose')) $colorBg = 'linear-gradient(135deg, #FAD0C4, #E8A598, #C97D6B)';
                        elseif (str_contains($lc, 'white') || str_contains($lc, 'silver') || str_contains($lc, 'platinum')) $colorBg = 'linear-gradient(135deg, #FFFFFF, #E8E8E8, #BDBDBD)';
                        else $colorBg = 'linear-gradient(135deg, #FFE082, #D4AF37, #B8860B)';
                    }
                @endphp
                <tr>
                    {{-- Product Image & Name & Specific Customizations --}}
                    <td style="padding-left: 20px;">
                        <div style="display: flex; align-items: flex-start; gap: 16px;">
                            {{-- Product Thumbnail with Selected Color --}}
                            <div class="order-item-thumb">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $item->product_name }}">
                                @else
                                    <span style="font-size: 10px; text-transform: uppercase; color: #9CA3AF; font-weight: 700; letter-spacing: 0.05em;">Jewel</span>
                                @endif
                            </div>

                            <div style="min-width: 0; flex: 1;">
                                {{-- Category & SKU row --}}
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    @if($category)
                                        <span class="order-category-tag">
                                            {{ $category }}
                                        </span>
                                    @endif
                                    @if($item->sku)
                                        <span class="order-sku-tag">
                                            SKU: {{ $item->sku }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Product Name --}}
                                <div class="order-item-name">
                                    {{ $item->product_name }}
                                </div>

                                {{-- Specific Customization Chips (Tone, Purity, Size, Diamond) - NO EMOJIS --}}
                                <div class="order-specs-grid">
                                    @if($color)
                                        <div class="order-spec-chip">
                                            <span class="order-spec-label">Tone:</span>
                                            <span style="width: 8px; height: 8px; border-radius: 9999px; background: {{ $colorBg }}; display: inline-block; box-shadow: 0 0 3px rgba(0,0,0,0.3); border: 1px solid rgba(255, 255, 255, 0.2);"></span>
                                            <span class="order-spec-val">{{ $color }}</span>
                                        </div>
                                    @endif

                                    @if($purity)
                                        <div class="order-spec-chip">
                                            <span class="order-spec-label">Purity:</span>
                                            <span class="order-spec-val">{{ $purity }}</span>
                                        </div>
                                    @endif

                                    @if($size)
                                        <div class="order-spec-chip">
                                            <span class="order-spec-label">Size:</span>
                                            <span class="order-spec-val">{{ $size }}</span>
                                        </div>
                                    @endif

                                    @if($diamondCarat)
                                        <div class="order-spec-chip">
                                            <span class="order-spec-label">Diamond:</span>
                                            <span class="order-spec-val">{{ $diamondCarat }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Link to view in store with exact options preselected --}}
                                @if($productUrl)
                                    <div style="margin-top: 6px;">
                                        <a href="{{ $productUrl }}" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #AD9575; text-decoration: underline; font-weight: 600;">
                                            <span>View in Store ({{ $color ? $color : '' }}{{ $color && $purity ? ' · ' : '' }}{{ $purity ? $purity : '' }}{{ ($color || $purity) && $size ? ' · ' : '' }}{{ $size ?: '' }})</span> &rarr;
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Quantity --}}
                    <td style="text-align: center;">
                        <span class="order-qty-badge">
                            × {{ $item->quantity }}
                        </span>
                    </td>

                    {{-- Unit Price --}}
                    <td style="text-align: right;" class="order-price-val">
                        {{ $order->currency_symbol ?: '₹' }} {{ number_format($item->unit_price, 2) }}
                    </td>

                    {{-- Subtotal --}}
                    <td style="text-align: right; padding-right: 20px;" class="order-subtotal-val">
                        {{ $order->currency_symbol ?: '₹' }} {{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="order-preview-tfoot">
            <tr>
                <td colspan="3" style="text-align: right;" class="order-foot-label">
                    Items Subtotal ({{ $order->items->sum('quantity') }} {{ Str::plural('item', $order->items->sum('quantity')) }}):
                </td>
                <td style="text-align: right; padding-right: 20px;" class="order-foot-val">
                    {{ $order->currency_symbol ?: '₹' }} {{ number_format($order->subtotal, 2) }}
                </td>
            </tr>
            @php
                $itemTax = (float)$order->tax;
                if ($itemTax >= (float)$order->grand_total || $itemTax <= 0) {
                    $itemTax = round((float)$order->grand_total - ((float)$order->grand_total / 1.03), 2);
                }
            @endphp
            @if($itemTax > 0)
                <tr>
                    <td colspan="3" style="text-align: right;" class="order-foot-label">
                        GST (3% Included):
                    </td>
                    <td style="text-align: right; padding-right: 20px;" class="order-foot-val">
                        {{ $order->currency_symbol ?: '₹' }} {{ number_format($itemTax, 2) }}
                    </td>
                </tr>
            @endif
            @if($order->shipping_fee > 0)
                <tr>
                    <td colspan="3" style="text-align: right;" class="order-foot-label">
                        Insured Atelier Shipping:
                    </td>
                    <td style="text-align: right; padding-right: 20px;" class="order-foot-val">
                        {{ $order->currency_symbol ?: '₹' }} {{ number_format($order->shipping_fee, 2) }}
                    </td>
                </tr>
            @endif
            <tr class="order-grand-total-row">
                <td colspan="3" style="text-align: right; padding: 14px 20px;" class="order-grand-total-label">
                    Grand Total ({{ $order->currency ?: 'INR' }}):
                </td>
                <td style="text-align: right; padding-right: 20px; padding-top: 14px; padding-bottom: 14px;" class="order-grand-total-val">
                    {{ $order->currency_symbol ?: '₹' }} {{ number_format($order->grand_total, 2) }}
                </td>
            </tr>
            @if($order->inr_grand_total && $order->currency !== 'INR')
                <tr>
                    <td colspan="4" style="text-align: right; padding: 8px 20px; font-size: 11px; color: #78716c; background-color: #fbfaf8;">
                        Base Valuation Settled: <strong>₹ {{ number_format($order->inr_grand_total, 2) }} INR</strong> (Locked Rate: 1 {{ $order->currency }} = ₹{{ number_format($order->exchange_rate, 2) }})
                    </td>
                </tr>
            @endif
        </tfoot>
    </table>
</div>
@endif
