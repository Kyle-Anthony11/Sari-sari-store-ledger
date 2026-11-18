<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 40px; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { color: #1e293b; font-size: 24px; font-weight: 700; margin: 0; }
        .content { color: #334155; font-size: 16px; line-height: 1.6; }
        .button { display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 14px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verify Your Store Account</h1>
        </div>
        <div class="content">
            <p>Hello {{ $storeName ?? 'Admin' }},</p>
            <p>Thank you for registering your store. Please click the button below to verify your email address and activate your account.</p>
            <p style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Verify Account</a>
            </p>
            <p>If you did not create this account, you can safely ignore this email.</p>
            <p>This link will expire in 24 hours.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>