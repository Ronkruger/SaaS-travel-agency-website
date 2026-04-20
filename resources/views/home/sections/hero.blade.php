@php $c = $section->content ?? []; @endphp
@if(!empty($c['heading']) || !empty($c['subheading']) || !empty($c['button_text']))
<section style="position:relative;min-height:500px;display:flex;align-items:center;justify-content:center;overflow:hidden;{{ !empty($c['background_image']) ? 'background:url('.e($c['background_image']).') center/cover no-repeat;' : 'background:linear-gradient(135deg,#0A2D74 0%,#1a4fa0 100%);' }}">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.35)"></div>
    <div style="position:relative;z-index:1;text-align:center;padding:80px 24px;max-width:800px;margin:0 auto">
        @if(!empty($c['heading']))
        <h1 style="font-size:3rem;font-weight:800;color:#fff;margin:0 0 16px;text-shadow:0 2px 10px rgba(0,0,0,.3)">{{ $c['heading'] }}</h1>
        @endif
        @if(!empty($c['subheading']))
        <p style="font-size:1.15rem;color:rgba(255,255,255,.9);max-width:600px;margin:0 auto 28px;text-shadow:0 1px 4px rgba(0,0,0,.2)">{{ $c['subheading'] }}</p>
        @endif
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            @if(!empty($c['button_text']))
            <a href="{{ $c['button_link'] ?? '#' }}" style="display:inline-flex;align-items:center;padding:14px 32px;background:#0A2D74;color:#fff;border-radius:10px;font-weight:700;text-decoration:none;font-size:.95rem">{{ $c['button_text'] }}</a>
            @endif
            @if(!empty($c['button2_text']))
            <a href="{{ $c['button2_link'] ?? '#' }}" style="display:inline-flex;align-items:center;padding:14px 32px;background:transparent;color:#fff;border:2px solid rgba(255,255,255,.6);border-radius:10px;font-weight:700;text-decoration:none;font-size:.95rem">{{ $c['button2_text'] }}</a>
            @endif
        </div>
    </div>
</section>
@endif
