@extends('layouts.app')

@section('title', 'Restaurant Menu')
@section('page-title', 'Restaurant Menu')

@section('content')
<div id="menuAlert"></div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        <a href="{{ route('restaurant.menu') }}" class="btn {{ request()->routeIs('restaurant.menu') ? 'btn-gold' : 'btn-outline-gold' }} btn-sm">Menu Items</a>
        <a href="{{ route('restaurant.orders.index') }}" class="btn {{ request()->routeIs('restaurant.orders.*') ? 'btn-gold' : 'btn-outline-gold' }} btn-sm">Orders</a>
    </div>
    <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#menuItemModal" onclick="resetMenuItemForm()">
        <i class="bi bi-plus-lg"></i> Add Menu Item
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menuItems as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:8px">
                                    @else
                                        <div class="avatar"><i class="bi bi-cup-hot"></i></div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $item->name }}</div>
                                        <small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge text-bg-light">{{ $item->category }}</span></td>
                            <td class="money">{{ $settings['currency'] }}{{ number_format($item->price, 2) }}</td>
                            <td>
                                @if($item->is_available)
                                    <span class="badge badge-status text-bg-success">Available</span>
                                @else
                                    <span class="badge badge-status text-bg-secondary">Unavailable</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form action="{{ route('restaurant.menu.toggle', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-outline-secondary btn-sm" title="Toggle availability">
                                        <i class="bi {{ $item->is_available ? 'bi-pause' : 'bi-play' }}"></i>
                                    </button>
                                </form>
                                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#menuItemModal"
                                        onclick="fillMenuItemForm({!! $item->toJson() !!})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" data-id="{{ $item->id }}" onclick="deleteMenuItem(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No menu items yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="menuItemModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="menuItemForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="menuItemModalTitle">Add Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-8">
                        <label class="form-label small fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control" id="miName" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-semibold">Price</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control" id="miPrice" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Category</label>
                        <input type="text" name="category" class="form-control" id="miCategory" list="menuCategories" placeholder="e.g. Breakfast" required>
                        <datalist id="menuCategories">
                            @foreach($categories as $category)
                                <option value="{{ $category }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Image URL</label>
                        <input type="url" name="image_url" class="form-control" id="miImage" placeholder="https://...">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" id="miDescription"></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="is_available" value="1" class="form-check-input" id="miAvailable" checked>
                            <label class="form-check-label small" for="miAvailable">Available for ordering</label>
                        </div>
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
function showMenuAlert(type, message) {
    const el = document.getElementById('menuAlert');
    el.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => { el.innerHTML = ''; }, 5000);
}

function resetMenuItemForm() {
    document.getElementById('menuItemForm').reset();
    document.getElementById('menuItemForm').removeAttribute('data-id');
    document.getElementById('menuItemModalTitle').textContent = 'Add Menu Item';
    document.getElementById('miAvailable').checked = true;
}

function fillMenuItemForm(i) {
    resetMenuItemForm();
    document.getElementById('menuItemForm').setAttribute('data-id', i.id);
    document.getElementById('menuItemModalTitle').textContent = 'Edit ' + i.name;
    document.getElementById('miName').value = i.name;
    document.getElementById('miPrice').value = i.price;
    document.getElementById('miCategory').value = i.category;
    document.getElementById('miImage').value = i.image_url || '';
    document.getElementById('miDescription').value = i.description || '';
    document.getElementById('miAvailable').checked = !!i.is_available;
}

document.getElementById('menuItemForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = this.getAttribute('data-id');
    const url = id ? '/restaurant/menu/' + id : '/restaurant/menu';
    const method = id ? 'PUT' : 'POST';
    const formData = new FormData(this);
    formData.set('_method', method);
    if (!formData.get('is_available')) formData.set('is_available', '0');

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(async res => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
    .then(({ ok, data }) => {
        if (ok) {
            bootstrap.Modal.getInstance(document.getElementById('menuItemModal')).hide();
            showMenuAlert('success', data.message || 'Saved.');
            setTimeout(() => window.location.reload(), 600);
        } else {
            showMenuAlert('danger', data.message || 'Could not save. Please check the form.');
        }
    })
    .catch(() => showMenuAlert('danger', 'Network error while saving.'))
    .finally(() => { this.querySelector('button[type="submit"]').disabled = false; });
});

function deleteMenuItem(btn) {
    if (!confirm('Delete this menu item?')) return;
    fetch('/restaurant/menu/' + btn.dataset.id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new URLSearchParams({ _method: 'DELETE' })
    })
    .then(async res => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
    .then(({ ok, data }) => {
        showMenuAlert(ok ? 'success' : 'danger', data.message || (ok ? 'Deleted.' : 'Could not delete.'));
        if (ok) setTimeout(() => window.location.reload(), 600);
    })
    .catch(() => showMenuAlert('danger', 'Network error while deleting.'));
}
</script>
@endpush
