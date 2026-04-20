@php
    $c = $section->content ?? []; $images = $c['images'] ?? []; $s = $section->settings ?? [];
    $bgColor = $s['bg_color'] ?? '#ffffff';
    $textColor = $s['text_color'] ?? '#6b7280';
    $headingColor = $s['heading_color'] ?? '#111827';
    $btnRadius = ($s['btn_radius'] ?? '12') . 'px';
    $paddingY = ($s['padding_y'] ?? '60') . 'px';
@endphp
@if(count($images))
<section style="padding:{{ $paddingY }} 0;background-color:{{ $bgColor }}">
    <div class="container">
        @if($section->title)
        <div style="text-align:center;margin-bottom:40px">
            <h2 style="font-size:1.8rem;font-weight:700;margin:0 0 8px;color:{{ $headingColor }}">{{ $section->title }}</h2>
            @if($section->subtitle)<p style="color:{{ $textColor }};margin:0">{{ $section->subtitle }}</p>@endif
        </div>
        @endif
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:16px">
            @foreach($images as $img)
            <div style="border-radius:{{ $btnRadius }};overflow:hidden;position:relative;aspect-ratio:4/3">
                <img src="{{ $img['url'] ?? '' }}" alt="{{ $img['caption'] ?? '' }}" style="width:100%;height:100%;object-fit:cover" loading="lazy">
                @if(!empty($img['caption']))
                <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.6));padding:16px 12px 12px;color:#fff;font-size:.85rem">
                    {{ $img['caption'] }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
