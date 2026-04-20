@php $c = $section->content ?? []; @endphp
<section class="hero" style="{{ !empty($c['background_image']) ? 'background-image:url('.e($c['background_image']).');background-size:cover;background-position:center;' : '' }}">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="hero-content" style="text-align:center;padding:80px 0">
            <h1 style="font-size:3rem;font-weight:800;color:#fff;margin:0 0 16px;text-shadow:0 2px 10px rgba(0,0,0,.3)">{{ $c['heading'] ?? $section->title ?? '' }}</h1>
            @if(!empty($c['subheading']))
            <p style="font-size:1.15rem;color:rgba(255,255,255,.9);max-width:600px;margin:0 auto 28px;text-shadow:0 1px 4px rgba(0,0,0,.2)">{{ $c['subheading'] }}</p>
            @endif
            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                @if(!empty($c['button_text']))
                <a href="{{ $c['button_link'] ?? '#' }}" class="btn btn-primary btn-lg">{{ $c['button_text'] }}</a>
                @endif
                @if(!empty($c['button2_text']))
                <a href="{{ $c['button2_link'] ?? '#' }}" class="btn btn-outline btn-lg btn-white">{{ $c['button2_text'] }}</a>
                @endif
            </div>
        </div>
    </div>
</section>
