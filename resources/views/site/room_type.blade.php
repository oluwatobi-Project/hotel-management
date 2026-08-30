@extends('layouts.site')

@section('title', $roomType->name . ' — ' . ($settings['hotel_name'] ?? 'Grand Horizon Hotel'))

@section('content')
<section class="py-5 bg-navy text-white">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('site.rooms') }}">Rooms</a></li>
                <li class="breadcrumb-item active text-white">{{ $roomType->name }}</li>
            </ol>
        </nav>
        <h1 class="font-display fw-bold mb-2">{{ $roomType->name }}</h1>
        <p class="text-white-50 mb-0">{{ $roomType->rooms_count }} {{ Str::plural('room', $roomType->rooms_count) }} available in this category.</p>
    </div>
</section>

<section class="py-5" style="background:var(--cream)">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <img src="{{ $roomType->image_url ?? 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1200&q=80' }}" class="img-fluid rounded-4 shadow w-100" alt="{{ $roomType->name }}">
                <h3 class="font-display fw-bold mt-4">About this room</h3>
                <p class="text-muted">{{ $roomType->description }}</p>
            </div>
            <div class="col-lg-5">
                <div class="booking-widget p-4">
                    <h5 class="font-display fw-bold mb-1">{{ $roomType->name }}</h5>
                    <div class="mb-3">
                        <span class="fs-3 fw-bold" style="color:var(--navy)">{{ $settings['currency'] ?? '$' }}{{ number_format($roomType->price, 2) }}</span>
                        <span class="text-muted"> / night</span>
                    </div>
                    <ul class="list-unstyled small mb-4">
                        <li class="mb-2"><i class="bi bi-check2-circle text-gold me-2"></i>Free Wi-Fi &amp; cable TV</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-gold me-2"></i>Daily housekeeping</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-gold me-2"></i>Air conditioning</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-gold me-2"></i>Complimentary breakfast</li>
                    </ul>
                    <a href="{{ route('site.booking.create') }}" class="btn btn-gold w-100 py-2">Book This Room</a>
                    <p class="text-muted small text-center mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>Reservations are held for 24 hours pending payment.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
