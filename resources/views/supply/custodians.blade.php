@extends('layouts.app')

@section('title', 'Custodian Management')
@section('subtitle', 'Manage asset custodians')

@section('content')
<div class="grid-2">
    <div class="card">
        <div class="card-header"><span>👤</span><span class="card-title">Personnel Registry</span></div>
        <div class="card-body" style="padding:0 16px 16px;">
            @forelse($personnel as $p)
            <div class="trail-item">
                <div class="user-avatar" style="width:36px;height:36px;font-size:13px;">{{ substr($p->full_name, 0, 2) }}</div>
                <div class="trail-content">
                    <div class="trail-action">{{ $p->full_name }}</div>
                    <div class="trail-meta">{{ $p->position ?? 'No position' }} · {{ $p->office_name ?? 'Unassigned' }}</div>
                    <div class="trail-meta" style="color:var(--teal);">
                        {{ $custodianAssets[$p->id] ?? 0 }} assets assigned
                        @if(!$p->is_active)
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state"><div class="ei">👤</div><div>No personnel records found</div></div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span>📋</span><span class="card-title">Add / Update Custodian</span></div>
        <div class="card-body">
            <form action="{{ route('custodians.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input class="form-control" name="full_name" placeholder="Last name, First name M.I." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Employee ID *</label>
                    <input class="form-control" name="employee_id" placeholder="e.g. NIA-2024-001" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input class="form-control" name="position" placeholder="e.g. Supply Officer III">
                </div>
                <div class="form-group">
                    <label class="form-label">Designation</label>
                    <input class="form-control" name="designation" placeholder="e.g. Property Custodian">
                </div>
                <div class="form-group">
                    <label class="form-label">Office</label>
                    <select class="form-control" name="office_id">
                        <option value="">Select office</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Add Custodian</button>
            </form>
        </div>
    </div>
</div>
@endsection
