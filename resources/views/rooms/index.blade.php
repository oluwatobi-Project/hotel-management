@extends('layouts.app')

@section('title', 'Rooms')
@section('page-title', 'Rooms')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="d-flex gap-2">
            <form class="d-flex gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" style="width:160px" placeholder="Room number…" value="{{ request('search') }}">
                <select name="status" class="form-select form-select-sm" style="width:150px" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                @if(request()->has('status') || request()->has('search'))
                    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                @endif
            </form>
        </div>
        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#roomModal" onclick="resetRoomForm()">
            <i class="bi bi-plus-lg"></i> Add Room
        </button>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @forelse($rooms as $room)
                <div class="col-md-4 col-xl-3">
                    <div class="room-card p-3 h-100 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0" style="color:var(--navy)">{{ $room->room_number }}</h6>
                            <span class="room-dot dot-{{ $room->status }}"></span>
                        </div>
                        <div class="small text-muted mb-1">{{ $room->roomType->name }} · Floor {{ $room->floor }}</div>
                        <div class="money mb-2">{{ $settings['currency'] }}{{ number_format($room->roomType->price, 2) }} <small class="text-muted fw-normal">/ night</small></div>
                        <div class="d-flex gap-2 mb-2">
                            <span class="badge text-bg-light">{{ $room->status }}</span>
                            <span class="badge text-bg-light">Cap {{ $room->roomType->capacity }}</span>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Status</button>
                                <ul class="dropdown-menu">
                                    @foreach($statuses as $status)
                                        @if($status !== $room->status)
                                            <li>
                                                <form action="{{ route('rooms.status', $room->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="status" value="{{ $status }}">
                                                    <button class="dropdown-item" type="submit">Mark {{ ucfirst($status) }}</button>
                                                </form>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#roomModal" onclick="fillRoomForm({!! $room->toJson() !!})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Delete room {{ $room->room_number }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-5">No rooms found</div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="roomForm" method="POST" action="{{ route('rooms.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="_method" value="POST" id="roomMethod">
            <div class="modal-header">
                <h5 class="modal-title" id="roomModalTitle">Add Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Room Number</label>
                        <input type="text" name="room_number" class="form-control" id="roomNumber" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Floor</label>
                        <input type="number" min="0" name="floor" class="form-control" id="roomFloor" value="1" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Room Type</label>
                        <select name="room_type_id" class="form-select" id="roomType" required>
                            @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} — {{ $settings['currency'] }}{{ number_format($type->price, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select" id="roomStatus">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" id="roomNotes"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetRoomForm() {
    document.getElementById('roomForm').reset();
    document.getElementById('roomForm').action = "{{ route('rooms.store') }}";
    document.getElementById('roomMethod').value = 'POST';
    document.getElementById('roomModalTitle').textContent = 'Add Room';
}
function fillRoomForm(r) {
    resetRoomForm();
    document.getElementById('roomForm').action = "/rooms/" + r.id;
    document.getElementById('roomMethod').value = 'PUT';
    document.getElementById('roomModalTitle').textContent = 'Edit Room ' + r.room_number;
    document.getElementById('roomNumber').value = r.room_number;
    document.getElementById('roomFloor').value = r.floor;
    document.getElementById('roomType').value = r.room_type_id;
    document.getElementById('roomStatus').value = r.status;
    document.getElementById('roomNotes').value = r.notes || '';
}
</script>
@endpush
