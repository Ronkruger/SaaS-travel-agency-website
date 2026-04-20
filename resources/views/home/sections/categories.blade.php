@if(isset($categories) && $categories->count())
<section class="section" style="padding:60px 0;background:#f9fafb">
    <div class="container">
        <div style="text-align:center;margin-bottom:40px">
            <h2 style="font-size:1.8rem;font-weight:700;margin:0 0 8px">{{ $section->title ?? 'Tour Categories' }}</h2>
            @if($section->subtitle)<p style="color:#6b7280;margin:0">{{ $section->subtitle }}</p>@endif
        </div>
        <div class="categories-grid">
            @foreach($categories as $category)
            <a href="{{ route('tours.index', ['category' => $category->slug]) }}" class="category-card">
                <img src="{{ cdn_url($category->image, asset('images/category-placeholder.jpg')) }}" alt="{{ $category->name }}" loading="lazy">
                <div class="category-card-overlay">
                    <h3>{{ $category->name }}</h3>
                    <span>{{ $category->tours_count ?? 0 }} {{ Str::plural('tour', $category->tours_count ?? 0) }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
