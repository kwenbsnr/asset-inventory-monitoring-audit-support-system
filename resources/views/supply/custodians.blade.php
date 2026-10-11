@extends('layouts.app')

@section('title', 'Custodian Management')
@section('subtitle', 'Manage government personnel and accountable property custodians')

@section('content')
<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <span>👤</span><span class="card-title">Personnel Registry</span>
            <span style="margin-left:auto;font-size:12px;color:var(--gray-500);">{{ count($personnel) }} records</span>
        </div>
        <div class="card-body" style="padding:0 16px 16px;max-height:650px;overflow-y:auto;">
            @forelse($personnel as $p)
            <div class="trail-item">
                <div class="user-avatar" style="width:36px;height:36px;font-size:13px;">{{ substr($p->full_name, 0, 2) }}</div>
                <div class="trail-content">
                    <div class="trail-action">{{ $p->full_name }}</div>
                    <div class="trail-meta">{{ $p->position ?? 'No Position' }} · {{ $p->office_name ?? 'Regional Office' }}</div>
                    <div class="trail-meta" style="color:var(--teal);margin-top:3px;">
                        <strong>{{ $custodianAssets[$p->personnel_id ?? $p->id] ?? 0 }}</strong> assets in active custody
                        @if(!$p->is_active)
                            <span class="badge badge-inactive" style="margin-left:6px;">Inactive</span>
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
        <div class="card-header"><span>➕</span><span class="card-title">Add New Custodian Personnel</span></div>
        <div class="card-body">
            <form action="{{ route('supply.custodians.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input class="form-control" name="full_name" placeholder="Last Name, First Name M.I." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Employee ID *</label>
                    <input class="form-control" name="employee_id" placeholder="e.g. NIA-RO9-2026-001" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Position Title</label>
                    <input class="form-control" name="position" placeholder="e.g. Senior Engineer / Supply Officer III">
                </div>
                <div class="form-group">
                    <label class="form-label">Designation / Role</label>
                    <input class="form-control" name="designation" placeholder="e.g. Property Custodian / Section Head">
                </div>
                <div class="form-group">
                    <label class="form-label">Assigned Office / Station</label>
                    <select class="form-control" name="office_id">
                        <option value="">Select station office...</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->office_id ?? $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px;">+ Save Custodian Record</button>
            </form>
        </div>
    </div>
</div>
@endsection
