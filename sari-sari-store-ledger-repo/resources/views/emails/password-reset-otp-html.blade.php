<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 40px; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { color: #1e293b; font-size: 24px; font-weight: 700; margin: 0; }
        .content { color: #334155; font-size: 16px; line-height: 1.6; }
        .otp-box {
            display: inline-block; background: #eef2ff; border: 2px dashed #6366f1;
            border-radius: 12px; padding: 16px 32px; margin: 20px 0;
            font-size: 32px; font-weight: 700; letter-spacing: 0.5em;
            color: #4338ca; font-family: 'Courier New', monospace;
        }
        .footer { margin-top: 30px; font-size: 14px; color: #94a3b8; text-align: center; }
        .warning { font-size: 13px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset Code</h1>
        </div>
        <div class="content">
            <p>Hello {{ $storeName ?? 'Admin' }},</p>
            <p>We received a request to reset your store account password. Use the code below to continue:</p>
            <p style="text-align: center;">
                <span class="otp-box">{{ $otp }}</span>
            </p>
            <p class="warning">This code is valid for 30 minutes and can only be used once.</p>
            <p>If you did not request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>