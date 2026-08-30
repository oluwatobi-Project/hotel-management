@extends('layouts.site')

@section('title', 'Book Your Stay — ' . ($settings['hotel_name'] ?? 'Grand Horizon Hotel'))

@section('content')
<section class="py-5 bg-navy text-white">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('site.home') }}">Home</a></li>
                <li class="breadcrumb-item active text-white">Book a Stay</li>
            </ol>
        </nav>
        <h1 class="font-display fw-bold mb-2">Reserve Your Stay</h1>
        <p class="text-white-50 mb-0">Choose your dates and we will show you what is available.</p>
    </div>
</section>

<section class="py-5" style="background:var(--cream)">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="bg-white rounded-4 shadow-sm p-4 p-lg-5">

                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-3 fs-5"></i>
                        <div>Reservations are <strong>held for 24 hours</strong> while you complete payment. If no payment (full or partial) is received within 24 hours, the reservation is automatically released.</div>
                    </div>

                    <form action="{{ route('site.booking.store') }}" method="POST" id="bookingForm">
                        @csrf
                        <input type="hidden" name="room_id" id="selectedRoomId" value="{{ old('room_id') }}">

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Check-in Date</label>
                                <input type="date" name="check_in_date" id="checkInDate" class="form-control" value="{{ old('check_in_date', date('Y-m-d', strtotime('+1 day'))) }}" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Check-out Date</label>
                                <input type="date" name="check_out_date" id="checkOutDate" class="form-control" value="{{ old('check_out_date', date('Y-m-d', strtotime('+3 days'))) }}" min="{{ date('Y-m-d', strtotime('+2 day')) }}" required>
                            </div>
                        </div>

                        <h6 class="fw-semibold mb-3">Available Rooms <span id="roomsCount" class="badge text-bg-success"></span></h6>
                        <div id="availableRooms" class="row g-3 mb-4">
                            <div class="col-12 text-center text-muted py-4">
                                <i class="bi bi-hourglass-split me-2"></i>Select your dates to see available rooms.
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-semibold mb-3">Guest Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1 555 000 0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">ID Card / Passport No.</label>
                                <input type="text" name="id_card" class="form-control" value="{{ old('id_card') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Special Requests</label>
                                <textarea name="notes" rows="3" class="form-control" placeholder="Early check-in, airport pickup, etc.">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <span class="text-muted small">Estimated total:</span>
                                <div class="fs-4 fw-bold" id="totalEstimate" style="color:var(--navy)">Select a room</div>
                            </div>
                            <button class="btn btn-gold btn-lg px-4" type="submit" disabled id="submitBtn">
                                <i class="bi bi-check-circle me-2"></i>Confirm Reservation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
const currency = @json($settings['currency'] ?? '$');
const pricePerNight = {};

function parseDate(d) { const [y,m,dd] = d.split('-').map(Number); return new Date(y, m-1, dd); }
function nights() {
    const a = parseDate(document.getElementById('checkInDate').value);
    const b = parseDate(document.getElementById('checkOutDate').value);
    if (isNaN(a) || isNaN(b) || b <= a) return 0;
    return Math.max(1, Math.round((b - a) / 86400000));
}

function loadRooms() {
    const ci = document.getElementById('checkInDate').value;
    const co = document.getElementById('checkOutDate').value;
    const container = document.getElementById('availableRooms');
    const count = document.getElementById('roomsCount');
    if (!ci || !co) return;

    fetch(`/api/site/available-rooms?check_in_date=${encodeURIComponent(ci)}&check_out_date=${encodeURIComponent(co)}`)
        .then(r => r.json())
        .then(rooms => {
            container.innerHTML = '';
            Object.keys(pricePerNight).forEach(k => delete pricePerNight[k]);

            if (!rooms.length) {
                container.innerHTML = '<div class="col-12 text-center text-muted py-4"><i class="bi bi-x-circle me-2"></i>No rooms are available for the selected dates. Try different dates.</div>';
                count.textContent = '';
                document.getElementById('selectedRoomId').value = '';
                document.getElementById('submitBtn').disabled = true;
                document.getElementById('totalEstimate').textContent = 'Select a room';
                return;
            }

            count.textContent = rooms.length + ' available';
            rooms.forEach(room => {
                pricePerNight[room.id] = room.price;
                const col = document.createElement('div');
                col.className = 'col-sm-6';
                col.innerHTML = `
                    <div class="border rounded-3 p-3 room-option" data-id="${room.id}" data-price="${room.price}" role="button"
                         style="cursor:pointer;transition:.15s">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">Room ${room.room_number}</div>
                                <div class="text-muted small">${room.type}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">${currency}${room.price.toFixed(2)}<span class="text-muted fw-normal small">/night</span></div>
                                <div class="small text-muted">${nights()} nights</div>
                            </div>
                        </div>
                    </div>`;
                col.querySelector('.room-option').addEventListener('click', () => selectRoom(col.querySelector('.room-option')));
                container.appendChild(col);
            });

            const prev = @json((int) old('room_id'));
            if (prev && document.querySelector(`.room-option[data-id="${prev}"]`)) {
                selectRoom(document.querySelector(`.room-option[data-id="${prev}"]`));
            }
        });
}

function selectRoom(el) {
    document.querySelectorAll('.room-option').forEach(o => {
        o.classList.remove('border-success', 'bg-success-subtle');
        o.style.borderColor = '#dee2e6';
    });
    el.classList.add('border-success', 'bg-success-subtle');
    el.style.borderColor = '#198754';
    document.getElementById('selectedRoomId').value = el.dataset.id;
    const total = (parseFloat(el.dataset.price) * nights()).toFixed(2);
    document.getElementById('totalEstimate').textContent = `${currency}${total}`;
    document.getElementById('submitBtn').disabled = false;
}

document.getElementById('checkInDate').addEventListener('change', function () {
    const min = new Date(parseDate(this.value));
    min.setDate(min.getDate() + 1);
    const co = document.getElementById('checkOutDate');
    if (co.value <= this.value) co.value = min.toISOString().slice(0, 10);
    co.min = min.toISOString().slice(0, 10);
    loadRooms();
});
document.getElementById('checkOutDate').addEventListener('change', loadRooms);

loadRooms();
</script>
@endpush
