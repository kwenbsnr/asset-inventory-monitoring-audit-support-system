@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Overview')

@section('content')
<div class="alert alert-warning" id="pendingAlert">
    <span>⚠️</span>
    <div><strong>3 pending transfer requests</strong> require acknowledgment. <a href="{{ route('transfers.index') }}" style="color:var(--amber);text-decoration:underline;">Review now</a></div>
</div>

<div class="grid-4">
    <div class="metric-card accent-teal">
        <div class="metric-label">Total Assets Registered</div>
        <div class="metric-value">{{ $totalAssets }}</div>
        <div class="metric-sub metric-up">↑ Active inventory</div>
    </div>
    <div class="metric-card accent-green">
        <div class="metric-label">Active / Deployed</div>
        <div class="metric-value">{{ $activeAssets }}</div>
        <div class="metric-sub" style="color:var(--gray-500);">{{ $totalAssets > 0 ? round(($activeAssets/$totalAssets)*100, 1) : 0 }}% of total inventory</div>
    </div>
    <div class="metric-card accent-amber">
        <div class="metric-label">Pending Verification</div>
        <div class="metric-value">{{ $pendingVerification }}</div>
        <div class="metric-sub metric-down">↑ Needs attention</div>
    </div>
    <div class="metric-card accent-red">
        <div class="metric-label">Discrepancies Found</div>
        <div class="metric-value">{{ $discrepancies }}</div>
        <div class="metric-sub metric-up">↓ Requires investigation</div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <span>📊</span><span class="card-title">Recent Activity</span>
        </div>
        <div class="card-body" style="padding:8px 16px;">
            @forelse($recentActivity as $activity)
            <div class="trail-item">
                <div class="trail-dot
                    @if($activity->action_type == 'registration') trail-dot-reg
                    @elseif($activity->action_type == 'transfer') trail-dot-transfer
                    @elseif($activity->action_type == 'qr_scan') trail-dot-scan
                    @elseif($activity->action_type == 'update') trail-dot-update
                    @else trail-dot-verify @endif">
                    @if($activity->action_type == 'registration') ➕
                    @elseif($activity->action_type == 'transfer') ⇄
                    @elseif($activity->action_type == 'qr_scan') 📷
                    @elseif($activity->action_type == 'update') ✎
                    @else ✓ @endif
                </div>
                <div class="trail-content">
                    <div class="trail-action">{{ $activity->module }}</div>
                    <div class="trail-meta">{{ $activity->asset_code ?? 'N/A' }} · {{ $activity->user_name }} · {{ $activity->performed_at }}</div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="ei">📭</div>
                <div>No recent activity</div>
            </div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span>⚠️</span><span class="card-title">Assets Requiring Attention</span></div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Asset Code</th><th>Description</th><th>Issue</th><th>Location</th></tr></thead>
                    <tbody>
                        @forelse($attentionAssets as $asset)
                        <tr>
                            <td class="td-code">{{ $asset->asset_code }}</td>
                            <td>{{ $asset->description }}</td>
                            <td>
                                @if($asset->status != 'active')
                                    <span class="badge badge-maintenance"><span class="dot"></span> {{ ucfirst($asset->status) }}</span>
                                @elseif(!$asset->custodian_name)
                                    <span class="badge badge-missing"><span class="dot"></span> No Custodian</span>
                                @endif
                            </td>
                            <td>{{ $asset->location_name ?? 'Unknown' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center" style="padding:20px;color:var(--gray-500);">No assets need attention</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
