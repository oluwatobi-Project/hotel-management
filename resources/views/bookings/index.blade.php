@extends('layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <form class="d-flex gap-2" method="GET">
            <input type="text" name="search" class="form-control form-control-sm" style="width:220px" placeholder="Search guest or reference…" value="{{ request('search') }}">
            <select name="status" class="form-select form-select-sm" style="width:150px" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach(['reserved', 'checked_in', 'checked_out', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            @if(request('status') || request('search'))
                <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </form>
        <a href="{{ route('bookings.create') }}" class="btn btn-gold btn-sm"><i class="bi bi-plus-lg"></i> New Booking</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Ref</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Nights</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td><span class="fw-semibold">{{ $booking->booking_ref }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar">{{ strtoupper(substr($booking->guest->name, 0, 1)) }}</span>
                                    <span class="fw-semibold">{{ $booking->guest->name }}</span>
                                </div>
                            </td>
                            <td>{{ $booking->room->room_number }} <small class="text-muted">· {{ $booking->room->roomType->name }}</small></td>
                            <td>{{ $booking->check_in_date->format('d M Y') }}</td>
                            <td>{{ $booking->check_out_date->format('d M Y') }}</td>
                            <td>{{ $booking->nights() }}</td>
                            <td class="money">{{ $settings['currency'] }}{{ number_format($booking->total_amount, 2) }}</td>
                            <td>
                                @php
                                    $map = ['reserved' => 'secondary', 'checked_in' => 'success', 'checked_out' => 'dark', 'cancelled' => 'danger'];
                                @endphp
                                <span class="badge text-bg-{{ $map[$booking->status] }} badge-status">{{ ucwords(str_replace('_', ' ', $booking->status)) }}</span>
                            </td>
                            <td class="text-end">
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
