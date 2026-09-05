<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Inquiry Notification</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background-color: #f8fafc; color: #0f172a; padding: 30px; }
        .card { max-width: 650px; margin: 0 auto; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px; }
        .header { border-bottom: 2px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 24px; }
        .ref-badge { display: inline-block; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; font-family: monospace; font-size: 14px; padding: 4px 10px; color: #334155; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .meta-table td { padding: 8px 0; font-size: 14px; border-bottom: 1px solid #f8fafc; vertical-align: top; }
        .label { font-weight: 600; color: #475569; width: 130px; }
        .message-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; font-size: 15px; white-space: pre-wrap; color: #1e293b; word-break: break-word; }
        .action-btn { display: inline-block; background-color: #2A2016; color: white; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2 style="margin: 0; color: #0f172a;">New Contact Inquiry</h2>
            <p style="margin: 6px 0 0 0; color: #64748b; font-size: 14px;">
                Reference: <span class="ref-badge">{{ $inquiry->public_reference }}</span>
            </p>
        </div>

        <table class="meta-table">
            <tr>
                <td class="label">Customer Name:</td>
                <td>{{ $inquiry->name }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></td>
            </tr>
            <tr>
                <td class="label">Phone:</td>
                <td>{{ $inquiry->phone ?: 'Not provided' }}</td>
            </tr>
            <tr>
                <td class="label">Subject:</td>
                <td>{{ $inquiry->subject }}</td>
            </tr>
            <tr>
                <td class="label">Submitted At:</td>
                <td>{{ $inquiry->created_at->format('M d, Y h:i A') }}</td>
            </tr>
        </table>

        <h3 style="margin-bottom: 10px; color: #0f172a;">Message:</h3>
        <div class="message-box">{{ $inquiry->message }}</div>

        <p style="margin-top: 28px; text-align: center;">
            <a href="{{ url('/admin/contact-inquiries/' . $inquiry->id) }}" class="action-btn">
                View &amp; Reply in Admin Panel →
            </a>
        </p>
    </div>
</body>
</html>

