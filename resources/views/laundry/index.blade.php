@extends('layouts.app')

@section('title', 'Laundry')
@section('page-title', 'Laundry Service')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0" style="color:var(--navy)">Laundry Requests</h6>
        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#laundryModal">
            <i class="bi bi-plus-lg"></i> New Request
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Guest</th>
                        <th>Service</th>
                        <th>Items</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>When</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $laundry)
                        <tr>
                            <td>{{ $laundry->room?->room_number ?? $laundry->booking?->room?->room_number ?? '—' }}</td>
                            <td>{{ $laundry->guest?->name ?? $laundry->booking?->guest?->name ?? 'Walk-in' }}</td>
                            <td><span class="badge text-bg-light">{{ str_replace('_', ' ', $laundry->service_type) }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $laundry->item_description }}</div>
                                <small class="text-muted">{{ $laundry->quantity }} item(s)</small>
                            </td>
                            <td class="money">{{ $settings['currency'] }}{{ number_format($laundry->estimated_cost, 2) }}</td>
                            <td>
                                <span class="badge badge-status {{ match($laundry->status) { 'pending' => 'text-bg-warning', 'in_progress' => 'text-bg-info', 'completed' => 'text-bg-success', 'cancelled' => 'text-bg-danger', default => 'text-bg-light' } }}">
                                    {{ str_replace('_', ' ', $laundry->status) }}
                                </span>
                            </td>
                            <td><small class="text-muted">{{ $laundry->created_at->diffForHumans() }}</small></td>
                            <td class="text-end">
                                <div class="dropdown d-inline">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Update</button>
                                    <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:180px">
                                        @foreach(\App\Models\LaundryRequest::STATUSES as $status)
                                            @if($status !== $laundry->status)
                                                <li>
                                                    <form method="POST" action="{{ route('laundry.status', $laundry->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $status }}">
                                                        @if($status === 'completed')
                                                            <div class="input-group input-group-sm mb-1">
                                                                <span class="input-group-text">Cost</span>
                                                                <input type="number" step="0.01" min="0" name="estimated_cost" class="form-control" value="{{ $laundry->estimated_cost }}" required>
                                                            </div>
                                                        @endif
                                                        <button class="dropdown-item small">{{ ucfirst(str_replace('_', ' ', $status)) }}</button>
                                                    </form>
                                                </li>
                                            @endif
                                        @endforeach
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('laundry.destroy', $laundry->id) }}" onsubmit="return confirm('Delete this request?')">
                                                @csrf @method('DELETE')
                                                <button class="dropdown-item small text-danger">Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No laundry requests found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $requests->links() }}</div>
    </div>
</div>

<div class="modal fade" id="laundryModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('laundry.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">New Laundry Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Room</label>
                        <select name="room_id" class="form-select">
                            <option value="">— Walk-in / pick later —</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">Room {{ $room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Service Type</label>
                        <select name="service_type" class="form-select" required>
                            @foreach(\App\Models\LaundryRequest::SERVICE_TYPES as $serviceType)
                                <option value="{{ $serviceType }}">{{ ucwords(str_replace('_', ' ', $serviceType)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Booking (optional)</label>
                        <select name="booking_id" class="form-select">
                            <option value="">None</option>
                            @foreach($bookings as $booking)
                                <option value="{{ $booking->id }}">{{ $booking->booking_ref }} · {{ $booking->guest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Guest (optional)</label>
                        <select name="guest_id" class="form-select">
                            <option value="">None</option>
                            @foreach($guests as $guest)
                                <option value="{{ $guest->id }}">{{ $guest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-8">
                        <label class="form-label small fw-semibold">Item Description</label>
                        <input type="text" name="item_description" class="form-control" placeholder="e.g. 2 shirts, 1 trousers" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-semibold">Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Estimated Cost</label>
                        <input type="number" step="0.01" min="0" name="estimated_cost" class="form-control" value="0" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Request</button>
            </div>
        </form>
    </div>
</div>
@endsection
