@if(isset($latestReviews) && $latestReviews->count())
<section class="section" style="padding:60px 0;background:#f9fafb">
    <div class="container">
        <div style="text-align:center;margin-bottom:40px">
            <h2 style="font-size:1.8rem;font-weight:700;margin:0 0 8px">{{ $section->title ?? 'What Our Travelers Say' }}</h2>
            @if($section->subtitle)<p style="color:#6b7280;margin:0">{{ $section->subtitle }}</p>@endif
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px">
            @foreach($latestReviews as $review)
            <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,.06);padding:28px">
                <div style="display:flex;gap:4px;margin-bottom:12px">
                    @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star" style="color:{{ $i <= $review->rating ? '#F5A623' : '#e5e7eb' }};font-size:.85rem"></i>
                    @endfor
                </div>
                <p style="color:#374151;font-size:.9rem;line-height:1.6;margin:0 0 16px">{{ Str::limit($review->comment, 150) }}</p>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#0A2D74,#1a4fa0);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.8rem">
                        {{ strtoupper(substr($review->reviewer_name ?? 'G', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem">{{ $review->reviewer_name ?? 'Guest' }}</div>
                        @if($review->tour)<div style="font-size:.75rem;color:#9ca3af">{{ Str::limit($review->tour->title, 30) }}</div>@endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
