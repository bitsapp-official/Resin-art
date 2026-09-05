<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Response from Maison Résine</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #FDFBF7; color: #1c1917; margin: 0; padding: 40px 20px; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e7e5e4; border-radius: 16px; padding: 40px; }
        .brand { font-family: Georgia, serif; font-size: 24px; letter-spacing: 2px; text-transform: uppercase; color: #2A2016; text-align: center; margin-bottom: 30px; }
        .reply-content { font-size: 15px; color: #292524; white-space: pre-wrap; margin: 20px 0; padding: 20px; background-color: #FAF8F5; border-left: 3px solid #AD9575; border-radius: 4px; }
        .original-quote { margin-top: 30px; padding-top: 20px; border-top: 1px solid #f5f5f4; font-size: 13px; color: #78716c; }
        .footer { font-size: 12px; color: #a8a29e; text-align: center; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">Maison Résine</div>
        <p>Dear {{ $inquiry->name }},</p>
        
        <div class="reply-content">{{ $replyMessage }}</div>

        <p>If you have any further questions or require additional details regarding your inquiry (Ref: {{ $inquiry->public_reference }}), please feel free to reply directly to this letter.</p>

        <p>Warmest regards,<br>
        <strong>Maison Résine Atelier Team</strong></p>

        <div class="original-quote">
            <p><strong>Your Original Message ({{ $inquiry->created_at->format('M d, Y') }}):</strong></p>
            <p><em>"{{ $inquiry->message }}"</em></p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Maison Résine. 14 Rue des Beaux-Arts, 33000 Bordeaux, France.
        </div>
    </div>
</body>
</html>
