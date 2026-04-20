@extends('layouts.app')
@section('title', $brandName ?? 'Home')

@section('content')
@foreach($sections as $section)
    @includeIf('home.sections.' . $section->section_type, [
        'section'       => $section,
        'categories'    => $categories ?? collect(),
        'featuredTours' => $featuredTours ?? collect(),
        'topRatedTours' => $topRatedTours ?? collect(),
        'latestReviews' => $latestReviews ?? collect(),
    ])
@endforeach
@endsection
