@php
    $c = $section->content ?? []; $items = $c['items'] ?? []; $s = $section->settings ?? [];
    $bgColor = $s['bg_color'] ?? '';
    $bgIsGradient = str_contains($bgColor, 'gradient');
    $bgStyle = $bgColor ? ($bgIsGradient ? 'background:'.$bgColor.';' : 'background-color:'.$bgColor.';') : 'background:linear-gradient(135deg,#0A2D74 0%,#1a4fa0 100%);';
    $textColor = $s['text_color'] ?? 'rgba(255,255,255,.8)';
    $headingColor = $s['heading_color'] ?? '#fff';
    $accentColor = $s['btn_color'] ?? '#F5A623';
    $paddingY = ($s['padding_y'] ?? '48') . 'px';
@endphp
@if(count($items))
<section style="padding:{{ $paddingY }} 0;{{ $bgStyle }}">
    <div class="container">
        @if($section->title)
        <h2 style="text-align:center;color:{{ $headingColor }};font-size:1.6rem;font-weight:700;margin:0 0 32px">{{ $section->title }}</h2>
        @endif
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:24px;text-align:center">
            @foreach($items as $item)
            <div>
                <div style="font-size:2.2rem;font-weight:800;color:{{ $accentColor }};margin-bottom:4px">{{ $item['number'] ?? '' }}</div>
                <div style="font-size:.9rem;color:{{ $textColor }}">{{ $item['label'] ?? '' }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
