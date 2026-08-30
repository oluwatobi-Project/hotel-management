@extends('layouts.site')

@section('title', 'Contact Us — ' . ($settings['hotel_name'] ?? 'Grand Horizon Hotel'))

@section('content')
<section class="py-5 bg-navy text-white">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li>
                <li class="breadcrumb-item active text-white">Contact</li>
            </ol>
        </nav>
        <h1 class="font-display fw-bold mb-2">Contact Us</h1>
        <p class="text-white-50 mb-0">We would love to hear from you.</p>
    </div>
</section>

<section class="py-5" style="background:var(--cream)">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="bg-white rounded-4 p-4 h-100 shadow-sm">
                    <h5 class="font-display fw-bold mb-4">Get in Touch</h5>
                    <div class="d-flex gap-3 mb-4">
                        <div class="feature-icon"><i class="bi bi-geo-alt"></i></div>
                        <div><h6 class="fw-semibold mb-1">Address</h6><p class="text-muted mb-0">{{ $settings['hotel_address'] ?? '—' }}</p></div>
                    </div>
                    <div class="d-flex gap-3 mb-4">
                        <div class="feature-icon"><i class="bi bi-telephone"></i></div>
                        <div><h6 class="fw-semibold mb-1">Phone</h6><p class="text-muted mb-0">{{ $settings['hotel_phone'] ?? '—' }}</p></div>
                    </div>
                    <div class="d-flex gap-3 mb-4">
                        <div class="feature-icon"><i class="bi bi-envelope"></i></div>
                        <div><h6 class="fw-semibold mb-1">Email</h6><p class="text-muted mb-0">{{ $settings['hotel_email'] ?? '—' }}</p></div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="feature-icon"><i class="bi bi-clock"></i></div>
                        <div><h6 class="fw-semibold mb-1">Reception</h6><p class="text-muted mb-0">Open 24 hours, 7 days a week</p></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="bg-white rounded-4 p-4 shadow-sm">
                    <h5 class="font-display fw-bold mb-4">Send a Message</h5>
                    <form action="{{ route('site.contact') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Message</label>
                                <textarea name="message" rows="5" class="form-control" required></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-gold px-4" type="submit"><i class="bi bi-send me-2"></i>Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
