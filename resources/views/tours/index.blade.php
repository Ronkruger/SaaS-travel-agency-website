@extends('layouts.app')
@section('title', 'All Plans')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Explore Our Plans</h1>
        <p>{{ $tours->total() }} plans available</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="tours-page-layout">

            <!-- Sidebar Filters -->
            <aside class="filter-sidebar" id="filterSidebar">
                <div class="filter-header">
                    <h3><i class="fas fa-filter"></i> Filters</h3>
                    <a href="{{ route('tours.index') }}" class="clear-filters">Clear All</a>
                </div>

                <form action="{{ route('tours.index') }}" method="GET" id="filterForm">
                    <!-- Search -->
                    <div class="filter-section">
                        <label>Keyword</label>
                        <div class="search-input-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Plan name or category...">
                        </div>
                    </div>

                    <!-- Region -->
                    <div class="filter-section">
                        <label>Region</label>
                        <select name="continent" class="form-control">
                            <option value="">All Regions</option>
                            @foreach($continents as $c)
                                <option value="{{ $c }}" {{ request('continent') == $c ? 'selected' : '' }}>
                                    {{ $c }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Duration -->
                    <div class="filter-section">
                        <label>Duration</label>
                        @foreach(['1-3' => '1–3 Days', '4-7' => '4–7 Days', '8-14' => '8–14 Days', '15+' => '15+ Days'] as $val => $label)
                            <label class="filter-check">
                                <input type="radio" name="duration" value="{{ $val }}"
                                    {{ request('duration') == $val ? 'checked' : '' }}>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>

                    <!-- Price Range -->
                    <div class="filter-section">
                        <label>Price Range (per person)</label>
                        <div class="price-range-inputs">
                            <input type="number" name="min_price" value="{{ request('min_price') }}"
                                placeholder="Min $" class="form-control" min="0">
                            <span>–</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}"
                                placeholder="Max $" class="form-control" min="0">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </form>
            </aside>

            <!-- Tours List -->
            <div class="tours-content">
                <!-- Toolbar -->
                <div class="tours-toolbar">
                    <span class="results-count">
                        Showing {{ $tours->firstItem() }}–{{ $tours->lastItem() }} of {{ $tours->total() }} plans
                    </span>
                    <div class="sort-row">
                        <label>Sort by:</label>
                        <select onchange="window.location = this.value" class="form-control form-control-sm">
                            @php
                                $sortParams = function($sort) use ($request) {
                                    return route('tours.index', array_merge($request->except(['sort', 'page']), ['sort' => $sort]));
                                };
                            @endphp
                            <option value="{{ $sortParams('latest') }}" {{ request('sort','latest') == 'latest' ? 'selected' : '' }}>Latest</option>
                            <option value="{{ $sortParams('popular') }}" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                            <option value="{{ $sortParams('rating') }}" {{ request('sort') == 'rating' ? 'selected' : '' }}>Top Rated</option>
                            <option value="{{ $sortParams('price_asc') }}" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ $sortParams('price_desc') }}" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                        <button class="btn btn-sm btn-outline mobile-filter-btn" id="mobileFilterBtn">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>
                </div>

                @if($tours->count() > 0)
                    <div class="tours-grid tours-grid--3">
                        @foreach($tours as $tour)
                            @include('partials.tour-card', ['tour' => $tour])
                        @endforeach
                    </div>

                    <div class="pagination-wrap">
                        {{ $tours->links('vendor.pagination.public') }}
                    </div>
                @else
                    <div class="empty-state large">
                        <i class="fas fa-search fa-3x"></i>
                        <h3>No plans found</h3>
                        <p>Try adjusting your filters or search terms.</p>
                        <a href="{{ route('tours.index') }}" class="btn btn-primary">
                            <i class="fas fa-undo"></i> Reset Filters
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
