@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

@section('content')
<div id="roleAlert"></div>
<div class="alert alert-info d-flex align-items-center">
    <i class="bi bi-shield-check me-2"></i>
    <div>Assign a role to each staff member to grant a bundle of modules. Per-staff module overrides can then add extra modules on the <a href="{{ route('users.index') }}">Staff</a> page.</div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0" style="color:var(--navy)">{{ count($roles) }} Roles</h6>
        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#roleModal" onclick="resetRoleForm()">
            <i class="bi bi-plus-lg"></i> Add Role
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Modules</th>
                        <th>Staff</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $role->name }}</span>
                                @if($role->description)
                                    <div><small class="text-muted">{{ $role->description }}</small></div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse(collect($role->modules ?? []) as $module)
                                        <span class="badge text-bg-light">{{ $modules->get($module)['label'] ?? $module }}</span>
                                    @empty
                                        <span class="text-muted small">No modules</span>
                                    @endforelse
                                </div>
                            </td>
                            <td><span class="badge text-bg-info">{{ $role->users_count }}</span></td>
                            <td class="text-end">
                                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#roleModal"
                                        onclick="fillRoleForm({!! $role->toJson() !!})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" data-id="{{ $role->id }}" onclick="deleteRole(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No roles yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="roleForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalTitle">Add Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Role Name</label>
                        <input type="text" name="name" class="form-control" id="roleName" placeholder="e.g. Front Desk" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Description</label>
                        <input type="text" name="description" class="form-control" id="roleDescription" placeholder="Short summary of this role">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Modules this role can handle</label>
                        <div class="border rounded p-2" style="max-height:280px;overflow-y:auto">
                            @foreach($modules as $key => $definition)
                                <div class="form-check form-switch">
                                    <input class="form-check-input role-module" type="checkbox" name="modules[]" value="{{ $key }}" id="roleModule_{{ $key }}">
                                    <label class="form-check-label" for="roleModule_{{ $key }}">
                                        <i class="bi {{ $definition['icon'] }} me-1"></i>{{ $definition['label'] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="roleSpinner"></span>Save
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showRoleAlert(type, message) {
    const el = document.getElementById('roleAlert');
    el.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => { el.innerHTML = ''; }, 5000);
}

function resetRoleForm() {
    document.getElementById('roleForm').reset();
    document.getElementById('roleForm').removeAttribute('data-id');
    document.getElementById('roleModalTitle').textContent = 'Add Role';
}

function fillRoleForm(r) {
    resetRoleForm();
    document.getElementById('roleForm').setAttribute('data-id', r.id);
    document.getElementById('roleModalTitle').textContent = 'Edit ' + r.name;
    document.getElementById('roleName').value = r.name;
    document.getElementById('roleDescription').value = r.description || '';
    (r.modules || []).forEach(m => {
        const box = document.getElementById('roleModule_' + m);
        if (box) box.checked = true;
    });
}

document.getElementById('roleForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = this.getAttribute('data-id');
    const url = id ? '/roles/' + id : '/roles';
    const method = id ? 'PUT' : 'POST';
    const formData = new FormData(this);
    formData.set('_method', method);
    const btn = this.querySelector('button[type="submit"]');
    const spinner = document.getElementById('roleSpinner');
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
            bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
            showRoleAlert('success', data.message || 'Saved.');
            setTimeout(() => window.location.reload(), 600);
        } else {
            showRoleAlert('danger', data.message || 'Could not save. Select at least one module and check the form.');
        }
    })
    .catch(() => showRoleAlert('danger', 'Network error while saving.'))
    .finally(() => { btn.disabled = false; spinner.classList.add('d-none'); });
});

function deleteRole(btn) {
    if (!confirm('Delete this role?')) return;
    fetch('/roles/' + btn.dataset.id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new URLSearchParams({ _method: 'DELETE' })
    })
    .then(async res => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
    .then(({ ok, data }) => {
        showRoleAlert(ok ? 'success' : 'danger', data.message || (ok ? 'Deleted.' : 'Could not delete.'));
        if (ok) setTimeout(() => window.location.reload(), 600);
    })
    .catch(() => showRoleAlert('danger', 'Network error while deleting.'));
}
</script>
@endpush
