<div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm overflow-hidden" style="width: 100%;">
    <style>
        .order-preview-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }
        .order-preview-table thead tr {
            background-color: rgba(243, 244, 246, 0.6);
            border-bottom: 1px solid rgba(229, 231, 235, 0.8);
        }
        :is(.dark) .order-preview-table thead tr {
            background-color: rgba(31, 41, 55, 0.6);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .order-preview-table th {
            padding: 12px 16px;
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6B7280;
        }
        :is(.dark) .order-preview-table th {
            color: #9CA3AF;
        }
        .order-preview-table tbody tr {
            border-bottom: 1px solid rgba(243, 244, 246, 0.8);
            transition: background-color 0.15s ease;
        }
        :is(.dark) .order-preview-table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }
        .order-preview-table tbody tr:hover {
            background-color: rgba(249, 250, 251, 0.8);
        }
        :is(.dark) .order-preview-table tbody tr:hover {
            background-color: rgba(31, 41, 55, 0.4);
        }
        .order-preview-table td {
            padding: 14px 16px;
            vertical-align: middle;
        }
        .order-preview-title {
            font-weight: 600;
            font-size: 13.5px;
            color: #111827;
        }
        :is(.dark) .order-preview-title {
            color: #F9FAFB !important;
        }
        .order-preview-sku {
            font-size: 10.5px;
            font-family: monospace;
            color: #6B7280;
            margin-top: 2px;
        }
        :is(.dark) .order-preview-sku {
            color: #9CA3AF !important;
        }
        .order-preview-qty {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 9999px;
            background: #F3F4F6;
            border: 1px solid #E5E7EB;
            font-size: 11.5px;
            font-weight: 600;
            color: #111827;
        }
        :is(.dark) .order-preview-qty {
            background: #374151;
            border-color: #4B5563;
            color: #F9FAFB !important;
        }
        .order-preview-price {
            color: #374151;
            font-weight: 500;
        }
        :is(.dark) .order-preview-price {
            color: #E5E7EB !important;
        }
        .order-preview-subtotal {
            font-weight: 700;
            color: #111827;
            font-size: 13.5px;
        }
        :is(.dark) .order-preview-subtotal {
            color: #FFFFFF !important;
        }
        .order-preview-tfoot {
            background: rgba(249, 250, 251, 0.9);
            border-top: 1px solid #E5E7EB;
            font-size: 12.5px;
        }
        :is(.dark) .order-preview-tfoot {
            background: rgba(17, 24, 39, 0.8);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .order-preview-tfoot td {
            padding: 8px 16px;
        }
        .order-preview-foot-label {
            color: #6B7280;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.05em;
        }
        :is(.dark) .order-preview-foot-label {
            color: #9CA3AF !important;
        }
        .order-preview-foot-val {
            color: #111827;
            font-weight: 600;
        }
        :is(.dark) .order-preview-foot-val {
            color: #F3F4F6 !important;
        }
        .order-preview-grand-total {
            color: #D97706;
            font-weight: 800;
            font-size: 16px;
        }
        :is(.dark) .order-preview-grand-total {
            color: #FBBF24 !important;
        }
    </style>

    <table class="order-preview-table">
        <thead>
            <tr>
                <th style="padding-left: 20px;">Product Piece</th>
                <th style="text-align: center; width: 130px;">Quantity</th>
                <th style="text-align: right; width: 160px;">Unit Price</th>
                <th style="text-align: right; width: 180px; padding-right: 20px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                @php
                    $img = null;
                    if (!empty($item->product_snapshot['images'][0])) {
                        $img = $item->product_snapshot['images'][0];
                    } elseif ($item->product && !empty($item->product->images[0])) {
                        $img = $item->product->images[0];
                    }
                @endphp
                <tr>
                    {{-- Product Image & Name --}}
                    <td style="padding-left: 20px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="width: 56px; height: 56px; min-width: 56px; max-width: 56px; border-radius: 8px; overflow: hidden; background: #374151; border: 1px solid rgba(255, 255, 255, 0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $item->product_name }}" style="width: 56px; height: 56px; min-width: 56px; max-width: 56px; min-height: 56px; max-height: 56px; object-fit: cover; display: block;">
                                @else
                                    <span style="font-size: 9px; text-transform: uppercase; color: #9CA3AF; font-weight: 700;">Art</span>
                                @endif
                            </div>
                            <div style="min-width: 0;">
                                <div class="order-preview-title">
                                    {{ $item->product_name }}
                                </div>
                                @if($item->sku)
                                    <div class="order-preview-sku">
                                        SKU: {{ $item->sku }}
                                    </div>
                                @endif
                                @if($item->product)
                                    <a href="{{ route('shop.show', $item->product->slug) }}" target="_blank" style="display: inline-block; font-size: 10.5px; color: #F59E0B; text-decoration: underline; margin-top: 4px; font-weight: 500;">
                                        View in Atelier &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Quantity --}}
                    <td style="text-align: center;">
                        <span class="order-preview-qty">
                            × {{ $item->quantity }}
                        </span>
                    </td>

                    {{-- Unit Price --}}
                    <td style="text-align: right;" class="order-preview-price">
                        ₹ {{ number_format($item->unit_price, 2) }}
                    </td>

                    {{-- Subtotal --}}
                    <td style="text-align: right; padding-right: 20px;" class="order-preview-subtotal">
                        ₹ {{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="order-preview-tfoot">
            <tr>
                <td colspan="3" style="text-align: right;" class="order-preview-foot-label">
                    Items Subtotal ({{ $order->items->sum('quantity') }} items):
                </td>
                <td style="text-align: right; padding-right: 20px;" class="order-preview-foot-val">
                    ₹ {{ number_format($order->subtotal, 2) }}
                </td>
            </tr>
            @if($order->tax > 0)
                @php
                    $effectiveRate = ($order->subtotal > 0) ? round(($order->tax / $order->subtotal) * 100, 1) : 0;
                @endphp
                <tr>
                    <td colspan="3" style="text-align: right;" class="order-preview-foot-label">
                        GST ({{ $effectiveRate }}%):
                    </td>
                    <td style="text-align: right; padding-right: 20px;" class="order-preview-foot-val">
                        ₹ {{ number_format($order->tax, 2) }}
                    </td>
                </tr>
            @endif
            @if($order->shipping_fee > 0)
                <tr>
                    <td colspan="3" style="text-align: right;" class="order-preview-foot-label">
                        Protective Shipping:
                    </td>
                    <td style="text-align: right; padding-right: 20px;" class="order-preview-foot-val">
                        ₹ {{ number_format($order->shipping_fee, 2) }}
                    </td>
                </tr>
            @endif
            <tr style="border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 10px; padding-bottom: 10px;">
                <td colspan="3" style="text-align: right; font-weight: 700; text-transform: uppercase; font-size: 11px; padding: 14px 16px;" class="order-preview-title">
                    Grand Total:
                </td>
                <td style="text-align: right; padding-right: 20px; padding-top: 14px; padding-bottom: 14px;" class="order-preview-grand-total">
                    ₹ {{ number_format($order->grand_total, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
