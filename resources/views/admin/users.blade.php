@extends('layouts.app')

@section('title', 'User Management')
@section('subtitle', 'Manage administrative accounts, supply officers, and inspection roles')

@section('content')
<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <span>👥</span><span class="card-title">System Users & Accounts</span>
            <span style="margin-left:auto;font-size:12px;color:var(--gray-500);">{{ count($users) }} accounts</span>
        </div>
        <div class="card-body" style="padding:0 16px 16px;">
            @forelse($users as $user)
            @php $uid = $user->users_id ?? $user->id; @endphp
            <div class="trail-item">
                <div class="user-avatar" style="width:36px;height:36px;font-size:13px;">{{ substr($user->full_name ?? $user->username, 0, 2) }}</div>
                <div class="trail-content">
                    <div class="trail-action">{{ $user->full_name ?? 'Personnel Account' }}</div>
                    <div class="trail-meta">
                        Username: <strong>{{ $user->username }}</strong> · Role:
                        <span class="badge badge-inactive">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                        @if($user->is_active)
                            <span class="badge badge-active" style="margin-left:4px;">Active</span>
                        @else
                            <span class="badge badge-disposed" style="margin-left:4px;">Inactive</span>
                        @endif
                    </div>
                </div>
                <div style="display:flex;gap:4px;">
                    <button type="button" class="btn btn-sm btn-primary" onclick="editUser({{ json_encode($user) }})">Edit</button>
                    @if($user->username !== 'admin')
                    <form action="{{ route('admin.users.destroy', $uid) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deactivate or delete this user account?')">Delete</button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state"><div class="ei">👥</div><div>No user accounts found</div></div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span>➕</span><span class="card-title">Create System User</span></div>
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Link to Personnel Record *</label>
                    <select class="form-control" name="personnel_id" required>
                        <option value="">Select personnel member...</option>
                        @foreach($personnel as $p)
                            <option value="{{ $p->personnel_id ?? $p->id }}">{{ $p->full_name }} ({{ $p->position ?? 'Personnel' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input class="form-control" name="username" placeholder="e.g. supply_officer9" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input class="form-control" name="password" type="password" placeholder="Minimum 6 characters" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Access Role *</label>
                    <select class="form-control" name="role" required>
                        <option value="supply_officer">Supply Officer (Asset Manager)</option>
                        <option value="inspection_officer">Inspection / Audit Officer</option>
                        <option value="admin">System Administrator</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Create System User Account</button>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    const userId = user.users_id || user.id;
    const content = `
        <form action="{{ url('admin/users') }}/${userId}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Personnel / Name</label>
                <input class="form-control" value="${user.full_name || user.username}" readonly style="background:var(--gray-50);">
            </div>
            <div class="form-group">
                <label class="form-label">Username</label>
                <input class="form-control" value="${user.username}" readonly style="background:var(--gray-50);">
            </div>
            <div class="form-group">
                <label class="form-label">Access Role *</label>
                <select class="form-control" name="role" required>
                    <option value="supply_officer" ${user.role === 'supply_officer' || user.role === 'asset_manager' ? 'selected' : ''}>Supply Officer (Asset Manager)</option>
                    <option value="inspection_officer" ${user.role === 'inspection_officer' ? 'selected' : ''}>Inspection / Audit Officer</option>
                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>System Administrator</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Account Status</label>
                <select class="form-control" name="is_active">
                    <option value="1" ${user.is_active ? 'selected' : ''}>Active</option>
                    <option value="0" ${!user.is_active ? 'selected' : ''}>Inactive / Suspended</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;margin-top:14px;">
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">Save User Updates</button>
            </div>
        </form>
    `;
    showModal('Edit User — ' + user.username, content);
}
</script>
@endsection
