@php
    $c = $section->content ?? []; $items = $c['items'] ?? []; $s = $section->settings ?? [];
    $bgColor = $s['bg_color'] ?? '#ffffff';
    $textColor = $s['text_color'] ?? '#6b7280';
    $headingColor = $s['heading_color'] ?? '#111827';
    $accentColor = $s['btn_color'] ?? '#0A2D74';
    $btnRadius = ($s['btn_radius'] ?? '14') . 'px';
    $paddingY = ($s['padding_y'] ?? '60') . 'px';
    $fontSize = ($s['font_size'] ?? '16') . 'px';
@endphp
@if(count($items))
<section style="padding:{{ $paddingY }} 0;background-color:{{ $bgColor }}">
    <div class="container">
        @if($section->title)
        <div style="text-align:center;margin-bottom:40px">
            <h2 style="font-size:1.8rem;font-weight:700;margin:0 0 8px;color:{{ $headingColor }}">{{ $section->title }}</h2>
            @if($section->subtitle)<p style="color:{{ $textColor }};margin:0;font-size:{{ $fontSize }}">{{ $section->subtitle }}</p>@endif
        </div>
        @endif
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px">
            @foreach($items as $item)
            <div style="background:#fff;border-radius:{{ $btnRadius }};box-shadow:0 2px 16px rgba(0,0,0,.06);padding:32px 24px;text-align:center">
                @if(!empty($item['icon']))
                <div style="width:60px;height:60px;background:{{ $accentColor }};border-radius:{{ $btnRadius }};display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="{{ $item['icon'] }}" style="color:#fff;font-size:1.3rem"></i>
                </div>
                @endif
                <h4 style="font-weight:700;margin:0 0 8px;font-size:1rem;color:{{ $headingColor }}">{{ $item['title'] ?? '' }}</h4>
                <p style="color:{{ $textColor }};font-size:.9rem;margin:0;line-height:1.6">{{ $item['description'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
