@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('content')
@php
    $map = ['reserved' => 'secondary', 'checked_in' => 'success', 'checked_out' => 'dark', 'cancelled' => 'danger'];
@endphp

<div class="d-flex flex-wrap gap-2 mb-3">
    @foreach(['reserved' => 'Reserved', 'checked_in' => 'Checked In', 'checked_out' => 'Checked Out', 'cancelled' => 'Cancelled'] as $status => $label)
        <a href="{{ route('invoices.index', ['status' => $status]) }}" class="badge text-bg-{{ $map[$status] }} text-decoration-none badge-status py-2 px-3 {{ request('status') === $status ? 'opacity-100' : 'opacity-50' }}">
            {{ $label }} · {{ $totals[$status] }}
        </a>
    @endforeach
</div>

<div class="card">
    <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <form class="d-flex gap-2" method="GET">
            <input type="text" name="search" class="form-control form-control-sm" style="width:220px" placeholder="Search guest, ref or room…" value="{{ request('search') }}">
            <select name="status" class="form-select form-select-sm" style="width:150px" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach(['reserved', 'checked_in', 'checked_out', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            @if(request('status') || request('search'))
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </form>
        <span class="small text-muted">Generate a status-aware invoice for any booking.</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Dates</th>
                        <th>Nights</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar">{{ strtoupper(substr($booking->guest->name, 0, 1)) }}</span>
                                    <div>
                                        <div class="fw-semibold">{{ $booking->guest->name }}</div>
                                        <div class="small text-muted">{{ $booking->booking_ref }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $booking->room->room_number }} <small class="text-muted">· {{ $booking->room->roomType->name }}</small></td>
                            <td><small class="text-muted">{{ $booking->check_in_date->format('d M') }} → {{ $booking->check_out_date->format('d M Y') }}</small></td>
                            <td>{{ $booking->nights() }}</td>
                            <td class="money">{{ $settings['currency'] }}{{ number_format($booking->total_amount, 2) }}</td>
                            <td class="text-success">{{ $settings['currency'] }}{{ number_format($booking->paidAmount(), 2) }}</td>
                            <td class="{{ $booking->outstandingAmount() > 0 ? 'text-danger' : 'text-muted' }}">{{ $settings['currency'] }}{{ number_format($booking->outstandingAmount(), 2) }}</td>
                            <td>
                                <span class="badge text-bg-{{ $map[$booking->status] }} badge-status">{{ ucwords(str_replace('_', ' ', $booking->status)) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('invoices.show', $booking->id) }}" class="btn btn-outline-secondary btn-sm" title="Invoice"><i class="bi bi-file-earmark-text"></i> Invoice</a>
                                <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No bookings found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
