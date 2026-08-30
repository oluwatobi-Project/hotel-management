@extends('layouts.app')

@section('title', 'Room Requests')
@section('page-title', 'Room Requests')

@section('content')
@php
    $types = \App\Models\RoomRequest::TYPES;
    $statuses = \App\Models\RoomRequest::STATUSES;
@endphp

<div class="card">
    <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <form class="d-flex gap-2" method="GET">
            <select name="status" class="form-select form-select-sm" style="width:140px" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <select name="request_type" class="form-select form-select-sm" style="width:150px" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach($types as $type)
                    <option value="{{ $type }}" @selected(request('request_type') === $type)>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
            @if(request('status') || request('request_type'))
                <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </form>
        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#requestModal" onclick="resetRequestForm()">
            <i class="bi bi-plus-lg"></i> New Request
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Room</th>
                        <th>Guest / Booking</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td class="text-muted">#{{ $req->id }}</td>
                            <td class="fw-semibold">{{ $req->room->room_number }}</td>
                            <td>
                                @if($req->booking)
                                    <a href="{{ route('bookings.show', $req->booking->id) }}" class="text-decoration-none">{{ $req->booking->guest->name }}</a>
                                    <div class="small text-muted">{{ $req->booking->booking_ref }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><span class="badge text-bg-light"><i class="bi {{ match($req->request_type) { 'cleaning' => 'bi-brush', 'food' => 'bi-cup-hot', 'amenity' => 'bi-gem', 'maintenance' => 'bi-tools', default => 'bi-three-dots' } }} me-1"></i>{{ ucfirst($req->request_type) }}</span></td>
                            <td><small>{{ Str::limit($req->description, 55) }}</small></td>
                            <td>
                                <span class="badge {{ $req->priority === 'high' ? 'text-bg-danger' : ($req->priority === 'medium' ? 'text-bg-warning' : 'text-bg-secondary') }}">{{ ucfirst($req->priority) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ match($req->status) { 'completed' => 'text-bg-success', 'in_progress' => 'text-bg-primary', 'cancelled' => 'text-bg-danger', default => 'text-bg-secondary' } }}">{{ ucwords(str_replace('_', ' ', $req->status)) }}</span>
                            </td>
                            <td><small>{{ $req->assignee->name ?? '—' }}</small></td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Update</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach($statuses as $status)
                                            @if($status !== $req->status)
                                                <li>
                                                    <form action="{{ route('requests.status', $req->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="status" value="{{ $status }}">
                                                        <button class="dropdown-item" type="submit">Mark {{ ucwords(str_replace('_', ' ', $status)) }}</button>
                                                    </form>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#requestModal" onclick="fillRequestForm({!! $req->toJson() !!}, @json($req->assigned_to))">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('requests.destroy', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this request?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">No room requests found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $requests->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="requestForm" method="POST" action="{{ route('requests.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="_method" value="POST" id="requestMethod">
            <div class="modal-header">
                <h5 class="modal-title" id="requestModalTitle">New Room Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Room *</label>
                        <select name="room_id" class="form-select" id="reqRoom" required>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" data-occupied="{{ $room->status === 'occupied' ? 1 : 0 }}">
                                    {{ $room->room_number }} ({{ ucfirst($room->status) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Booking (optional)</label>
                        <select name="booking_id" class="form-select" id="reqBooking">
                            <option value="">—</option>
                            @foreach($bookings as $booking)
                                <option value="{{ $booking->id }}">{{ $booking->booking_ref }} · {{ $booking->guest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Guest (optional)</label>
                        <select name="guest_id" class="form-select" id="reqGuest">
                            <option value="">—</option>
                            @foreach($guests as $guest)
                                <option value="{{ $guest->id }}">{{ $guest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Request Type *</label>
                        <select name="request_type" class="form-select" id="reqType" required>
                            @foreach($types as $type)
                                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Priority *</label>
                        <select name="priority" class="form-select" id="reqPriority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select" id="reqStatus">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Assign To</label>
                        <select name="assigned_to" class="form-select" id="reqAssignee">
                            <option value="">Unassigned</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Description *</label>
                        <textarea name="description" class="form-control" rows="3" id="reqDescription" required placeholder="e.g. Need extra towels, room not cleaned, AC not working…"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-gold">Save Request</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetRequestForm() {
    document.getElementById('requestForm').reset();
    document.getElementById('requestForm').action = "{{ route('requests.store') }}";
    document.getElementById('requestMethod').value = 'POST';
    document.getElementById('requestModalTitle').textContent = 'New Room Request';
}
function fillRequestForm(r, assigned) {
    resetRequestForm();
    document.getElementById('requestForm').action = "/requests/" + r.id;
    document.getElementById('requestMethod').value = 'PUT';
    document.getElementById('requestModalTitle').textContent = 'Edit Request #' + r.id;
    document.getElementById('reqRoom').value = r.room_id;
    document.getElementById('reqBooking').value = r.booking_id || '';
    document.getElementById('reqGuest').value = r.guest_id || '';
    document.getElementById('reqType').value = r.request_type;
    document.getElementById('reqPriority').value = r.priority;
    document.getElementById('reqStatus').value = r.status;
    document.getElementById('reqAssignee').value = assigned || '';
    document.getElementById('reqDescription').value = r.description;
}
</script>
@endpush
