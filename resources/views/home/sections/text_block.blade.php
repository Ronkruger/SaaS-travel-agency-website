@php
    $c = $section->content ?? []; $s = $section->settings ?? [];
    $bgColor = $s['bg_color'] ?? '#ffffff';
    $textColor = $s['text_color'] ?? '#374151';
    $headingColor = $s['heading_color'] ?? '#111827';
    $fontSize = ($s['font_size'] ?? '16') . 'px';
    $paddingY = ($s['padding_y'] ?? '60') . 'px';
@endphp
<section style="padding:{{ $paddingY }} 0;background-color:{{ $bgColor }}">
    <div class="container" style="max-width:800px">
        @if($section->title)
        <h2 style="font-size:1.8rem;font-weight:700;margin:0 0 8px;text-align:center;color:{{ $headingColor }}">{{ $section->title }}</h2>
        @endif
        @if($section->subtitle)
        <p style="color:{{ $textColor }};text-align:center;margin:0 0 28px;font-size:{{ $fontSize }}">{{ $section->subtitle }}</p>
        @endif
        <div style="color:{{ $textColor }};font-size:{{ $fontSize }};line-height:1.8">
            {!! $c['body'] ?? '' !!}
        </div>
    </div>
</section>
