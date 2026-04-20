@php $c = $section->content ?? []; $s = $section->settings ?? []; @endphp
@if(!empty($c['heading']) || !empty($c['subheading']) || !empty($c['button_text']))
@php
    $bgColor = $s['bg_color'] ?? '';
    $bgIsGradient = str_contains($bgColor, 'gradient');
    $hasBgImage = !empty($c['background_image']);
    $bgStyle = $hasBgImage ? 'background:url('.e($c['background_image']).') center/cover no-repeat;' : ($bgColor ? ($bgIsGradient ? 'background:'.$bgColor.';' : 'background-color:'.$bgColor.';') : 'background:linear-gradient(135deg,#0A2D74 0%,#1a4fa0 100%);');
    $textColor = $s['text_color'] ?? '#fff';
    $headingColor = $s['heading_color'] ?? $textColor;
    $btnColor = $s['btn_color'] ?? '#0A2D74';
    $btnTextColor = $s['btn_text_color'] ?? '#fff';
    $btnRadius = ($s['btn_radius'] ?? '10') . 'px';
    $fontSize = ($s['font_size'] ?? '18') . 'px';
    $headingSize = ($s['heading_size'] ?? '48') . 'px';
    $paddingY = ($s['padding_y'] ?? '80') . 'px';
@endphp
<section style="position:relative;min-height:500px;display:flex;align-items:center;justify-content:center;overflow:hidden;{{ $bgStyle }}">
    @if($hasBgImage)<div style="position:absolute;inset:0;background:rgba(0,0,0,.35)"></div>@endif
    <div style="position:relative;z-index:1;text-align:center;padding:{{ $paddingY }} 24px;max-width:800px;margin:0 auto">
        @if(!empty($c['heading']))
        <h1 style="font-size:{{ $headingSize }};font-weight:800;color:{{ $headingColor }};margin:0 0 16px;text-shadow:0 2px 10px rgba(0,0,0,.15)">{{ $c['heading'] }}</h1>
        @endif
        @if(!empty($c['subheading']))
        <p style="font-size:{{ $fontSize }};color:{{ $textColor }};opacity:.9;max-width:600px;margin:0 auto 28px">{{ $c['subheading'] }}</p>
        @endif
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            @if(!empty($c['button_text']))
            <a href="{{ $c['button_link'] ?? '#' }}" style="display:inline-flex;align-items:center;padding:14px 32px;background:{{ $btnColor }};color:{{ $btnTextColor }};border-radius:{{ $btnRadius }};font-weight:700;text-decoration:none;font-size:.95rem">{{ $c['button_text'] }}</a>
            @endif
            @if(!empty($c['button2_text']))
            <a href="{{ $c['button2_link'] ?? '#' }}" style="display:inline-flex;align-items:center;padding:14px 32px;background:transparent;color:{{ $textColor }};border:2px solid {{ $textColor }}40;border-radius:{{ $btnRadius }};font-weight:700;text-decoration:none;font-size:.95rem">{{ $c['button2_text'] }}</a>
            @endif
        </div>
    </div>
</section>
@endif
