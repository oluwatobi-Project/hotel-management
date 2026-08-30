@extends('layouts.site')

@section('title', 'Find My Booking — ' . ($settings['hotel_name'] ?? 'Grand Horizon Hotel'))

@section('content')
<section class="py-5" style="background:var(--cream); min-height:60vh">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="bg-white rounded-4 shadow-sm p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width:76px;height:76px;background:var(--gold-soft)">
                            <i class="bi bi-search fs-2" style="color:var(--gold)"></i>
                        </div>
                        <h4 class="font-display fw-bold mb-1">Manage Your Booking</h4>
                        <p class="text-muted mb-0">Enter your booking reference and the email you used to book.</p>
                    </div>

                    <form action="{{ route('site.booking.verify') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Booking Reference</label>
                            <input type="text" name="booking_ref" class="form-control text-uppercase" placeholder="e.g. BK-2026XXXX-XXXX" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                        </div>
                        <button class="btn btn-gold w-100 py-2"><i class="bi bi-key me-2"></i>Access My Booking</button>
                    </form>

                    <p class="text-center text-muted small mt-4 mb-0">
                        New guest? <a href="{{ route('site.booking.create') }}" class="text-decoration-none">Book a stay</a> instead.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
