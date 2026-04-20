@php $s = $section->settings ?? []; @endphp
@if(isset($featuredTours) && $featuredTours->count())
<section style="padding:{{ ($s['padding_y'] ?? '60') . 'px' }} 0;background-color:{{ $s['bg_color'] ?? '#ffffff' }}">
    <div class="container">
        <div style="text-align:center;margin-bottom:40px">
            <h2 style="font-size:1.8rem;font-weight:700;margin:0 0 8px;color:{{ $s['heading_color'] ?? '#111827' }}">{{ $section->title ?? 'Featured Tours' }}</h2>
            @if($section->subtitle)<p style="color:{{ $s['text_color'] ?? '#6b7280' }};margin:0">{{ $section->subtitle }}</p>@endif
        </div>
        <div class="tours-grid">
            @foreach($featuredTours as $tour)
                @include('partials.tour-card', ['tour' => $tour])
            @endforeach
        </div>
        <div style="text-align:center;margin-top:32px">
            <a href="{{ route('tours.index') }}" class="btn btn-outline">View All Tours <i class="fas fa-arrow-right" style="margin-left:6px"></i></a>
        </div>
    </div>
</section>
@endif
