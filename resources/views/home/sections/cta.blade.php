@php $c = $section->content ?? []; @endphp
@if(!empty($c['heading']) || !empty($c['button_text']))
<section style="position:relative;padding:5rem 0;{{ !empty($c['background_image']) ? 'background:url('.e($c['background_image']).') center/cover no-repeat;' : 'background:linear-gradient(135deg,#0A2D74 0%,#1a4fa0 100%);' }}">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.3)"></div>
    <div style="position:relative;z-index:1;text-align:center;padding:0 24px;max-width:700px;margin:0 auto">
        @if(!empty($c['heading']))
        <h2 style="font-size:2rem;font-weight:700;color:#fff;margin:0 0 12px">{{ $c['heading'] }}</h2>
        @endif
        @if(!empty($c['subheading']))
        <p style="color:rgba(255,255,255,.85);font-size:1.05rem;margin:0 0 28px">{{ $c['subheading'] }}</p>
        @endif
        @if(!empty($c['button_text']))
        <a href="{{ $c['button_link'] ?? '#' }}" style="display:inline-flex;align-items:center;padding:14px 32px;background:#F5A623;color:#fff;border-radius:10px;font-weight:700;text-decoration:none;font-size:.95rem">{{ $c['button_text'] }}</a>
        @endif
    </div>
</section>
@endif
