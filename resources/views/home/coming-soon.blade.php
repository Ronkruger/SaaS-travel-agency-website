<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $brandName ?? 'Coming Soon' }}</title>
    @if(!empty($brandFaviconUrl))
    <link rel="icon" href="{{ $brandFaviconUrl }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); color: #374151; }
        .wrap { text-align: center; max-width: 520px; padding: 40px 24px; }
        .logo-box { width: 80px; height: 80px; background: linear-gradient(135deg, #0A2D74, #1a4fa0); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; }
        .logo-box span { color: #fff; font-size: 2rem; font-weight: 800; }
        .logo-img { max-height: 80px; margin-bottom: 24px; }
        h1 { font-size: 2rem; font-weight: 800; color: #0A2D74; margin-bottom: 10px; }
        .tagline { font-size: 1.05rem; color: #6b7280; margin-bottom: 36px; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 20px rgba(0,0,0,.06); padding: 32px; margin-bottom: 32px; }
        .icon-wrap { width: 56px; height: 56px; background: #FEF3C7; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .icon-wrap i { color: #F59E0B; font-size: 1.4rem; }
        .card h3 { font-size: 1.05rem; font-weight: 700; margin-bottom: 8px; }
        .card p { color: #9ca3af; font-size: .88rem; line-height: 1.6; }
        .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: .9rem; text-decoration: none; transition: all .2s; }
        .btn-primary { background: #0A2D74; color: #fff; }
        .btn-primary:hover { background: #163e94; }
        .btn-outline { background: #fff; color: #0A2D74; border: 1px solid #d1d5db; }
        .btn-outline:hover { border-color: #0A2D74; }
        .admin-link { margin-top: 40px; font-size: .82rem; color: #9ca3af; }
        .admin-link a { color: #6b7280; text-decoration: none; }
        .admin-link a:hover { color: #0A2D74; }
    </style>
</head>
<body>
    <div class="wrap">
        @if(!empty($brandLogoUrl))
        <img src="{{ $brandLogoUrl }}" alt="{{ $brandName }}" class="logo-img">
        @else
        <div class="logo-box">
            <span>{{ strtoupper(substr($brandName ?? 'T', 0, 1)) }}</span>
        </div>
        @endif

        <h1>{{ $brandName ?? 'Coming Soon' }}</h1>

        @if(!empty($brandTagline))
        <p class="tagline">{{ $brandTagline }}</p>
        @else
        <p class="tagline">We're building something amazing. Check back soon!</p>
        @endif

        <div class="card">
            <div class="icon-wrap"><i class="fas fa-hard-hat"></i></div>
            <h3>Site Under Construction</h3>
            <p>Our website is currently being set up. Check back soon or get in touch with us directly.</p>
        </div>

        <div class="actions">
            @if(isset($currentTenant) && $currentTenant->email)
            <a href="mailto:{{ $currentTenant->email }}" class="btn btn-primary">
                <i class="fas fa-envelope"></i> Contact Us
            </a>
            @endif
        </div>

        <div class="admin-link">
            <a href="{{ route('admin.auth.login') }}">Admin Login <i class="fas fa-arrow-right" style="font-size:.7rem"></i></a>
        </div>
    </div>
</body>
</html>
