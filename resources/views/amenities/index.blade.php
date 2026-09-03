@extends('layouts.app')

@section('title', 'Amenities')
@section('page-title', 'Room Amenities')

@section('content')
<div id="amenityAlert"></div>
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0" style="color:var(--navy)">{{ count($amenities) }} Amenities</h6>
        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#amenityModal" onclick="resetAmenityForm()">
            <i class="bi bi-plus-lg"></i> Add Amenity
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Room Types</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($amenities as $amenity)
                        <tr>
                            <td><i class="bi {{ $amenity->icon ?? 'bi-check' }} fs-5" style="color:var(--gold)"></i></td>
                            <td class="fw-semibold">{{ $amenity->name }}</td>
                            <td><small class="text-muted">{{ Str::limit($amenity->description, 60) }}</small></td>
                            <td><span class="badge text-bg-light">{{ $amenity->room_types_count }}</span></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#amenityModal"
                                        onclick="fillAmenityForm({!! $amenity->toJson() !!})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" data-id="{{ $amenity->id }}" onclick="deleteAmenity(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No amenities yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="amenityModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="amenityForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="amenityModalTitle">Add Amenity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-4">
                        <label class="form-label small fw-semibold">Bootstrap Icon</label>
                        <input type="text" name="icon" class="form-control" id="amenityIcon" placeholder="bi-wifi">
                    </div>
                    <div class="col-8">
                        <label class="form-label small fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control" id="amenityName" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" id="amenityDescription"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="amenitySpinner"></span>Save
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showAmenityAlert(type, message) {
    const el = document.getElementById('amenityAlert');
    el.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => { el.innerHTML = ''; }, 5000);
}

function resetAmenityForm() {
    document.getElementById('amenityForm').reset();
    document.getElementById('amenityForm').removeAttribute('data-id');
    document.getElementById('amenityModalTitle').textContent = 'Add Amenity';
}

function fillAmenityForm(a) {
    resetAmenityForm();
    document.getElementById('amenityForm').setAttribute('data-id', a.id);
    document.getElementById('amenityModalTitle').textContent = 'Edit ' + a.name;
    document.getElementById('amenityName').value = a.name;
    document.getElementById('amenityIcon').value = a.icon || '';
    document.getElementById('amenityDescription').value = a.description || '';
}

document.getElementById('amenityForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = this.getAttribute('data-id');
    const url = id ? '/amenities/' + id : '/amenities';
    const method = id ? 'PUT' : 'POST';
    const formData = new FormData(this);
    formData.set('_method', method);
    const btn = this.querySelector('button[type="submit"]');
    const spinner = document.getElementById('amenitySpinner');
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
            bootstrap.Modal.getInstance(document.getElementById('amenityModal')).hide();
            showAmenityAlert('success', data.message || 'Saved.');
            setTimeout(() => window.location.reload(), 600);
        } else {
            showAmenityAlert('danger', data.message || 'Could not save. Please check the form.');
        }
    })
    .catch(() => showAmenityAlert('danger', 'Network error while saving.'))
    .finally(() => { btn.disabled = false; spinner.classList.add('d-none'); });
});

function deleteAmenity(btn) {
    if (!confirm('Delete this amenity?')) return;
    fetch('/amenities/' + btn.dataset.id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new URLSearchParams({ _method: 'DELETE' })
    })
    .then(async res => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
    .then(({ ok, data }) => {
        showAmenityAlert(ok ? 'success' : 'danger', data.message || (ok ? 'Deleted.' : 'Could not delete.'));
        if (ok) setTimeout(() => window.location.reload(), 600);
    })
    .catch(() => showAmenityAlert('danger', 'Network error while deleting.'));
}
</script>
@endpush
