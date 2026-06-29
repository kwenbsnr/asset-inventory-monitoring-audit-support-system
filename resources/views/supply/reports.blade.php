@extends('layouts.app')

@section('title', 'Audit Trail')
@section('subtitle', 'Full transaction history')

@section('content')
<div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
    <div class="search-bar" style="max-width:300px;">
        <span>🔍</span>
        <input type="text" id="auditSearch" placeholder="Search by asset or action..." onkeyup="filterAudit()">
    </div>
    <select class="form-control" id="actionFilter" style="width:150px;" onchange="filterAudit()">
        <option value="">All Actions</option>
        @foreach($actionTypes as $type)
            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
        @endforeach
    </select>
    <a href="{{ route('audit.export') }}" class="btn btn-sm">⬇ Export Log</a>
</div>

<div class="card">
    <div class="card-header">
        <span>📋</span><span class="card-title">System Audit Trail</span>
        <span style="margin-left:auto;font-size:12px;color:var(--gray-500);" id="auditCount">{{ count($auditLogs) }} entries</span>
    </div>
    <div class="card-body" id="auditLogBody">
        @forelse($auditLogs as $log)
        <div class="trail-item" data-action="{{ $log->action_type }}">
            <div class="trail-dot
                @if($log->action_type == 'registration') trail-dot-reg
                @elseif($log->action_type == 'transfer') trail-dot-transfer
                @elseif($log->action_type == 'qr_scan') trail-dot-scan
                @elseif($log->action_type == 'update') trail-dot-update
                @else trail-dot-verify @endif">
                @if($log->action_type == 'registration') ➕
                @elseif($log->action_type == 'transfer') ⇄
                @elseif($log->action_type == 'qr_scan') 📷
                @elseif($log->action_type == 'update') ✎
                @else ✓ @endif
            </div>
            <div class="trail-content">
                <div class="trail-action">{{ $log->module }}</div>
                <div class="trail-meta">
                    {{ $log->asset_code ?? 'N/A' }} ·
                    {{ $log->user_name }} ·
                    {{ $log->performed_at }}
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="ei">📭</div>
            <div>No audit log entries found</div>
        </div>
        @endforelse
    </div>
</div>

<script>
function filterAudit() {
    const search = document.getElementById('auditSearch').value.toLowerCase();
    const action = document.getElementById('actionFilter').value;

    const items = document.querySelectorAll('#auditLogBody .trail-item');
    let visible = 0;

    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        const itemAction = item.dataset.action || '';

        let show = true;
        if (search && !text.includes(search)) show = false;
        if (action && itemAction !== action) show = false;

        item.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('auditCount').textContent = visible + ' entries';
}
</script>
@endsection
