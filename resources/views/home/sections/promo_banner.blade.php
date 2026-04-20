@php $c = $section->content ?? []; $imgUrl = $c['image_url'] ?? $section->image_url ?? ''; @endphp
@if($imgUrl)
<section style="padding:0">
    @if(!empty($c['link']))
    <a href="{{ $c['link'] }}" style="display:block">
        <img src="{{ $imgUrl }}" alt="{{ $section->title ?? 'Promo' }}" style="width:100%;display:block" loading="lazy">
    </a>
    @else
    <img src="{{ $imgUrl }}" alt="{{ $section->title ?? 'Promo' }}" style="width:100%;display:block" loading="lazy">
    @endif
</section>
@endif
