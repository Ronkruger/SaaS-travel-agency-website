@php $c = $section->content ?? []; $items = $c['items'] ?? []; @endphp
@if(count($items))
<section class="section" style="padding:60px 0">
    <div class="container">
        @if($section->title)
        <div style="text-align:center;margin-bottom:40px">
            <h2 style="font-size:1.8rem;font-weight:700;margin:0 0 8px">{{ $section->title }}</h2>
            @if($section->subtitle)<p style="color:#6b7280;margin:0">{{ $section->subtitle }}</p>@endif
        </div>
        @endif
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px">
            @foreach($items as $item)
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.06);padding:32px 24px;text-align:center">
                @if(!empty($item['icon']))
                <div style="width:60px;height:60px;background:linear-gradient(135deg,#0A2D74,#1a4fa0);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
                    <i class="{{ $item['icon'] }}" style="color:#fff;font-size:1.3rem"></i>
                </div>
                @endif
                <h4 style="font-weight:700;margin:0 0 8px;font-size:1rem">{{ $item['title'] ?? '' }}</h4>
                <p style="color:#6b7280;font-size:.9rem;margin:0;line-height:1.6">{{ $item['description'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
