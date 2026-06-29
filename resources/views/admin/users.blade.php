@extends('layouts.app')

@section('title', 'User Management')
@section('subtitle', 'Manage system users')

@section('content')
<div class="grid-2">
    <div class="card">
        <div class="card-header"><span>👥</span><span class="card-title">System Users</span></div>
        <div class="card-body" style="padding:0 16px 16px;">
            @forelse($users as $user)
            <div class="trail-item">
                <div class="user-avatar" style="width:36px;height:36px;font-size:13px;">{{ substr($user->full_name, 0, 2) }}</div>
                <div class="trail-content">
                    <div class="trail-action">{{ $user->full_name }}</div>
                    <div class="trail-meta">
                        Username: {{ $user->username }} · Role: {{ ucfirst($user->role) }}
                        @if($user->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </div>
                </div>
                <div>
                    <button class="btn btn-sm btn-primary" onclick="editUser({{ json_encode($user) }})">Edit</button>
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-state"><div class="ei">👥</div><div>No users found</div></div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span>➕</span><span class="card-title">Add New User</span></div>
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Personnel *</label>
                    <select class="form-control" name="personnel_id" required>
                        <option value="">Select personnel...</option>
                        @foreach($personnel as $p)
                            <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->position ?? 'No position' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input class="form-control" name="username" placeholder="e.g. jdoe" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input class="form-control" name="password" type="password" placeholder="Minimum 6 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select class="form-control" name="role" required>
                        <option value="supply_officer">Supply Officer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Create User</button>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    const content = `
        <form action="{{ route('admin.users.update', '') }}/${user.id}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">User</label>
                <input class="form-control" value="${user.full_name}" readonly style="background:var(--gray-50);">
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select class="form-control" name="role">
                    <option value="supply_officer" ${user.role === 'supply_officer' ? 'selected' : ''}>Supply Officer</option>
                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select class="form-control" name="is_active">
                    <option value="1" ${user.is_active ? 'selected' : ''}>Active</option>
                    <option value="0" ${!user.is_active ? 'selected' : ''}>Inactive</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;margin-top:12px;">
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">Update User</button>
            </div>
        </form>
    `;
    showModal('Edit User — ' + user.username, content);
}
</script>
@endsection
