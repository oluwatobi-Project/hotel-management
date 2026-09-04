@extends('layouts.app')

@section('title', 'Staff')
@section('page-title', 'Staff Accounts')

@section('content')
<div id="userAlert"></div>
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0" style="color:var(--navy)">{{ count($users) }} Accounts</h6>
        <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetUserForm()">
            <i class="bi bi-plus-lg"></i> Add User
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Assigned Modules</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <span class="fw-semibold">{{ $user->name }} @if($user->id === auth()->id())<span class="badge text-bg-light ms-1">You</span>@endif</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->isAdmin() ? 'text-bg-warning' : 'text-bg-secondary' }}">{{ ucfirst($user->role) }}</span>
                                @if(!$user->isAdmin() && $user->accessRole)
                                    <div><small class="text-muted">{{ $user->accessRole->name }}</small></div>
                                @endif
                            </td>
                            <td>
                                @if($user->isAdmin())
                                    <span class="text-muted small">All modules</span>
                                @else
                                    <div class="d-flex flex-wrap gap-1" style="max-width:260px">
                                        @forelse($user->visibleModules() as $module)
                                            <span class="badge text-bg-light">{{ config("rbac.modules.$module.label", $module) }}</span>
                                        @empty
                                            <span class="text-muted small">Dashboard only</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>
                            <td class="text-muted">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                @if($user->id !== auth()->id())
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal"
                                            onclick="fillUserForm({!! $user->load('accessRole')->toJson() !!})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" data-id="{{ $user->id }}" onclick="deleteUser(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="userForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Full Name *</label>
                        <input type="text" name="name" class="form-control" id="userName" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Email *</label>
                        <input type="email" name="email" class="form-control" id="userEmail" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" id="userPhone">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Account Type</label>
                        <select name="role" class="form-select" id="userRole">
                            <option value="staff">Staff</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <div class="form-text">Admins can access every module. Staff are limited to their assigned role and overrides below.</div>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" id="userPassword" placeholder="Min 8 chars" minlength="8">
                    </div>

                    <div class="col-12">
                        <hr class="my-1">
                        <span class="small text-muted fw-semibold">Module Access</span>
                    </div>

                    <div class="col-6" id="roleSelectCol">
                        <label class="form-label small fw-semibold">Assigned Role</label>
                        <select name="role_id" class="form-select" id="userRoleId">
                            <option value="">— No role (Dashboard only) —</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">The role's modules are granted automatically.</div>
                    </div>

                    <div class="col-6" id="extraModulesCol">
                        <label class="form-label small fw-semibold">Extra Module Overrides</label>
                        <div class="border rounded p-2" style="max-height:150px;overflow-y:auto">
                            @foreach($modules as $key => $definition)
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="extra_modules[]" value="{{ $key }}" id="userExtra_{{ $key }}">
                                    <label class="form-check-label" for="userExtra_{{ $key }}">
                                        <i class="bi {{ $definition['icon'] }} me-1"></i>{{ $definition['label'] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text">Extra modules added on top of the role, for this staff member only.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="userSpinner"></span>Save
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showUserAlert(type, message) {
    const el = document.getElementById('userAlert');
    el.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => { el.innerHTML = ''; }, 5000);
}

function toggleRoleAssignmentVisibility() {
    const isAdmin = document.getElementById('userRole').value === 'admin';
    document.getElementById('roleSelectCol').classList.toggle('d-none', isAdmin);
    document.getElementById('extraModulesCol').classList.toggle('d-none', isAdmin);
}

function resetUserForm() {
    document.getElementById('userForm').reset();
    document.getElementById('userForm').removeAttribute('data-id');
    document.getElementById('userModalTitle').textContent = 'Add User';
    document.getElementById('userPassword').required = true;
    document.getElementById('userPassword').placeholder = 'Min 8 chars';
    document.getElementById('userRole').value = 'staff';
    toggleRoleAssignmentVisibility();
}

function fillUserForm(u) {
    resetUserForm();
    document.getElementById('userForm').setAttribute('data-id', u.id);
    document.getElementById('userModalTitle').textContent = 'Edit ' + u.name;
    document.getElementById('userName').value = u.name;
    document.getElementById('userEmail').value = u.email;
    document.getElementById('userPhone').value = u.phone || '';
    document.getElementById('userRole').value = u.role;
    document.getElementById('userRoleId').value = u.role_id || '';
    toggleRoleAssignmentVisibility();

    const extra = u.extra_modules || [];
    document.querySelectorAll('input[name="extra_modules[]"]').forEach(box => {
        box.checked = extra.includes(box.value);
    });

    document.getElementById('userPassword').required = false;
}

document.getElementById('userRole').addEventListener('change', toggleRoleAssignmentVisibility);

document.getElementById('userForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = this.getAttribute('data-id');
    const url = id ? '/users/' + id : '/users';
    const method = id ? 'PUT' : 'POST';
    const formData = new FormData(this);
    formData.set('_method', method);
    if (!formData.get('password')) formData.delete('password');
    const btn = this.querySelector('button[type="submit"]');
    const spinner = document.getElementById('userSpinner');
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
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
            showUserAlert('success', data.message || 'Saved.');
            setTimeout(() => window.location.reload(), 600);
        } else {
            showUserAlert('danger', data.message || 'Could not save. Please check the form.');
        }
    })
    .catch(() => showUserAlert('danger', 'Network error while saving.'))
    .finally(() => { btn.disabled = false; spinner.classList.add('d-none'); });
});

function deleteUser(btn) {
    if (!confirm('Delete this user account?')) return;
    fetch('/users/' + btn.dataset.id, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new URLSearchParams({ _method: 'DELETE' })
    })
    .then(async res => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
    .then(({ ok, data }) => {
        showUserAlert(ok ? 'success' : 'danger', data.message || (ok ? 'Deleted.' : 'Could not delete.'));
        if (ok) setTimeout(() => window.location.reload(), 600);
    })
    .catch(() => showUserAlert('danger', 'Network error while deleting.'));
}
</script>
@endpush
