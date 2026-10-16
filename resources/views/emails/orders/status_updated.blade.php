<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update — Solitaire Fine Jewels</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FAF8F5; color: #1C1917; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background-color: #FFFFFF; border: 1px solid #E5DFD3; border-radius: 16px; overflow: hidden; }
        .header { background-color: #12100E; padding: 36px 30px; text-align: center; color: #FAF8F5; }
        .header h1 { font-family: Georgia, serif; font-size: 28px; font-weight: normal; margin: 0 0 6px; letter-spacing: 0.05em; font-style: italic; }
        .header p { font-size: 11px; text-transform: uppercase; letter-spacing: 0.25em; color: #8E877D; margin: 0; }
        .content { padding: 36px 30px; }
        .badge { display: inline-block; background-color: #1C1917; color: #FFFFFF; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2em; padding: 6px 14px; border-radius: 9999px; margin-bottom: 16px; }
        .order-title { font-size: 22px; margin: 0 0 12px; font-family: Georgia, serif; font-weight: normal; color: #1C1917; }
        .lead { font-size: 14px; line-height: 1.6; color: #524C46; margin: 0 0 24px; }
        .tracking-card { background-color: #FAF8F5; border: 1px solid #EAE4D9; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .btn { display: inline-block; background-color: #1C1917 !important; color: #FAF8F5 !important; text-decoration: none !important; padding: 14px 34px; border-radius: 9999px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2em; margin-top: 10px; }
        .btn, .btn:visited, .btn:hover, .btn:active, .btn span { color: #FAF8F5 !important; text-decoration: none !important; }
        .footer { background-color: #FAF8F5; border-top: 1px solid #E5DFD3; padding: 20px 30px; text-align: center; font-size: 11px; color: #8E877D; }
        /* Prevent auto blue links in Gmail/Apple Mail for address & numbers */
        a[x-apple-data-detectors], u + #body a { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
    </style>
</head>
<body id="body">
    <div class="container">
        <div class="header">
            <h1 style="color: #FAF8F5 !important;">Solitaire Fine Jewels</h1>
            <p style="color: #8E877D !important;">Certified Diamonds &middot; Fine Jewellery Atelier</p>
        </div>

        <div class="content">
            <span class="badge">{{ $order->status_label }}</span>
            <h2 class="order-title">Status Update on Your Order</h2>
            <p class="lead">
                Your order <strong>{{ $order->order_reference }}</strong> has progressed to 
                <strong>
                    @if($order->status === 'CONFIRMED')
                        Order Confirmed
                    @elseif($order->status === 'PROCESSING' || in_array($order->status, ['CRAFTING', 'QUALITY_CHECK', 'PACKED']))
                        Order Processing & Packaging
                    @elseif($order->status === 'SHIPPED')
                        Dispatched with Courier Partner
                    @elseif($order->status === 'DELIVERED')
                        Successfully Delivered
                    @elseif($order->status === 'CANCELLED')
                        Cancelled
                    @else
                        {{ $order->status_label }}
                    @endif
                </strong>.
            </p>

            @if($order->tracking_number || $order->courier)
                <div class="tracking-card">
                    <h4 style="margin: 0 0 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.15em; color: #8E877D;">Courier Dispatch Details</h4>
                    <table style="width: 100%; font-size: 13px; line-height: 1.6;">
                        @if($order->courier)
                            <tr>
                                <td style="color: #78716C; width: 40%;">Courier Partner:</td>
                                <td><strong>{{ $order->courier }}</strong></td>
                            </tr>
                        @endif
                        @if($order->tracking_number)
                            <tr>
                                <td style="color: #78716C;">Tracking Reference:</td>
                                <td><strong style="font-family: monospace;">{{ $order->tracking_number }}</strong></td>
                            </tr>
                        @endif
                    </table>
                </div>
            @endif

            <div style="text-align: center; margin-top: 28px;">
                <a href="{{ route('tracking.index', ['order_reference' => $order->order_reference, 'email' => $order->email]) }}"
                   class="btn"
                   target="_blank"
                   style="display: inline-block; background-color: #1C1917; color: #FAF8F5 !important; text-decoration: none !important; padding: 14px 34px; border-radius: 9999px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2em; line-height: 1; mso-padding-alt: 0;">
                    <span style="color: #FAF8F5 !important; text-decoration: none !important; font-weight: bold; text-transform: uppercase; letter-spacing: 0.2em;">Track Live Shipment &rarr;</span>
                </a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Solitaire Fine Jewels Atelier &middot; Certified Fine Jewellery &amp; Bespoke Creations.<br>
            If you have any questions, reply to this email or visit our Atelier Concierge.
        </div>
    </div>
</body>
</html>
