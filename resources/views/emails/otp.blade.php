<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Password Reset OTP</title>
    <style>
        body { margin:0; padding:0; background:#f4f6f8; font-family:'Segoe UI',Arial,sans-serif; color:#1a202c; }
        .wrapper { max-width:560px; margin:0 auto; padding:32px 16px; }
        .card { background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .header { background:#1e3a5f; padding:32px 40px; text-align:center; }
        .header h1 { color:#fff; margin:0; font-size:22px; font-weight:700; letter-spacing:-.3px; }
        .header p  { color:#93c5fd; margin:6px 0 0; font-size:14px; }
        .body { padding:36px 40px; }
        .greeting { font-size:16px; margin:0 0 20px; }
        .otp-box { text-align:center; background:#f0f7ff; border:2px dashed #3b82f6; border-radius:12px; padding:24px 16px; margin:24px 0; }
        .otp-label { font-size:13px; color:#6b7280; text-transform:uppercase; letter-spacing:.08em; margin:0 0 12px; font-weight:600; }
        .otp-code { font-size:44px; font-weight:800; letter-spacing:14px; color:#1e3a5f; font-family:'Courier New',monospace; margin:0; }
        .expiry-note { text-align:center; font-size:13px; color:#dc2626; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:10px 16px; margin:0 0 24px; }
        .info-text { font-size:14px; color:#4b5563; line-height:1.6; }
        .divider { border:none; border-top:1px solid #e2e8f0; margin:24px 0; }
        .footer { text-align:center; font-size:12px; color:#9ca3af; padding:20px 40px 32px; }
        .footer a { color:#1e3a5f; text-decoration:none; }
        .ignore-note { background:#fafafa; border-left:3px solid #d1d5db; padding:12px 16px; font-size:13px; color:#6b7280; border-radius:0 6px 6px 0; margin-top:20px; }
    </style>
</head>
<body>
<div class="wrapper">
<div class="card">

    <div class="header">
        <h1>{{ $brandName ?? "Your Agency" }}</h1>
        <p>🔐 Password Reset Request</p>
    </div>

    <div class="body">
        <p class="greeting">Hello,</p>

        <p class="info-text">
            We received a request to reset the password for your {{ $brandName ?? "Your Agency" }} account
            associated with <strong>{{ $email }}</strong>.
        </p>

        <p class="info-text">Enter the OTP below on the verification page:</p>

        <div class="otp-box">
            <p class="otp-label">Your One-Time Password</p>
            <p class="otp-code">{{ $otp }}</p>
        </div>

        <div class="expiry-note">
            ⏰ This OTP expires in <strong>15 minutes</strong>.
        </div>

        <hr class="divider">

        <div class="ignore-note">
            If you did not request a password reset, you can safely ignore this email.
            Your password will not be changed.
        </div>
    </div>

    <div class="footer">
        <p style="margin:0 0 8px;background:#f3f4f6;border-radius:6px;padding:8px 12px;font-size:11px;color:#6b7280;display:inline-block">
            ⚠️ This is an automated, system-generated email — please do not reply directly to this message.
        </p>
        &copy; {{ date('Y') }} <a href="#">{{ $brandName ?? "Your Agency" }}</a> &mdash; All rights reserved.
    </div>

</div>
</div>
</body>
</html>
