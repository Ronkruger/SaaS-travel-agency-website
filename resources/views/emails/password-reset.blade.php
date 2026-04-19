<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #0e7490; color: #ffffff; padding: 30px 40px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px; }
        .content p { margin: 0 0 20px; font-size: 15px; }
        .button { display: inline-block; background: #0e7490; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 600; margin: 20px 0; }
        .button:hover { background: #0c5d73; }
        .footer { background: #f8f8f8; padding: 20px 40px; text-align: center; font-size: 13px; color: #666; border-top: 1px solid #e0e0e0; }
        .footer p { margin: 0; }
        .link-text { color: #0e7490; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Reset Your Password</h1>
        </div>
        <div class="content">
            <p>Hello,</p>
            <p>We received a request to reset the password for your <strong>{{ $tenantName }}</strong> account.</p>
            <p>Click the button below to reset your password:</p>
            <p style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">Reset Password</a>
            </p>
            <p style="font-size: 13px; color: #666; margin-top: 30px;">
                Or copy and paste this link into your browser:<br>
                <span class="link-text">{{ $resetUrl }}</span>
            </p>
            <p style="font-size: 13px; color: #666; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                <strong>This link will expire in 24 hours.</strong><br>
                If you didn't request a password reset, you can safely ignore this email.
            </p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Tour SaaS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
