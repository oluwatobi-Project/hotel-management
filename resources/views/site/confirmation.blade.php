@extends('layouts.site')

@section('title', 'Booking Confirmed — ' . ($settings['hotel_name'] ?? 'Grand Horizon Hotel'))

@section('content')
<section class="py-5" style="background:var(--cream); min-height:60vh">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="bg-white rounded-4 shadow-sm p-4 p-lg-5 text-center">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width:76px;height:76px;background:#e8f6ec">
                        <i class="bi bi-check-lg fs-1" style="color:#198754"></i>
                    </div>
                    <h3 class="font-display fw-bold mb-1">Reservation Confirmed</h3>
                    <p class="text-muted mb-4">A confirmation email has been sent to <strong>{{ $booking->guest->email }}</strong>.</p>

                    <div class="alert alert-warning text-start d-flex">
                        <i class="bi bi-clock-history me-3 fs-5"></i>
                        <div>Your reservation <strong>{{ $booking->booking_ref }}</strong> is being held for <strong>24 hours</strong>.
                        Please complete your payment (full or partial deposit) before the hold expires to secure your room.</div>
                    </div>

                    <div class="text-start border rounded-3 p-4 mb-4">
                        <h6 class="fw-semibold text-muted text-uppercase small mb-3">Booking Summary</h6>
                        <div class="row g-2">
                            <div class="col-sm-6"><span class="text-muted small">Booking Ref</span><div class="fw-semibold">{{ $booking->booking_ref }}</div></div>
                            <div class="col-sm-6"><span class="text-muted small">Guest</span><div class="fw-semibold">{{ $booking->guest->name }}</div></div>
                            <div class="col-sm-6"><span class="text-muted small">Room</span><div class="fw-semibold">Room {{ $booking->room->room_number }} ({{ $booking->room->roomType->name }})</div></div>
                            <div class="col-sm-6"><span class="text-muted small">Dates</span><div class="fw-semibold">{{ $booking->check_in_date }} → {{ $booking->check_out_date }} ({{ $booking->nights() }} nights)</div></div>
                            <div class="col-sm-6"><span class="text-muted small">Total</span><div class="fw-semibold">{{ $settings['currency'] ?? '$' }}{{ number_format($booking->total_amount, 2) }}</div></div>
                            <div class="col-sm-6"><span class="text-muted small">Paid</span><div class="fw-semibold text-success">{{ $settings['currency'] ?? '$' }}{{ number_format($booking->paidAmount(), 2) }}</div></div>
                            <div class="col-sm-6"><span class="text-muted small">Balance Due</span><div class="fw-bold" style="color:var(--navy)">{{ $settings['currency'] ?? '$' }}{{ number_format($booking->outstandingAmount(), 2) }}</div></div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                        <a href="{{ route('site.booking.portal', $booking->id) }}" class="btn btn-gold px-4"><i class="bi bi-credit-card me-2"></i>Pay Now</a>
                        <a href="{{ route('site.home') }}" class="btn btn-outline-gold px-4">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
