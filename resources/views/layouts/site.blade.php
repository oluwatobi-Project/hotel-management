<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $settings['hotel_name'] ?? 'Grand Horizon Hotel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0f2239; --navy-2: #16324f; --gold: #c8a24b; --gold-soft: #f5ecd9; --cream: #faf7f1;
        }
        body { font-family: 'Inter', system-ui, sans-serif; color: #1c2733; }
        .font-display { font-family: 'Playfair Display', serif; }
        .text-gold { color: var(--gold) !important; }
        .bg-navy { background: var(--navy) !important; }
        .btn-gold { background: var(--gold); color: #fff; border: 0; }
        .btn-gold:hover { color: #fff; filter: brightness(.96); }
        .btn-outline-gold { border: 1px solid var(--gold); color: var(--gold); }
        .btn-outline-gold:hover { background: var(--gold); color: #fff; }
        .navbar { background: rgba(15,34,57,.95); backdrop-filter: blur(8px); }
        .navbar .nav-link { color: #dbe4ef; }
        .navbar .nav-link:hover { color: var(--gold); }
        .navbar .nav-link.cta { background: var(--gold); color: #fff; border-radius: 8px; padding: .5rem 1.1rem; }
        .hero {
            background: linear-gradient(rgba(15,34,57,.72), rgba(15,34,57,.72)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1800&q=80') center/cover no-repeat;
            color: #fff; min-height: 70vh; display: flex; align-items: center;
        }
        .hero h1 { font-size: clamp(2.2rem, 5vw, 4rem); }
        .room-card { border: 1px solid #e9e2d3; border-radius: 16px; overflow: hidden; transition: transform .2s, box-shadow .2s; }
        .room-card:hover { transform: translateY(-4px); box-shadow: 0 14px 40px rgba(15,34,57,.14); }
        .room-card img { height: 210px; object-fit: cover; width: 100%; }
        .feature-icon {
            width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
            background: var(--gold-soft); color: var(--gold); font-size: 24px;
        }
        .footer { background: var(--navy); color: #aebcd0; }
        .footer a { color: #c9d4e2; text-decoration: none; }
        .footer a:hover { color: var(--gold); }
        .booking-widget { background: #fff; border-radius: 16px; box-shadow: 0 18px 50px rgba(15,34,57,.18); }
        .section-title { font-size: clamp(1.6rem, 3vw, 2.4rem); }
        .form-control:focus, .form-select:focus { border-color: var(--gold); box-shadow: 0 0 0 .2rem rgba(200,162,75,.18); }
        .breadcrumb-item a { color: var(--gold); text-decoration: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 text-white" href="{{ route('site.home') }}">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 text-white" style="width:38px;height:38px;background:linear-gradient(135deg,#c8a24b,#a8822f)">
                <i class="bi bi-building"></i>
            </span>
            <span class="font-display fw-bold">{{ $settings['hotel_name'] ?? 'Grand Horizon' }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="siteNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.home') ? 'text-gold' : '' }}" href="{{ route('site.home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.rooms', 'site.room-type') ? 'text-gold' : '' }}" href="{{ route('site.rooms') }}">Rooms</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.booking.lookup') ? 'text-gold' : '' }}" href="{{ route('site.booking.lookup') }}">My Booking</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('site.contact') ? 'text-gold' : '' }}" href="{{ route('site.contact') }}">Contact</a></li>
                <li class="nav-item ms-lg-2"><a class="nav-link cta" href="{{ route('site.booking.create') }}"><i class="bi bi-calendar-check me-1"></i>Book Now</a></li>
                <li class="nav-item ms-lg-1"><a class="nav-link" href="{{ route('login') }}"><i class="bi bi-person-lock me-1"></i>Staff</a></li>
            </ul>
        </div>
    </div>
</nav>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-0 mb-0" role="alert">
        <div class="container"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-0 mb-0" role="alert">
        <div class="container"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-0 mb-0" role="alert">
        <div class="container">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

@yield('content')

<footer class="footer pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="font-display text-white">{{ $settings['hotel_name'] ?? 'Grand Horizon Hotel' }}</h5>
                <p class="small mt-2">{{ $settings['hotel_address'] ?? '' }}</p>
            </div>
            <div class="col-lg-4">
                <h6 class="text-white mb-3">Quick Links</h6>
                <div class="d-flex flex-column gap-2 small">
                    <a href="{{ route('site.rooms') }}">Rooms & Suites</a>
                    <a href="{{ route('site.booking.create') }}">Book a Stay</a>
                    <a href="{{ route('site.booking.lookup') }}">Manage My Booking</a>
                    <a href="{{ route('site.contact') }}">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-4">
                <h6 class="text-white mb-3">Contact</h6>
                <div class="d-flex flex-column gap-2 small">
                    <span><i class="bi bi-geo-alt me-2 text-gold"></i>{{ $settings['hotel_address'] ?? '—' }}</span>
                    <span><i class="bi bi-telephone me-2 text-gold"></i>{{ $settings['hotel_phone'] ?? '—' }}</span>
                    <span><i class="bi bi-envelope me-2 text-gold"></i>{{ $settings['hotel_email'] ?? '—' }}</span>
                </div>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="text-center small">© {{ date('Y') }} {{ $settings['hotel_name'] ?? 'Grand Horizon Hotel' }} · All rights reserved</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
