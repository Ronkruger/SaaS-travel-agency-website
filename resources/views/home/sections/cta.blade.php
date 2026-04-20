@php
    $c = $section->content ?? []; $s = $section->settings ?? [];
    $bgColor = $s['bg_color'] ?? '';
    $bgIsGradient = str_contains($bgColor, 'gradient');
    $hasBgImage = !empty($c['background_image']);
    $bgStyle = $hasBgImage ? 'background:url('.e($c['background_image']).') center/cover no-repeat;' : ($bgColor ? ($bgIsGradient ? 'background:'.$bgColor.';' : 'background-color:'.$bgColor.';') : 'background:linear-gradient(135deg,#0A2D74 0%,#1a4fa0 100%);');
    $textColor = $s['text_color'] ?? '#fff';
    $headingColor = $s['heading_color'] ?? $textColor;
    $btnColor = $s['btn_color'] ?? '#F5A623';
    $btnTextColor = $s['btn_text_color'] ?? '#fff';
    $btnRadius = ($s['btn_radius'] ?? '10') . 'px';
    $paddingY = ($s['padding_y'] ?? '80') . 'px';
@endphp
@if(!empty($c['heading']) || !empty($c['button_text']))
<section style="position:relative;padding:{{ $paddingY }} 0;{{ $bgStyle }}">
    @if($hasBgImage)<div style="position:absolute;inset:0;background:rgba(0,0,0,.3)"></div>@endif
    <div style="position:relative;z-index:1;text-align:center;padding:0 24px;max-width:700px;margin:0 auto">
        @if(!empty($c['heading']))
        <h2 style="font-size:2rem;font-weight:700;color:{{ $headingColor }};margin:0 0 12px">{{ $c['heading'] }}</h2>
        @endif
        @if(!empty($c['subheading']))
        <p style="color:{{ $textColor }};opacity:.85;font-size:1.05rem;margin:0 0 28px">{{ $c['subheading'] }}</p>
        @endif
        @if(!empty($c['button_text']))
        <a href="{{ $c['button_link'] ?? '#' }}" style="display:inline-flex;align-items:center;padding:14px 32px;background:{{ $btnColor }};color:{{ $btnTextColor }};border-radius:{{ $btnRadius }};font-weight:700;text-decoration:none;font-size:.95rem">{{ $c['button_text'] }}</a>
        @endif
    </div>
</section>
@endif
