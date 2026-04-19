<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #0e7490 0%, #0c5d73 100%); color: #ffffff; padding: 40px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
        .header p { margin: 10px 0 0; font-size: 16px; opacity: 0.9; }
        .content { padding: 40px; }
        .content p { margin: 0 0 20px; font-size: 15px; }
        .highlight-box { background: #f0f9ff; border-left: 4px solid #0e7490; padding: 20px; margin: 25px 0; border-radius: 4px; }
        .highlight-box strong { color: #0e7490; font-size: 18px; }
        .button { display: inline-block; background: #0e7490; color: #ffffff !important; text-decoration: none; padding: 16px 40px; border-radius: 6px; font-weight: 600; margin: 20px 0; font-size: 16px; }
        .button:hover { background: #0c5d73; }
        .features { margin: 30px 0; }
        .feature { display: flex; margin: 15px 0; }
        .feature-icon { color: #0e7490; font-size: 20px; margin-right: 12px; }
        .feature-text { font-size: 14px; color: #555; }
        .footer { background: #f8f8f8; padding: 20px 40px; text-align: center; font-size: 13px; color: #666; border-top: 1px solid #e0e0e0; }
        .footer p { margin: 0; }
        .link-text { color: #0e7490; word-break: break-all; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Welcome to Tour SaaS!</h1>
            <p>Your travel agency platform is ready</p>
        </div>
        <div class="content">
            <p>Hello {{ $ownerName }},</p>
            
            <p>Thank you for registering <strong>{{ $companyName }}</strong> with Tour SaaS!</p>
            
            <p>Click the button below to activate your <strong>30-day free trial</strong> and start building your travel agency:</p>
            
            <p style="text-align: center;">
                <a href="{{ $activationUrl }}" class="button">🎯 Activate My Free Trial</a>
            </p>

            <div class="highlight-box">
                <strong>Your Trial Includes:</strong>
                <div class="features">
                    <div class="feature">
                        <span class="feature-icon">✅</span>
                        <span class="feature-text">Full access to all features for 30 days</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">✅</span>
                        <span class="feature-text">Tour package management & bookings</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">✅</span>
                        <span class="feature-text">DIY tour builder for your clients</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">✅</span>
                        <span class="feature-text">Payment processing & invoicing</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">✅</span>
                        <span class="feature-text">Email notifications & client management</span>
                    </div>
                </div>
            </div>
            
            <p style="font-size: 13px; color: #666; margin-top: 30px;">
                Or copy and paste this link into your browser:<br>
                <span class="link-text">{{ $activationUrl }}</span>
            </p>
            
            <p style="font-size: 13px; color: #666; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                <strong>What happens after 30 days?</strong><br>
                You'll have the option to upgrade to a paid plan to continue using all features. No credit card required to start your trial!
            </p>
            
            <p style="font-size: 13px; color: #666; margin-top: 15px;">
                If you didn't create this account, you can safely ignore this email.
            </p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Tour SaaS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
