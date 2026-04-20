@php
    $c = $section->content ?? []; $s = $section->settings ?? [];
    $layout = $c['layout'] ?? 'contained';
    $body = $c['body'] ?? '';
    $bgColor = $s['bg_color'] ?? '#ffffff';
    $bgIsGradient = str_contains($bgColor, 'gradient');
    $hasBgImage = !empty($c['background_image']);
    $bgStyle = $hasBgImage
        ? 'background:url('.e($c['background_image']).') center/cover no-repeat;'
        : ($bgColor ? ($bgIsGradient ? 'background:'.$bgColor.';' : 'background-color:'.$bgColor.';') : '');
    $textColor = $s['text_color'] ?? '#374151';
    $headingColor = $s['heading_color'] ?? '#111827';
    $btnColor = $s['btn_color'] ?? '#0A2D74';
    $btnTextColor = $s['btn_text_color'] ?? '#fff';
    $btnRadius = ($s['btn_radius'] ?? '10') . 'px';
    $fontSize = ($s['font_size'] ?? '16') . 'px';
    $headingSize = ($s['heading_size'] ?? '32') . 'px';
    $paddingY = ($s['padding_y'] ?? '60') . 'px';
    $hasContent = !empty($body) || !empty($section->title) || !empty($c['button_text']);
@endphp
@if($hasContent)
<section style="position:relative;padding:{{ $paddingY }} 0;{{ $bgStyle }}{{ $hasBgImage ? 'color:#fff;' : '' }}">
    @if($hasBgImage)
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.4)"></div>
    @endif
    <div style="position:relative;{{ $layout === 'full_width' ? 'width:100%;padding:0 24px;' : 'max-width:1200px;margin:0 auto;padding:0 24px;' }}">

        @if($section->title)
        <div style="text-align:center;margin-bottom:32px">
            <h2 style="font-size:{{ $headingSize }};font-weight:700;margin:0 0 8px;color:{{ $hasBgImage ? '#fff' : $headingColor }}">{{ $section->title }}</h2>
            @if($section->subtitle)
            <p style="color:{{ $hasBgImage ? 'rgba(255,255,255,.85)' : $textColor }};margin:0;font-size:{{ $fontSize }}">{{ $section->subtitle }}</p>
            @endif
        </div>
        @endif

        @if($layout === 'two_column' && $hasBgImage)
        {{-- Two column: text left, image right --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center">
            <div>
                <div style="color:{{ $hasBgImage ? '#fff' : $textColor }};font-size:{{ $fontSize }};line-height:1.7">{!! $body !!}</div>
                @if(!empty($c['button_text']))
                <div style="margin-top:24px">
                    <a href="{{ $c['button_link'] ?? '#' }}" style="display:inline-block;background:{{ $btnColor }};color:{{ $btnTextColor }};padding:12px 28px;border-radius:{{ $btnRadius }};text-decoration:none;font-weight:600;font-size:.95rem">{{ $c['button_text'] }}</a>
                </div>
                @endif
            </div>
            <div>
                <img src="{{ $c['background_image'] }}" alt="{{ $section->title ?? '' }}" style="width:100%;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.15)" loading="lazy">
            </div>
        </div>
        @elseif($layout === 'two_column')
        {{-- Two column without bg image: content spans both cols --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start">
            <div style="color:{{ $textColor }};font-size:{{ $fontSize }};line-height:1.7">{!! $body !!}</div>
            <div>
                @if(!empty($c['button_text']))
                <div style="margin-top:24px">
                    <a href="{{ $c['button_link'] ?? '#' }}" style="display:inline-block;background:{{ $btnColor }};color:{{ $btnTextColor }};padding:12px 28px;border-radius:{{ $btnRadius }};text-decoration:none;font-weight:600;font-size:.95rem">{{ $c['button_text'] }}</a>
                </div>
                @endif
            </div>
        </div>
        @else
        {{-- Contained or Full Width --}}
        <div style="{{ $layout === 'contained' ? 'max-width:800px;margin:0 auto;' : '' }}color:{{ $hasBgImage ? '#fff' : $textColor }};font-size:{{ $fontSize }};line-height:1.7">
            {!! $body !!}
        </div>
        @if(!empty($c['button_text']))
        <div style="text-align:center;margin-top:28px">
            <a href="{{ $c['button_link'] ?? '#' }}" style="display:inline-block;background:{{ $btnColor }};color:{{ $btnTextColor }};padding:14px 32px;border-radius:{{ $btnRadius }};text-decoration:none;font-weight:600;font-size:1rem;transition:opacity .2s">{{ $c['button_text'] }}</a>
        </div>
        @endif
        @endif
    </div>
</section>
@endif
