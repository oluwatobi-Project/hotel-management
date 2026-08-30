@extends('layouts.app')

@section('title', 'Guests')
@section('page-title', 'Guests')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <form class="d-flex gap-2" method="GET">
            <input type="text" name="search" class="form-control form-control-sm" style="width:260px" placeholder="Search name, email, phone, ID…" value="{{ request('search') }}">
            @if(request('search'))
                <a href="{{ route('guests.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </form>
        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#guestModal" onclick="resetGuestForm()">
            <i class="bi bi-plus-lg"></i> Add Guest
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Phone</th>
                        <th>ID Card</th>
                        <th>Email</th>
                        <th>Bookings</th>
                        <th>Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guests as $guest)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar">{{ strtoupper(substr($guest->name, 0, 1)) }}</span>
                                    <span class="fw-semibold">{{ $guest->name }}</span>
                                </div>
                            </td>
                            <td>{{ $guest->phone ?? '—' }}</td>
                            <td>{{ $guest->id_card ?? '—' }}</td>
                            <td>{{ $guest->email ?? '—' }}</td>
                            <td><span class="badge text-bg-light">{{ $guest->bookings_count }}</span></td>
                            <td class="text-muted">{{ $guest->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#guestModal" onclick="fillGuestForm({!! $guest->toJson() !!})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('guests.destroy', $guest->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete guest {{ $guest->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No guests found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $guests->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="guestModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="guestForm" method="POST" action="{{ route('guests.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="_method" value="POST" id="guestMethod">
            <div class="modal-header">
                <h5 class="modal-title" id="guestModalTitle">Add Guest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Full Name *</label>
                        <input type="text" name="name" class="form-control" id="guestName" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" id="guestPhone" placeholder="+1 555 000 1234">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" id="guestEmail">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">ID Card / Passport</label>
                        <input type="text" name="id_card" class="form-control" id="guestIdCard">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Nationality</label>
                        <input type="text" name="nationality" class="form-control" id="guestNationality">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control" id="guestAddress">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" id="guestNotes"></textarea>
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
function resetGuestForm() {
    document.getElementById('guestForm').reset();
    document.getElementById('guestForm').action = "{{ route('guests.store') }}";
    document.getElementById('guestMethod').value = 'POST';
    document.getElementById('guestModalTitle').textContent = 'Add Guest';
}
function fillGuestForm(g) {
    resetGuestForm();
    document.getElementById('guestForm').action = "/guests/" + g.id;
    document.getElementById('guestMethod').value = 'PUT';
    document.getElementById('guestModalTitle').textContent = 'Edit ' + g.name;
    document.getElementById('guestName').value = g.name;
    document.getElementById('guestPhone').value = g.phone || '';
    document.getElementById('guestEmail').value = g.email || '';
    document.getElementById('guestIdCard').value = g.id_card || '';
    document.getElementById('guestNationality').value = g.nationality || '';
    document.getElementById('guestAddress').value = g.address || '';
    document.getElementById('guestNotes').value = g.notes || '';
}
</script>
@endpush
