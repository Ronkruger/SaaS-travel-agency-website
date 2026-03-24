@extends('layouts.app')
@section('title', 'My Wishlist — DiscoverGRP')

@section('content')
<section class="page-header">
    <div class="container">
        <h1>My Wishlist</h1>
        <p>Tours you've saved for later</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-check-circle"></i>
            {{ session('success') }}
            <button class="alert-close">&times;</button>
        </div>
        @endif

        @if($tours->count())
        <div class="tours-toolbar">
            <p class="results-count">
                <strong>{{ $tours->count() }}</strong> {{ Str::plural('tour', $tours->count()) }} saved
            </p>
        </div>

        <div class="tours-grid tours-grid--3">
            @foreach($tours as $tour)
            @include('partials.tour-card', ['tour' => $tour])
            @endforeach
        </div>
        @else
        <div class="empty-state large">
            <i class="fa-regular fa-heart" style="font-size:4rem;color:var(--gray-300);"></i>
            <h3>Your wishlist is empty</h3>
            <p class="text-muted">Save tours by clicking the heart icon, and they'll appear here.</p>
            <a href="{{ route('tours.index') }}" class="btn btn-primary">
                <i class="fa-solid fa-compass"></i> Explore Tours
            </a>
        </div>
        @endif
    </div>
</section>
@endsection
