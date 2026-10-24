<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation — Solitaire Fine Jewels</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAF8F5; color: #1C1917; margin: 0; padding: 0; -webkit-text-size-adjust: none; }
        .container { max-width: 600px; margin: 30px auto; background-color: #FFFFFF; border: 1px solid #E5DFD3; border-radius: 16px; overflow: hidden; }
        .header { background-color: #12100E; padding: 36px 30px; text-align: center; color: #FAF8F5; }
        .header h1 { font-family: Georgia, serif; font-size: 28px; font-weight: normal; margin: 0 0 6px; letter-spacing: 0.05em; font-style: italic; }
        .header p { font-size: 11px; text-transform: uppercase; letter-spacing: 0.25em; color: #8E877D; margin: 0; }
        .content { padding: 36px 30px; }
        .badge { display: inline-block; background-color: #F4EFE6; color: #8E7558; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2em; padding: 5px 12px; border-radius: 9999px; margin-bottom: 16px; }
        .order-title { font-size: 22px; margin: 0 0 12px; font-family: Georgia, serif; font-weight: normal; color: #1C1917; }
        .lead { font-size: 14px; line-height: 1.6; color: #524C46; margin: 0 0 24px; }
        .summary-card { background-color: #FAF8F5; border: 1px solid #EAE4D9; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .table th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.15em; color: #8E877D; padding-bottom: 8px; border-bottom: 1px solid #E5DFD3; }
        .table td { padding: 12px 0; font-size: 13px; border-bottom: 1px solid #F0ECE1; }
        .table td.total { font-weight: bold; font-size: 15px; border-top: 2px solid #E5DFD3; border-bottom: none; }
        .btn { display: inline-block; background-color: #1C1917 !important; color: #FAF8F5 !important; text-decoration: none !important; padding: 14px 34px; border-radius: 9999px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2em; margin-top: 10px; }
        .btn, .btn:visited, .btn:hover, .btn:active, .btn span { color: #FAF8F5 !important; text-decoration: none !important; }
        .footer { background-color: #FAF8F5; border-top: 1px solid #E5DFD3; padding: 20px 30px; text-align: center; font-size: 11px; color: #8E877D; }
        /* Prevent auto blue links in Gmail/Apple Mail for address & numbers */
        a[x-apple-data-detectors], .address-block a, u + #body a { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
    </style>
</head>
<body id="body">
    <div class="container">
        <div class="header">
            <h1 style="color: #FAF8F5 !important;">Solitaire Fine Jewels</h1>
            <p style="color: #8E877D !important;">Certified Diamonds &middot; Fine Jewellery Atelier</p>
        </div>

        <div class="content">
            <span class="badge">Order Confirmed</span>
            <h2 class="order-title">Thank you for your acquisition.</h2>
            <p class="lead">
                Your fine jewellery creation has been registered under order reference <strong>{{ $order->order_reference }}</strong>. Our master craftsmen will prepare your piece with the highest care.
            </p>

            <div class="summary-card">
                <table style="width: 100%; font-size: 12px; margin-bottom: 12px;">
                    <tr>
                        <td><strong>Order Reference:</strong> {{ $order->order_reference }}</td>
                        <td style="text-align: right;"><strong>Date:</strong> {{ $order->created_at->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Payment Method:</strong> {{ strtoupper($order->payment_method) }}</td>
                        <td style="text-align: right;"><strong>Status:</strong> {{ strtoupper($order->payment_status) }}</td>
                    </tr>
                </table>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Acquired Piece</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Price</th>
                        </tr>
                    </thead>
                    @php
                        $orderCurrency = $order->currency ?: 'INR';
                        $orderSymbol = $order->currency_symbol ?: ($orderCurrency === 'USD' ? '$' : ($orderCurrency === 'EUR' ? '€' : ($orderCurrency === 'GBP' ? '£' : ($orderCurrency === 'AED' ? 'AED ' : '₹'))));
                        $decimals = ($orderCurrency === 'INR') ? 0 : 2;
                    @endphp
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name }}</strong>
                                    @php
                                        $options = $item->options ?? [];
                                        $color = $options['metal_color'] ?? null;
                                        $purity = $options['gold_purity'] ?? null;
                                        $size = $options['size'] ?? null;
                                    @endphp
                                    @if($color || $purity || $size)
                                        <div style="font-size: 11px; color: #7B746B; margin-top: 4px; line-height: 1.4;">
                                            @if($color)
                                                <span style="display: inline-block; margin-right: 8px;"><strong>Tone:</strong> {{ $color }}</span>
                                            @endif
                                            @if($purity)
                                                <span style="display: inline-block; margin-right: 8px;"><strong>Purity:</strong> {{ $purity }}</span>
                                            @endif
                                            @if($size)
                                                <span style="display: inline-block;"><strong>Size:</strong> {{ $size }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ $item->quantity }}</td>
                                <td style="text-align: right;">{{ $orderSymbol }} {{ number_format($item->subtotal, $decimals) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="total">Grand Total ({{ $orderCurrency }})</td>
                            <td class="total" style="text-align: right;">
                                {{ $orderSymbol }} {{ number_format($order->grand_total, $decimals) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="address-block" style="font-size: 12px; color: #524C46; margin-bottom: 24px; line-height: 1.6;">
                <strong style="text-transform: uppercase; font-size: 10px; letter-spacing: 0.15em; color: #8E877D; display: block; margin-bottom: 6px;">Destination Crate:</strong>
                <span style="color: #524C46 !important; text-decoration: none !important;">{{ $order->shipping_address_snapshot['full_name'] ?? '' }}</span><br>
                <span style="color: #524C46 !important; text-decoration: none !important;">{{ $order->shipping_address_snapshot['address_line_1'] ?? '' }}</span><br>
                <span style="color: #524C46 !important; text-decoration: none !important;">{{ $order->shipping_address_snapshot['city'] ?? '' }}, {{ $order->shipping_address_snapshot['postal_code'] ?? '' }}</span><br>
                <span style="color: #524C46 !important; text-decoration: none !important;">{{ $order->shipping_address_snapshot['country'] ?? '' }}</span>
            </div>

            <div style="text-align: center; margin: 28px 0 10px;">
                <a href="{{ route('tracking.index', ['order_reference' => $order->order_reference, 'email' => $order->email]) }}"
                   class="btn"
                   target="_blank"
                   style="display: inline-block; background-color: #1C1917; color: #FAF8F5 !important; text-decoration: none !important; padding: 14px 34px; border-radius: 9999px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2em; line-height: 1; mso-padding-alt: 0;">
                    <span style="color: #FAF8F5 !important; text-decoration: none !important; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2em;">Track Order Live &rarr;</span>
                </a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Solitaire Fine Jewels Atelier &middot; Atelier Plaza, Mumbai<br>
            All rights reserved. Certified diamonds and hallmarked gold jewellery.
        </div>
    </div>
</body>
</html>
