@php $c = $section->content ?? []; @endphp
<section class="cta-section" style="{{ !empty($c['background_image']) ? 'background-image:url('.e($c['background_image']).');background-size:cover;background-position:center;' : '' }}">
    <div class="cta-overlay"></div>
    <div class="container">
        <div style="text-align:center;position:relative;z-index:1;padding:60px 0">
            <h2 style="font-size:2rem;font-weight:700;color:#fff;margin:0 0 12px">{{ $c['heading'] ?? $section->title ?? 'Ready to Start Your Adventure?' }}</h2>
            @if(!empty($c['subheading']))
            <p style="color:rgba(255,255,255,.85);font-size:1.05rem;margin:0 0 28px;max-width:500px;margin-left:auto;margin-right:auto">{{ $c['subheading'] }}</p>
            @endif
            @if(!empty($c['button_text']))
            <a href="{{ $c['button_link'] ?? '#' }}" class="btn btn-primary btn-lg">{{ $c['button_text'] }}</a>
            @endif
        </div>
    </div>
</section>
