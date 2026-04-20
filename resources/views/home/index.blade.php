<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $brandName ?? 'Home' }}</title>
    @if(!empty($brandFaviconUrl))
    <link rel="icon" href="{{ $brandFaviconUrl }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ global_asset('css/styles.css') }}">
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; }
        .site-admin-bar { background: #1e293b; color: #fff; font-size: .8rem; padding: 8px 20px; display: flex; align-items: center; justify-content: space-between; }
        .site-admin-bar a { color: #93c5fd; text-decoration: none; font-weight: 600; }
        .site-admin-bar a:hover { color: #fff; }
    </style>
</head>
<body>
    @auth('admin')
    <div class="site-admin-bar">
        <span><i class="fas fa-paint-roller" style="margin-right:6px"></i> You're viewing your site. Sections are loaded from Page Builder.</span>
        <a href="{{ route('admin.page-builder.index') }}"><i class="fas fa-edit" style="margin-right:4px"></i> Edit in Page Builder</a>
    </div>
    @endauth

    @foreach($sections as $section)
        @includeIf('home.sections.' . $section->section_type, [
            'section'       => $section,
            'categories'    => $categories ?? collect(),
            'featuredTours' => $featuredTours ?? collect(),
            'topRatedTours' => $topRatedTours ?? collect(),
            'latestReviews' => $latestReviews ?? collect(),
        ])
    @endforeach
</body>
</html>
