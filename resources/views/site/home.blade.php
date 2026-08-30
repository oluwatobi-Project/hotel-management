@extends('layouts.site')

@section('title', $settings['hotel_name'] ?? 'Grand Horizon Hotel')

@section('content')
<section class="hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-9">
                <p class="text-gold fw-semibold text-uppercase letter-spacing-2 mb-3" style="letter-spacing:.3em">Welcome to {{ $settings['hotel_name'] ?? 'Grand Horizon Hotel' }}</p>
                <h1 class="font-display fw-bold mb-3">Experience Luxury, Comfort &amp; Unforgettable Hospitality</h1>
                <p class="lead text-white-50 mb-4">{{ $settings['hotel_tagline'] ?? 'A serene escape in the heart of the city, with world-class amenities and exceptional service.' }}</p>
                <a href="{{ route('site.booking.create') }}" class="btn btn-gold btn-lg px-4 me-2"><i class="bi bi-calendar-check me-2"></i>Reserve Your Stay</a>
                <a href="{{ route('site.rooms') }}" class="btn btn-outline-light btn-lg px-4">Explore Rooms</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background:var(--cream)">
    <div class="container">
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-7">
                <h2 class="font-display fw-bold section-title">Our Featured Suites</h2>
                <p class="text-muted">Handpicked accommodations designed for relaxation and productivity.</p>
            </div>
        </div>
        <div class="row g-4">
            @foreach($roomTypes as $rt)
            <div class="col-md-6 col-lg-4">
                <div class="room-card bg-white h-100 d-flex flex-column">
                    <img src="{{ $rt->image_url ?? 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=80' }}" alt="{{ $rt->name }}">
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="font-display fw-semibold mb-0">{{ $rt->name }}</h5>
                            <span class="badge text-bg-light fw-semibold">{{ $rt->rooms_count }} {{ Str::plural('room', $rt->rooms_count) }}</span>
                        </div>
                        <p class="text-muted small flex-grow-1">{{ Str::limit($rt->description ?? 'A comfortable stay with modern amenities.', 110) }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fs-5 fw-bold" style="color:var(--navy)">{{ $settings['currency'] ?? '$' }}{{ number_format($rt->price, 2) }}<span class="text-muted small fw-normal"> / night</span></span>
                            <a href="{{ route('site.room-type', $rt->id) }}" class="btn btn-outline-gold btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('site.rooms') }}" class="btn btn-gold px-4">See All Rooms</a>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?auto=format&fit=crop&w=1200&q=80" class="img-fluid rounded-4 shadow" alt="Hotel lobby">
            </div>
            <div class="col-lg-6">
                <h2 class="font-display fw-bold section-title mb-3">Why Stay With Us?</h2>
                <p class="text-muted">{{ $settings['hotel_tagline'] ?? 'We blend modern comfort with warm hospitality to make every stay memorable.' }}</p>
                <div class="row g-4 mt-2">
                    <div class="col-sm-6">
                        <div class="d-flex gap-3">
                            <div class="feature-icon"><i class="bi bi-wifi"></i></div>
                            <div><h6 class="fw-semibold mb-1">Free High-Speed Wi-Fi</h6><p class="text-muted small mb-0">Stay connected throughout the property.</p></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex gap-3">
                            <div class="feature-icon"><i class="bi bi-cup-hot"></i></div>
                            <div><h6 class="fw-semibold mb-1">Daily Breakfast</h6><p class="text-muted small mb-0">A fresh spread to start your morning.</p></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex gap-3">
                            <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                            <div><h6 class="fw-semibold mb-1">24/7 Security</h6><p class="text-muted small mb-0">Your safety is our top priority.</p></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex gap-3">
                            <div class="feature-icon"><i class="bi bi-car-front"></i></div>
                            <div><h6 class="fw-semibold mb-1">Free Parking</h6><p class="text-muted small mb-0">Complimentary on-site parking.</p></div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('site.contact') }}" class="btn btn-outline-gold mt-4 px-4">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-navy text-white">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-lg-3">
                <h2 class="font-display fw-bold text-gold mb-0">{{ $stats['rooms'] }}+</h2>
                <p class="text-white-50 mb-0">Elegant Rooms</p>
            </div>
            <div class="col-6 col-lg-3">
                <h2 class="font-display fw-bold text-gold mb-0">{{ max(0, $stats['rooms'] - $stats['occupied']) }}+</h2>
                <p class="text-white-50 mb-0">Available Tonight</p>
            </div>
            <div class="col-6 col-lg-3">
                <h2 class="font-display fw-bold text-gold mb-0">24/7</h2>
                <p class="text-white-50 mb-0">Front Desk Service</p>
            </div>
            <div class="col-6 col-lg-3">
                <h2 class="font-display fw-bold text-gold mb-0">100%</h2>
                <p class="text-white-50 mb-0">Guest Satisfaction</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background:var(--gold-soft)">
    <div class="container">
        <div class="booking-widget p-4 p-lg-5">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <h3 class="font-display fw-bold mb-1">Ready for an unforgettable stay?</h3>
                    <p class="text-muted mb-0">Book directly to get the best rate. Reservations are held for 24 hours pending payment.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('site.booking.create') }}" class="btn btn-gold btn-lg px-4 w-100 w-lg-auto"><i class="bi bi-arrow-right me-2"></i>Book Your Stay</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
