@extends('layouts.app')

@section('title', 'Invoice '.$booking->booking_ref)
@section('page-title', 'Invoice '.$booking->booking_ref)

@section('content')
@php
    $map = ['reserved' => 'secondary', 'checked_in' => 'success', 'checked_out' => 'dark', 'cancelled' => 'danger'];
    $statusBadge = ['reserved' => 'Proforma — payment due', 'checked_in' => 'In-house — balance per statement', 'checked_out' => 'Settled in full', 'cancelled' => 'No charge'];
    $payments = $booking->payments->sortByDesc('paid_at');
@endphp

<div class="invoice-toolbar d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3 d-print-none">
    <div>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> All Invoices</a>
        <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i> Booking</a>
    </div>
    <button onclick="window.print()" class="btn btn-gold btn-sm"><i class="bi bi-printer"></i> Print / Save PDF</button>
</div>

<style>
    @media print {
        .sidebar, .navbar, .alert, .invoice-toolbar { display: none !important; }
        .main { margin-left: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
        .invoice-sheet { box-shadow: none !important; border: 0 !important; }
    }
    .invoice-sheet { background:#fff; border:1px solid #e6ebf2; border-radius:14px; max-width:820px; margin:0 auto; padding:34px 38px; }
    .invoice-brand { width:44px; height:44px; border-radius:10px; background:linear-gradient(135deg,#0f2239,#c8a24b); color:#fff; display:flex; align-items:center; justify-content:center; font-size:20px; }
    .invoice-muted { color:#6b7a8c; font-size:12px; }
    .invoice-th { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#6b7a8c; }
    .inv-rule { border-top:1px dashed #dfe6ef; }
</style>

<div class="invoice-sheet">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="invoice-brand"><i class="bi bi-building"></i></div>
            <div>
                <div class="fw-bold fs-5" style="color:var(--navy)">{{ $settings['hotel_name'] ?? config('app.name') }}</div>
                <div class="invoice-muted">{{ $settings['hotel_address'] }}</div>
                <div class="invoice-muted">{{ $settings['hotel_phone'] }} @if($settings['hotel_email']) · {{ $settings['hotel_email'] }} @endif</div>
            </div>
        </div>
        <div class="text-md-end">
            <div class="fs-4 fw-bold" style="color:var(--navy)">{{ $document['label'] }}</div>
            <div class="invoice-muted">{{ $document['subtitle'] }}</div>
            <div class="mt-1"><span class="badge text-bg-{{ $map[$booking->status] }} badge-status">{{ ucwords(str_replace('_', ' ', $booking->status)) }}</span></div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-6">
            <div class="invoice-th mb-1">Bill To</div>
            <div class="fw-semibold">{{ $booking->guest->name }}</div>
            <div class="invoice-muted">{{ $booking->guest->email }}</div>
            <div class="invoice-muted">{{ $booking->guest->phone }}</div>
            <div class="invoice-muted">{{ $booking->guest->nationality }}</div>
        </div>
        <div class="col-6 text-md-end">
            <div class="d-flex justify-content-md-end gap-5">
                <div>
                    <div class="invoice-th mb-1">Invoice No.</div>
                    <div class="fw-semibold">INV-{{ $booking->booking_ref }}</div>
                </div>
                <div>
                    <div class="invoice-th mb-1">Issued</div>
                    <div class="fw-semibold">{{ now()->format('d M Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4 g-2">
        <div class="col-md-4">
            <div class="invoice-th mb-1">Booking</div>
            <div class="fw-semibold">{{ $booking->booking_ref }}</div>
        </div>
        <div class="col-md-4">
            <div class="invoice-th mb-1">Room</div>
            <div class="fw-semibold">{{ $booking->room->room_number }} <span class="text-muted fw-normal">· {{ $booking->room->roomType->name }} (Floor {{ $booking->room->floor }})</span></div>
        </div>
        <div class="col-md-4">
            <div class="invoice-th mb-1">Stay</div>
            <div class="fw-semibold">{{ $booking->check_in_date->format('d M Y') }} → {{ $booking->check_out_date->format('d M Y') }}</div>
            <div class="invoice-muted">{{ $nights }} night(s)</div>
        </div>
    </div>

    <table class="table mt-4 mb-0">
        <thead>
            <tr>
                <th class="invoice-th">Description</th>
                <th class="invoice-th text-end">Qty</th>
                <th class="invoice-th text-end">Rate</th>
                <th class="invoice-th text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Accommodation — {{ $booking->room->roomType->name }}
                    <div class="invoice-muted">{{ $booking->check_in_date->format('D, d M Y') }} to {{ $booking->check_out_date->format('D, d M Y') }}</div>
                </td>
                <td class="text-end">{{ $nights }}</td>
                <td class="text-end">{{ $settings['currency'] }}{{ number_format($ratePerNight, 2) }}</td>
                <td class="text-end money">{{ $settings['currency'] }}{{ number_format($booking->total_amount + $booking->discount, 2) }}</td>
            </tr>
            @if($booking->discount > 0)
                <tr>
                    <td colspan="3">Discount</td>
                    <td class="text-end text-danger">-{{ $settings['currency'] }}{{ number_format($booking->discount, 2) }}</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="inv-rule">
                <td colspan="3" class="text-end fw-bold">Total</td>
                <td class="text-end fs-5 fw-bold money">{{ $settings['currency'] }}{{ number_format($booking->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-end text-success">Paid</td>
                <td class="text-end text-success fw-semibold">{{ $settings['currency'] }}{{ number_format($paid, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-end fw-bold">Balance Due</td>
                <td class="text-end fw-bold {{ $due > 0 ? 'text-danger' : 'text-success' }}">{{ $settings['currency'] }}{{ number_format($due, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="d-flex align-items-center gap-2 mt-3">
        <span class="badge text-bg-{{ $map[$booking->status] }} badge-status">{{ ucwords(str_replace('_', ' ', $booking->status)) }}</span>
        <span class="small text-muted">{{ $statusBadge[$booking->status] }}</span>
    </div>
    @if($due > 0 && in_array($booking->status, ['reserved', 'checked_in']))
        <div class="alert alert-{{ $booking->status === 'reserved' ? 'warning' : 'info' }} py-2 mt-3 mb-0 small">
            <i class="bi bi-info-circle me-2"></i>
            {{ $booking->status === 'reserved' ? 'This proforma invoice is due before check-in. Please settle at the front desk or via the guest payment portal.' : 'Outstanding balance should be settled before or at check-out.' }}
        </div>
    @endif

    @if($payments->count())
        <h6 class="fw-bold mt-4 mb-2" style="color:var(--navy)"><i class="bi bi-credit-card me-2"></i>Payments</h6>
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th class="invoice-th">Receipt</th>
                    <th class="invoice-th text-end">Amount</th>
                    <th class="invoice-th">Method</th>
                    <th class="invoice-th">Date</th>
                    <th class="invoice-th">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td><span class="fw-semibold">{{ $payment->receipt_no }}</span></td>
                        <td class="text-end money">{{ $settings['currency'] }}{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst($payment->method) }}</td>
                        <td>{{ $payment->paid_at?->format('d M Y H:i') }}</td>
                        <td>
                            @if($payment->status === 'paid')
                                <span class="badge text-bg-success badge-status">Paid</span>
                            @else
                                <span class="badge text-bg-secondary badge-status">Refunded</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="inv-rule pt-3 mt-4">
        <div class="invoice-muted text-center">
            Thank you for choosing {{ $settings['hotel_name'] ?? config('app.name') }}.
            Questions? Call {{ $settings['hotel_phone'] }} or email {{ $settings['hotel_email'] }}.
        </div>
    </div>
</div>
@endsection
