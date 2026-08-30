@extends('layouts.app')

@section('title', 'Booking ' . $booking->booking_ref)
@section('page-title', 'Booking ' . $booking->booking_ref)

@section('content')
@php
    $map = ['reserved' => 'secondary', 'checked_in' => 'success', 'checked_out' => 'dark', 'cancelled' => 'danger'];
    $paid = $booking->payments->where('status', 'paid')->sum('amount');
@endphp

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        <span class="badge text-bg-{{ $map[$booking->status] }} badge-status fs-6">{{ ucwords(str_replace('_', ' ', $booking->status)) }}</span>
        <span class="text-muted small">Created {{ $booking->created_at->format('d M Y H:i') }} by {{ $booking->creator->name ?? 'System' }}</span>
    </div>
    <div class="btn-row">
        @if($booking->status === 'reserved')
            <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
            <form action="{{ route('bookings.check-in', $booking->id) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-success btn-sm"><i class="bi bi-box-arrow-in-right"></i> Check In</button>
            </form>
            <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this booking?')">
                @csrf
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-lg"></i> Cancel</button>
            </form>
        @elseif($booking->status === 'checked_in')
            <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#checkoutModal"><i class="bi bi-box-arrow-right"></i> Check Out</button>
            <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this booking?')">
                @csrf
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-lg"></i> Cancel</button>
            </form>
        @endif
        @if($booking->status !== 'checked_in')
            <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this booking?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-person me-2"></i>Guest & Room</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex gap-3">
                            <span class="avatar" style="width:44px;height:44px;font-size:16px">{{ strtoupper(substr($booking->guest->name, 0, 1)) }}</span>
                            <div>
                                <div class="fw-bold fs-6">{{ $booking->guest->name }}</div>
                                <div class="small text-muted">{{ $booking->guest->email ?? 'No email' }}</div>
                                <div class="small text-muted">{{ $booking->guest->phone ?? 'No phone' }}</div>
                                <div class="small text-muted">{{ $booking->guest->nationality ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-3">
                            <span class="avatar" style="width:44px;height:44px;font-size:16px;background:linear-gradient(135deg,#c8a24b,#a8822f)"><i class="bi bi-door-open"></i></span>
                            <div>
                                <div class="fw-bold fs-6">Room {{ $booking->room->room_number }} <span class="badge text-bg-light">{{ $booking->room->status }}</span></div>
                                <div class="small text-muted">{{ $booking->room->roomType->name }} · Floor {{ $booking->room->floor }}</div>
                                <div class="small text-muted">{{ $booking->room->roomType->amenities }}</div>
                                <div class="small text-muted">Capacity {{ $booking->room->roomType->capacity }} · {{ $booking->room->roomType->bed_count }} bed(s)</div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="small text-muted">Check-In</div>
                        <div class="fw-bold">{{ $booking->check_in_date->format('D, d M Y') }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Nights</div>
                        <div class="fw-bold">{{ $booking->nights() }}</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">Check-Out</div>
                        <div class="fw-bold">{{ $booking->check_out_date->format('D, d M Y') }}</div>
                    </div>
                </div>
                @if($booking->notes)
                    <hr>
                    <div class="small"><span class="fw-semibold">Notes:</span> {{ $booking->notes }}</div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-concierge-bell me-2"></i>Room Requests</h6>
                <a href="{{ route('requests.index') }}" class="small text-decoration-none" style="color:var(--gold)">Manage</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead><tr><th>Type</th><th>Description</th><th>Priority</th><th>Status</th><th>Assigned</th></tr></thead>
                        <tbody>
                            @forelse($booking->requests as $req)
                                <tr>
                                    <td><span class="badge text-bg-light">{{ ucfirst($req->request_type) }}</span></td>
                                    <td><small>{{ Str::limit($req->description, 60) }}</small></td>
                                    <td>
                                        <span class="badge {{ $req->priority === 'high' ? 'text-bg-danger' : ($req->priority === 'medium' ? 'text-bg-warning' : 'text-bg-secondary') }}">{{ ucfirst($req->priority) }}</span>
                                    </td>
                                    <td><span class="badge text-bg-light">{{ ucwords(str_replace('_', ' ', $req->status)) }}</span></td>
                                    <td><small>{{ $req->assignee->name ?? '—' }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No requests for this booking</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-receipt me-2"></i>Billing</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">{{ $booking->nights() }} night(s) rate</span>
                    <span class="money">{{ $settings['currency'] }}{{ number_format($booking->total_amount + $booking->discount, 2) }}</span>
                </div>
                @if($booking->discount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Discount</span>
                        <span class="text-danger">-{{ $settings['currency'] }}{{ number_format($booking->discount, 2) }}</span>
                    </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total</span>
                    <span class="fs-4 fw-bold money">{{ $settings['currency'] }}{{ number_format($booking->total_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <span class="text-muted">Paid</span>
                    <span class="fw-semibold text-success">{{ $settings['currency'] }}{{ number_format($paid, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Outstanding</span>
                    <span class="fw-semibold {{ $booking->total_amount - $paid > 0 ? 'text-danger' : 'text-success' }}">{{ $settings['currency'] }}{{ number_format(max(0, $booking->total_amount - $paid), 2) }}</span>
                </div>
                @if($booking->status === 'checked_in' && $booking->total_amount - $paid > 0)
                    <button class="btn btn-gold w-100 mt-3" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                        <i class="bi bi-cash-coin"></i> Collect Payment & Check Out
                    </button>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white"><h6 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-credit-card me-2"></i>Payments</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead><tr><th>Receipt</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse($booking->payments->sortByDesc('paid_at') as $payment)
                                <tr>
                                    <td><small>{{ $payment->receipt_no }}</small></td>
                                    <td class="money">{{ $settings['currency'] }}{{ number_format($payment->amount, 2) }}</td>
                                    <td><span class="badge text-bg-light">{{ ucfirst($payment->method) }}</span></td>
                                    <td><small class="text-muted">{{ $payment->paid_at?->format('d M Y') }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No payments yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($booking->status === 'checked_in')
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('bookings.check-out', $booking->id) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Check Out — {{ $booking->guest->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Room</label>
                        <input type="text" class="form-control" value="{{ $booking->room->room_number }} · {{ $booking->room->roomType->name }}" disabled>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Amount Due</label>
                        <input type="text" class="form-control fw-bold" value="{{ $settings['currency'] }}{{ number_format($booking->total_amount - $paid, 2) }}" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Payment Method</label>
                        <select name="method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile">Mobile Payment</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-gold"><i class="bi bi-check-lg"></i> Complete Check Out</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
