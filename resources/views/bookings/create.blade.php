@extends('layouts.app')

@section('title', 'New Booking')
@section('page-title', 'New Booking')

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-calendar-plus me-2"></i>Booking Details</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bookings.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Guest *</label>
                            <select name="guest_id" id="guestSelect" class="form-select" required>
                                <option value="">Select a guest…</option>
                                @foreach($guests as $guest)
                                    <option value="{{ $guest->id }}" @selected(old('guest_id') == $guest->id)>
                                        {{ $guest->name }}{{ $guest->phone ? ' · ' . $guest->phone : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#quickGuestModal">
                                <i class="bi bi-person-plus me-1"></i> New Guest
                            </button>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Check-In Date *</label>
                            <input type="date" name="check_in_date" id="checkIn" class="form-control" value="{{ old('check_in_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Check-Out Date *</label>
                            <input type="date" name="check_out_date" id="checkOut" class="form-control" value="{{ old('check_out_date', now()->addDay()->toDateString()) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Room *</label>
                            <select name="room_id" id="roomSelect" class="form-select" required disabled>
                                <option value="">Select check-in / check-out dates first…</option>
                            </select>
                            <div class="form-text" id="roomStatusMsg">Available rooms will load once both dates are set.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Discount ({{ $settings['currency'] }})</label>
                            <input type="number" step="0.01" min="0" name="discount" class="form-control" id="discount" value="{{ old('discount', 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Special requests…">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total:</span>
                            <span class="fs-5 fw-bold money" id="totalPreview">{{ $settings['currency'] }}0.00</span>
                            <small class="text-muted d-block" id="breakdown">Select dates and a room.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-gold px-4"><i class="bi bi-check-lg"></i> Create Booking</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-info-circle me-2"></i>Room Rates</h6>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr><th>Type</th><th class="text-end">Price / Night</th></tr>
                    </thead>
                    <tbody>
                        @foreach($rooms->pluck('roomType')->unique('id') as $type)
                            <tr>
                                <td>{{ $type->name }}</td>
                                <td class="text-end money">{{ $settings['currency'] }}{{ number_format($type->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quickGuestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Add Guest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickGuestForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Full Name *</label>
                            <input type="text" id="qgName" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Phone</label>
                            <input type="text" id="qgPhone" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" id="qgEmail" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">ID Card</label>
                            <input type="text" id="qgIdCard" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Address</label>
                            <input type="text" id="qgAddress" class="form-control">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="qgSave">Save Guest</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const currency = "{{ $settings['currency'] }}";

function loadRooms() {
    const checkIn = document.getElementById('checkIn').value;
    const checkOut = document.getElementById('checkOut').value;
    const roomSelect = document.getElementById('roomSelect');
    const msg = document.getElementById('roomStatusMsg');
    if (!checkIn || !checkOut) return;
    roomSelect.disabled = true;
    roomSelect.innerHTML = '<option value="">Loading available rooms…</option>';
    fetch(`/api/available-rooms?check_in_date=${checkIn}&check_out_date=${checkOut}`)
        .then(r => r.json())
        .then(rooms => {
            roomSelect.innerHTML = '<option value="">Select a room…</option>';
            rooms.forEach(room => {
                const opt = document.createElement('option');
                opt.value = room.id;
                opt.textContent = `${room.room_number} · ${room.type} · ${currency}${room.price}/night`;
                opt.dataset.price = room.price;
                roomSelect.appendChild(opt);
            });
            roomSelect.disabled = false;
            msg.textContent = `${rooms.length} room(s) available for those dates.`;
            if (rooms.length === 0) msg.textContent = 'No rooms available for those dates.';
            updatePreview();
        })
        .catch(() => {
            roomSelect.innerHTML = '<option value="">Failed to load rooms</option>';
            msg.textContent = 'Could not load available rooms.';
        });
}

function updatePreview() {
    const checkIn = document.getElementById('checkIn').value;
    const checkOut = document.getElementById('checkOut').value;
    const room = document.getElementById('roomSelect').selectedOptions[0];
    const discount = parseFloat(document.getElementById('discount').value || 0);
    if (!checkIn || !checkOut || !room || !room.dataset.price) {
        document.getElementById('totalPreview').textContent = currency + '0.00';
        return;
    }
    const nights = Math.max(1, Math.round((new Date(checkOut) - new Date(checkIn)) / 86400000));
    const price = parseFloat(room.dataset.price);
    const total = Math.max(0, nights * price - discount);
    document.getElementById('totalPreview').textContent = currency + total.toFixed(2);
    document.getElementById('breakdown').textContent = `${nights} night(s) × ${currency}${price.toFixed(2)} - discount ${currency}${discount.toFixed(2)}`;
}

document.getElementById('checkIn').addEventListener('change', loadRooms);
document.getElementById('checkOut').addEventListener('change', loadRooms);
document.getElementById('discount').addEventListener('input', updatePreview);
document.getElementById('roomSelect').addEventListener('change', function () {
    const opt = this.selectedOptions[0];
    if (opt) {
        const m = opt.textContent.match(/([\d.]+)\/night/);
        if (m) opt.dataset.price = m[1];
    }
    updatePreview();
});

document.getElementById('qgSave').addEventListener('click', function () {
    const payload = {
        name: document.getElementById('qgName').value,
        phone: document.getElementById('qgPhone').value,
        email: document.getElementById('qgEmail').value,
        id_card: document.getElementById('qgIdCard').value,
        address: document.getElementById('qgAddress').value,
    };
    fetch('/guests', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(payload)
    })
    .then(r => r.json().then(d => ({ ok: r.ok, d })))
    .then(({ ok, d }) => {
        if (!ok) throw new Error(d.message || 'Failed to save guest');
        const select = document.getElementById('guestSelect');
        const opt = document.createElement('option');
        opt.value = d.id;
        opt.textContent = payload.name + (payload.phone ? ' · ' + payload.phone : '');
        select.appendChild(opt);
        select.value = d.id;
        bootstrap.Modal.getInstance(document.getElementById('quickGuestModal')).hide();
        document.getElementById('qgName').value = '';
        document.getElementById('qgPhone').value = '';
        document.getElementById('qgEmail').value = '';
        document.getElementById('qgIdCard').value = '';
        document.getElementById('qgAddress').value = '';
    })
    .catch(err => alert(err.message));
});

if (document.getElementById('checkIn').value && document.getElementById('checkOut').value) {
    loadRooms();
}
</script>
@endpush
