@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payments')

@section('content')
<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <span class="text-muted small d-block">Total Collected</span>
            <span class="fs-3 fw-bold money">{{ $settings['currency'] }}{{ number_format($total, 2) }}</span>
        </div>
        <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="bi bi-cash-coin me-1"></i> Record Payment
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <form class="d-flex gap-2" method="GET">
            <input type="text" name="search" class="form-control form-control-sm" style="width:220px" placeholder="Search receipt or guest…" value="{{ request('search') }}">
            <select name="method" class="form-select form-select-sm" style="width:150px" onchange="this.form.submit()">
                <option value="">All methods</option>
                @foreach(['cash', 'card', 'mobile'] as $method)
                    <option value="{{ $method }}" @selected(request('method') === $method)>{{ ucfirst($method) }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Receipt No</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Booking Ref</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Paid At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td><span class="fw-semibold">{{ $payment->receipt_no }}</span></td>
                            <td>{{ $payment->booking->guest->name }}</td>
                            <td>{{ $payment->booking->room->room_number }}</td>
                            <td><small class="text-muted">{{ $payment->booking->booking_ref }}</small></td>
                            <td class="money">{{ $settings['currency'] }}{{ number_format($payment->amount, 2) }}</td>
                            <td><span class="badge text-bg-light">{{ ucfirst($payment->method) }}</span></td>
                            <td>
                                @if($payment->status === 'paid')
                                    <span class="badge text-bg-success">Paid</span>
                                @else
                                    <span class="badge text-bg-danger">Refunded</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $payment->paid_at?->format('d M Y H:i') }}</td>
                            <td class="text-end">
                                @if($payment->status === 'paid')
                                    <form action="{{ route('payments.refund', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Refund this payment?')">
                                        @csrf
                                        <button class="btn btn-outline-warning btn-sm"><i class="bi bi-arrow-counterclockwise"></i> Refund</button>
                                    </form>
                                @endif
                                <form action="{{ route('payments.destroy', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this payment record?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No payments found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $payments->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('payments.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Booking</label>
                        <select name="booking_id" class="form-select" id="payBooking" required>
                            @foreach(\App\Models\Booking::with(['guest', 'room'])->whereIn('status', ['checked_in', 'reserved'])->orderByDesc('created_at')->limit(50)->get() as $b)
                                <option value="{{ $b->id }}">{{ $b->booking_ref }} · {{ $b->guest->name }} · Room {{ $b->room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Amount</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Method</label>
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
                <button type="submit" class="btn btn-gold">Record Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection
