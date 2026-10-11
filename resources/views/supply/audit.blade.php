@extends('layouts.app')

@section('title', 'Audit Trail')
@section('subtitle', 'Full chronological system security and accountability log')

@section('content')
<div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
    <div class="search-bar" style="max-width:320px;">
        <span>🔍</span>
        <input type="text" id="auditSearch" placeholder="Search by asset, user, action..." onkeyup="filterAudit()">
    </div>
    <select class="form-control" id="actionFilter" style="width:180px;" onchange="filterAudit()">
        <option value="">All Actions</option>
        @foreach($actionTypes as $type)
            <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
        @endforeach
    </select>
    <a href="{{ route('supply.audit.export') }}" class="btn btn-sm btn-navy" style="margin-left:auto;">⬇ Export Audit Log (CSV)</a>
</div>

<div class="card">
    <div class="card-header">
        <span>📋</span><span class="card-title">System Audit Trail Records</span>
        <span style="margin-left:auto;font-size:12px;color:var(--gray-500);" id="auditCount">{{ count($auditLogs) }} entries</span>
    </div>
    <div class="card-body" id="auditLogBody" style="padding:8px 16px;">
        @forelse($auditLogs as $log)
        <div class="trail-item" data-action="{{ $log->action_type }}">
            <div class="trail-dot
                @if(stripos($log->action_type, 'reg') !== false) trail-dot-reg
                @elseif(stripos($log->action_type, 'transfer') !== false) trail-dot-transfer
                @elseif(stripos($log->action_type, 'scan') !== false) trail-dot-scan
                @elseif(stripos($log->action_type, 'update') !== false) trail-dot-update
                @else trail-dot-verify @endif">
                @if(stripos($log->action_type, 'reg') !== false) ➕
                @elseif(stripos($log->action_type, 'transfer') !== false) ⇄
                @elseif(stripos($log->action_type, 'scan') !== false) 📷
                @elseif(stripos($log->action_type, 'update') !== false) ✎
                @else ✓ @endif
            </div>
            <div class="trail-content">
                <div class="trail-action">{{ $log->module ?? $log->action_type }} <span class="badge badge-inactive" style="margin-left:6px;font-size:10px;">{{ strtoupper($log->action_type) }}</span></div>
                <div class="trail-meta">
                    @if($log->asset_code)
                        <strong>{{ $log->asset_code }}</strong> ({{ $log->description ?? 'Property' }}) ·
                    @endif
                    User: <strong>{{ $log->user_name }}</strong> ·
                    Timestamp: {{ $log->performed_at }}
                    @if(isset($log->ip_address) && $log->ip_address)
                        · IP: {{ $log->ip_address }}
                    @endif
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
    const search = document.getElementById('auditSearch').value.toLowerCase().trim();
    const action = document.getElementById('actionFilter').value.trim();

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
