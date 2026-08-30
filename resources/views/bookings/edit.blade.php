@extends('layouts.app')

@section('title', 'Edit Booking ' . $booking->booking_ref)
@section('page-title', 'Edit ' . $booking->booking_ref)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-pencil-square me-2"></i>Edit Booking</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('bookings.update', $booking->id) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Guest *</label>
                            <select name="guest_id" class="form-select" required>
                                @foreach($guests as $guest)
                                    <option value="{{ $guest->id }}" @selected($booking->guest_id === $guest->id)>
                                        {{ $guest->name }}{{ $guest->phone ? ' · ' . $guest->phone : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Current Amount</label>
                            <input type="text" class="form-control" value="{{ $settings['currency'] }}{{ number_format($booking->total_amount, 2) }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Check-In Date *</label>
                            <input type="date" name="check_in_date" id="checkIn" class="form-control" value="{{ $booking->check_in_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Check-Out Date *</label>
                            <input type="date" name="check_out_date" id="checkOut" class="form-control" value="{{ $booking->check_out_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Room *</label>
                            <select name="room_id" id="roomSelect" class="form-select" required>
                                <option value="{{ $booking->room_id }}" selected data-price="{{ $booking->room->roomType->price }}">
                                    {{ $booking->room->room_number }} · {{ $booking->room->roomType->name }} · Current
                                </option>
                            </select>
                            <div class="form-text" id="roomStatusMsg">Loading available rooms…</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Discount ({{ $settings['currency'] }})</label>
                            <input type="number" step="0.01" min="0" name="discount" class="form-control" id="discount" value="{{ $booking->discount }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $booking->notes }}</textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total:</span>
                            <span class="fs-5 fw-bold money" id="totalPreview">{{ $settings['currency'] }}{{ number_format($booking->total_amount, 2) }}</span>
                            <small class="text-muted d-block" id="breakdown"></small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-gold px-4"><i class="bi bi-check-lg"></i> Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currency = "{{ $settings['currency'] }}";
const excludeBooking = {{ $booking->id }};

function loadRooms() {
    const checkIn = document.getElementById('checkIn').value;
    const checkOut = document.getElementById('checkOut').value;
    const roomSelect = document.getElementById('roomSelect');
    const msg = document.getElementById('roomStatusMsg');
    if (!checkIn || !checkOut) return;
    const currentId = roomSelect.value;
    fetch(`/api/available-rooms?check_in_date=${checkIn}&check_out_date=${checkOut}&exclude_booking=${excludeBooking}`)
        .then(r => r.json())
        .then(rooms => {
            const currentOption = roomSelect.querySelector('option[value="' + currentId + '"]');
            roomSelect.innerHTML = '';
            if (currentOption) roomSelect.appendChild(currentOption);
            rooms.forEach(room => {
                if (String(room.id) === String(currentId)) return;
                const opt = document.createElement('option');
                opt.value = room.id;
                opt.textContent = `${room.room_number} · ${room.type} · ${currency}${room.price}/night`;
                opt.dataset.price = room.price;
                roomSelect.appendChild(opt);
            });
            msg.textContent = `${rooms.length} other room(s) available for those dates.`;
            updatePreview();
        })
        .catch(() => msg.textContent = 'Could not load available rooms.');
}

function updatePreview() {
    const checkIn = document.getElementById('checkIn').value;
    const checkOut = document.getElementById('checkOut').value;
    const opt = document.getElementById('roomSelect').selectedOptions[0];
    const discount = parseFloat(document.getElementById('discount').value || 0);
    if (!checkIn || !checkOut || !opt) return;
    const nights = Math.max(1, Math.round((new Date(checkOut) - new Date(checkIn)) / 86400000));
    const price = parseFloat(opt.dataset.price || 0);
    const total = Math.max(0, nights * price - discount);
    document.getElementById('totalPreview').textContent = currency + total.toFixed(2);
    document.getElementById('breakdown').textContent = `${nights} night(s) × ${currency}${price.toFixed(2)} - discount ${currency}${discount.toFixed(2)}`;
}

document.getElementById('checkIn').addEventListener('change', loadRooms);
document.getElementById('checkOut').addEventListener('change', loadRooms);
document.getElementById('discount').addEventListener('input', updatePreview);
document.getElementById('roomSelect').addEventListener('change', updatePreview);

loadRooms();
</script>
@endpush
