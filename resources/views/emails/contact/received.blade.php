<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letter Received</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FDFBF7; color: #1c1917; margin: 0; padding: 40px 20px; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e7e5e4; border-radius: 16px; padding: 40px; }
        .brand { font-family: Georgia, serif; font-size: 24px; font-weight: normal; letter-spacing: 2px; text-transform: uppercase; color: #2A2016; text-align: center; margin-bottom: 30px; }
        .divider { height: 1px; background-color: #f5f5f4; margin: 30px 0; }
        .reference-badge { background-color: #F7F3EB; border: 1px solid #DFD4C0; border-radius: 8px; padding: 12px 16px; font-family: monospace; font-size: 15px; color: #4D3C2C; display: inline-block; margin: 15px 0; }
        .footer { font-size: 12px; color: #78716c; text-align: center; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">Maison Résine</div>
        <p>Dear {{ $inquiry->name }},</p>
        <p>Thank you for writing to our atelier. We have received your correspondence regarding <strong>{{ $inquiry->subject }}</strong>.</p>
        
        <p>Your unique reference code is:</p>
        <div class="reference-badge">{{ $inquiry->public_reference }}</div>

        <p>Our artisans and correspondence team review every letter carefully. We aim to reply within two working days.</p>
        
        <div class="divider"></div>
        
        <p style="font-style: italic; color: #57534e;">"Handcrafted fine resin art, poured with intention in Bordeaux."</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} Maison Résine Atelier. 14 Rue des Beaux-Arts, 33000 Bordeaux, France.<br>
            This is an automated acknowledgment.
        </div>
    </div>
</body>
</html>
