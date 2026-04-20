@php $c = $section->content ?? []; @endphp
<section class="section" style="padding:60px 0">
    <div class="container" style="max-width:800px">
        @if($section->title)
        <h2 style="font-size:1.8rem;font-weight:700;margin:0 0 8px;text-align:center">{{ $section->title }}</h2>
        @endif
        @if($section->subtitle)
        <p style="color:#6b7280;text-align:center;margin:0 0 28px">{{ $section->subtitle }}</p>
        @endif
        <div style="color:#374151;font-size:.95rem;line-height:1.8">
            {!! $c['body'] ?? '' !!}
        </div>
    </div>
</section>
