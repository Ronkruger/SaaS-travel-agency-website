@extends('layouts.app')
@section('title', 'Find My Booking')

@section('content')
<div class="page-header">
    <div class="container">
        <h1>Find My Booking</h1>
        <p>Link an existing reservation to your account</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">

                @if (session('success'))
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-search"></i> Find Your Reservation</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Already booked a tour with us before creating an account? Enter the name
                            used on your reservation and the tour date below to link it to your account.
                        </p>

                        <form action="{{ route('booking.claim') }}" method="POST">
                            @csrf

                            <div class="form-group mb-3">
                                <label for="contact_name" class="form-label">Full Name on Booking <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="contact_name"
                                    id="contact_name"
                                    class="form-control @error('contact_name') is-invalid @enderror"
                                    value="{{ old('contact_name') }}"
                                    placeholder="e.g. Juan dela Cruz"
                                    required
                                >
                                @error('contact_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="tour_date" class="form-label">Tour Date <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    name="tour_date"
                                    id="tour_date"
                                    class="form-control @error('tour_date') is-invalid @enderror"
                                    value="{{ old('tour_date') }}"
                                    required
                                >
                                @error('tour_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-link"></i> Find &amp; Link Booking
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted small">
                        <i class="fas fa-info-circle"></i>
                        The name must match exactly as recorded in your reservation.
                        Contact us at <a href="{{ route('contact') }}">support</a> if you need help.
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
