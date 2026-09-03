@extends('layouts.app')

@section('title', 'Room Types')
@section('page-title', 'Room Types')

@section('content')
<div id="typeAlert"></div>
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0" style="color:var(--navy)">{{ count($roomTypes) }} Room Types</h6>
        <div>
            <a href="{{ route('amenities.index') }}" class="btn btn-outline-gold btn-sm me-1">
                <i class="bi bi-stars"></i> Manage Amenities
            </a>
            <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#typeModal" onclick="resetTypeForm()">
                <i class="bi bi-plus-lg"></i> Add Room Type
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Price / Night</th>
                        <th>Capacity</th>
                        <th>Beds</th>
                        <th>Rooms</th>
                        <th>Amenities</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="typeTableBody">
                    @forelse($roomTypes as $type)
                        <tr data-id="{{ $type->id }}">
                            <td class="fw-semibold">{{ $type->name }}</td>
                            <td class="money">{{ $settings['currency'] }}{{ number_format($type->price, 2) }}</td>
                            <td>{{ $type->capacity }}</td>
                            <td>{{ $type->bed_count }}</td>
                            <td><span class="badge text-bg-light">{{ $type->rooms_count }}</span></td>
                            <td>
                                @if($type->amenityItems->isNotEmpty())
                                    @foreach($type->amenityItems->take(4) as $am)
                                        <span class="badge text-bg-light me-1"><i class="bi {{ $am->icon ?? 'bi-check' }} me-1"></i>{{ $am->name }}</span>
                                    @endforeach
                                @else
                                    <small class="text-muted">{{ Str::limit($type->amenities, 40) }}</small>
                                @endif
                            </td>                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#typeModal"
                                        onclick="fillTypeForm({!! $type->toJson() !!})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" data-id="{{ $type->id }}" onclick="deleteType(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="noTypesRow"><td colspan="7" class="text-center text-muted py-4">No room types yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="typeModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="typeForm" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="_method" value="POST" id="typeMethod">
            <div class="modal-header">
                <h5 class="modal-title" id="typeModalTitle">Add Room Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control" id="typeName" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Price / Night</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control" id="typePrice" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Capacity (guests)</label>
                        <input type="number" min="1" name="capacity" class="form-control" id="typeCapacity" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Bed Count</label>
                        <input type="number" min="1" name="bed_count" class="form-control" id="typeBeds" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Amenities</label>
                        <input type="text" name="amenities" class="form-control" id="typeAmenities" placeholder="WiFi, TV, Mini Bar…">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Amenity Items</label>
                        @php($amenities = \App\Models\Amenity::orderBy('name')->get())
                        @if($amenities->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($amenities as $am)
                                    <label class="form-check form-check-inline">
                                        <input class="form-check-input amenity-check" type="checkbox" name="amenity_ids[]" value="{{ $am->id }}">
                                        <span class="form-check-label small"><i class="bi {{ $am->icon ?? 'bi-check' }} me-1"></i>{{ $am->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <small class="text-muted">No amenities defined yet. <a href="{{ route('amenities.index') }}">Create some</a> first.</small>
                        @endif
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" id="typeDescription"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="typeSubmitBtn">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="typeSpinner"></span>Save
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showTypeAlert(type, message) {
    const el = document.getElementById('typeAlert');
    el.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => { el.innerHTML = ''; }, 5000);
}

function resetTypeForm() {
    document.getElementById('typeForm').reset();
    document.getElementById('typeForm').removeAttribute('data-id');
    document.getElementById('typeMethod').value = 'POST';
    document.getElementById('typeModalTitle').textContent = 'Add Room Type';
    document.getElementById('typeName').value = '';
}

function fillTypeForm(t) {
    resetTypeForm();
    document.getElementById('typeForm').setAttribute('data-id', t.id);
    document.getElementById('typeMethod').value = 'PUT';
    document.getElementById('typeModalTitle').textContent = 'Edit ' + t.name;
    document.getElementById('typeName').value = t.name;
    document.getElementById('typePrice').value = t.price;
    document.getElementById('typeCapacity').value = t.capacity;
    document.getElementById('typeBeds').value = t.bed_count;
    document.getElementById('typeAmenities').value = t.amenities || '';
    document.getElementById('typeDescription').value = t.description || '';
    const amenityIds = (t.amenity_items || []).map(a => a.id);
    document.querySelectorAll('.amenity-check').forEach(c => c.checked = amenityIds.includes(parseInt(c.value, 10)));
}

document.getElementById('typeForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const id = this.getAttribute('data-id');
    const url = id ? '/room-types/' + id : '/room-types';
    const method = id ? 'PUT' : 'POST';

    const formData = new FormData(this);
    formData.set('_method', method);

    const btn = document.getElementById('typeSubmitBtn');
    const spinner = document.getElementById('typeSpinner');
    btn.disabled = true;
    spinner.classList.remove('d-none');

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(async res => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
    .then(({ ok, data }) => {
        if (ok) {
            bootstrap.Modal.getInstance(document.getElementById('typeModal')).hide();
            showTypeAlert('success', data.message || 'Saved.');
            setTimeout(() => window.location.reload(), 600);
        } else {
            const msg = data.message || 'Could not save. Please check the form.';
            showTypeAlert('danger', msg);
        }
    })
    .catch(() => showTypeAlert('danger', 'Network error while saving.'))
    .finally(() => {
        btn.disabled = false;
        spinner.classList.add('d-none');
    });
});

function deleteType(btn) {
    if (!confirm('Delete this room type?')) return;
    const id = btn.dataset.id;
    fetch('/room-types/' + id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new URLSearchParams({ _method: 'DELETE' })
    })
    .then(async res => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
    .then(({ ok, data }) => {
        showTypeAlert(ok ? 'success' : 'danger', data.message || (ok ? 'Deleted.' : 'Could not delete.'));
        if (ok) setTimeout(() => window.location.reload(), 600);
    })
    .catch(() => showTypeAlert('danger', 'Network error while deleting.'));
}
</script>
@endpush
