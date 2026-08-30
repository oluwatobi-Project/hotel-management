@extends('layouts.app')

@section('title', 'Staff')
@section('page-title', 'Staff Accounts')

@section('content')
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
                        <th>Phone</th>
                        <th>Role</th>
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
                            <td>{{ $user->phone ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $user->isAdmin() ? 'text-bg-warning' : 'text-bg-secondary' }}">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td class="text-muted">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                @if($user->id !== auth()->id())
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="fillUserForm({!! $user->toJson() !!})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user account?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                    </form>
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
    <div class="modal-dialog">
        <form id="userForm" method="POST" action="{{ route('users.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="_method" value="POST" id="userMethod">
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
                        <label class="form-label small fw-semibold">Role</label>
                        <select name="role" class="form-select" id="userRole">
                            <option value="staff">Staff</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" id="userPassword" placeholder="Min 8 chars" minlength="8">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-gold">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetUserForm() {
    document.getElementById('userForm').reset();
    document.getElementById('userForm').action = "{{ route('users.store') }}";
    document.getElementById('userMethod').value = 'POST';
    document.getElementById('userModalTitle').textContent = 'Add User';
    document.getElementById('userPassword').required = true;
}
function fillUserForm(u) {
    resetUserForm();
    document.getElementById('userForm').action = "/users/" + u.id;
    document.getElementById('userMethod').value = 'PUT';
    document.getElementById('userModalTitle').textContent = 'Edit ' + u.name;
    document.getElementById('userName').value = u.name;
    document.getElementById('userEmail').value = u.email;
    document.getElementById('userPhone').value = u.phone || '';
    document.getElementById('userRole').value = u.role;
    document.getElementById('userPassword').required = false;
}
</script>
@endpush
