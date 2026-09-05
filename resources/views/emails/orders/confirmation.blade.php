<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation — Maison Résine</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAF8F5; color: #1C1917; margin: 0; padding: 0; }
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
        .btn { display: inline-block; background-color: #1C1917; color: #FAF8F5; text-decoration: none; padding: 14px 28px; border-radius: 9999px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2em; margin-top: 10px; }
        .footer { background-color: #FAF8F5; border-top: 1px solid #E5DFD3; padding: 20px 30px; text-align: center; font-size: 11px; color: #8E877D; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Maison Résine</h1>
            <p>Atelier &middot; Bordeaux</p>
        </div>

        <div class="content">
            <span class="badge">Order Confirmed</span>
            <h2 class="order-title">Thank you for your acquisition.</h2>
            <p class="lead">
                Your handcrafted resin piece has been registered under order reference <strong>{{ $order->order_reference }}</strong>. Our artisans will prepare your piece with the highest care.
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
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td style="text-align: center;">{{ $item->quantity }}</td>
                                <td style="text-align: right;">₹ {{ number_format($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="total">Grand Total</td>
                            <td class="total" style="text-align: right;">₹ {{ number_format($order->grand_total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="font-size: 12px; color: #524C46; margin-bottom: 24px;">
                <strong style="text-transform: uppercase; font-size: 10px; letter-spacing: 0.15em; color: #8E877D; display: block; margin-bottom: 4px;">Destination Crate:</strong>
                {{ $order->shipping_address_snapshot['full_name'] ?? '' }}<br>
                {{ $order->shipping_address_snapshot['address_line_1'] ?? '' }}<br>
                {{ $order->shipping_address_snapshot['city'] ?? '' }}, {{ $order->shipping_address_snapshot['postal_code'] ?? '' }}<br>
                {{ $order->shipping_address_snapshot['country'] ?? '' }}
            </div>

            <div style="text-align: center;">
                <a href="{{ route('tracking.index', ['order_reference' => $order->order_reference, 'email' => $order->email]) }}" class="btn">
                    Track Order Live &rarr;
                </a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Maison R&eacute;sine Atelier &middot; 14 rue des &Eacute;toiles, 33000 Bordeaux<br>
            All rights reserved. Handcrafted in limited series.
        </div>
    </div>
</body>
</html>
