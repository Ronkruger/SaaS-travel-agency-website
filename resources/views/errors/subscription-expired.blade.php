<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired — TourSaaS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; }
        .card { background: #fff; border-radius: 20px; padding: 3rem 2.5rem; max-width: 520px; width: 100%; text-align: center; box-shadow: 0 8px 48px rgba(10,45,116,.1); border: 1px solid #e5e7eb; }
        .icon { font-size: 3.5rem; margin-bottom: 1.2rem; }
        h1 { font-size: 1.6rem; font-weight: 800; color: #d97706; margin-bottom: .7rem; }
        p { color: #6b7280; line-height: 1.65; margin-bottom: 1.5rem; }
        .btn { display: inline-block; background: #0A2D74; color: #fff; padding: .8rem 2rem; border-radius: 10px; font-weight: 700; font-size: .9rem; text-decoration: none; transition: background .2s; }
        .btn:hover { background: #1a47a0; }
        .contact { font-size: .85rem; color: #9ca3af; margin-top: 1rem; }
        .contact a { color: #0A2D74; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⏰</div>
        <h1>Subscription Expired</h1>
        <p>Your TourSaaS subscription has expired. To continue using the platform and allow your customers to access your booking site, please renew your subscription.</p>
        @php $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost'; @endphp
        <a href="http://{{ $centralDomain }}/login" class="btn">Renew Subscription →</a>
        <p class="contact">Questions? <a href="mailto:support@toursaas.com">support@toursaas.com</a></p>
    </div>
</body>
</html>
