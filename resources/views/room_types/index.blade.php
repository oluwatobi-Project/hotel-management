@extends('layouts.app')

@section('title', 'Room Types')
@section('page-title', 'Room Types')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0" style="color:var(--navy)">{{ count($roomTypes) }} Room Types</h6>
        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#typeModal" onclick="resetTypeForm()">
            <i class="bi bi-plus-lg"></i> Add Room Type
        </button>
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
                <tbody>
                    @forelse($roomTypes as $type)
                        <tr>
                            <td class="fw-semibold">{{ $type->name }}</td>
                            <td class="money">{{ $settings['currency'] }}{{ number_format($type->price, 2) }}</td>
                            <td>{{ $type->capacity }}</td>
                            <td>{{ $type->bed_count }}</td>
                            <td><span class="badge text-bg-light">{{ $type->rooms_count }}</span></td>
                            <td><small class="text-muted">{{ Str::limit($type->amenities, 40) }}</small></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#typeModal"
                                        onclick="fillTypeForm({!! $type->toJson() !!})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('room-types.destroy', $type->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this room type?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No room types yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="typeModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="typeForm" method="POST" action="{{ route('room-types.store') }}" class="modal-content">
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
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" id="typeDescription"></textarea>
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
function resetTypeForm() {
    document.getElementById('typeForm').reset();
    document.getElementById('typeForm').action = "{{ route('room-types.store') }}";
    document.getElementById('typeMethod').value = 'POST';
    document.getElementById('typeModalTitle').textContent = 'Add Room Type';
    document.getElementById('typeName').value = '';
}
function fillTypeForm(t) {
    resetTypeForm();
    document.getElementById('typeForm').action = "/room-types/" + t.id;
    document.getElementById('typeMethod').value = 'PUT';
    document.getElementById('typeModalTitle').textContent = 'Edit ' + t.name;
    document.getElementById('typeName').value = t.name;
    document.getElementById('typePrice').value = t.price;
    document.getElementById('typeCapacity').value = t.capacity;
    document.getElementById('typeBeds').value = t.bed_count;
    document.getElementById('typeAmenities').value = t.amenities || '';
    document.getElementById('typeDescription').value = t.description || '';
}
</script>
@endpush
