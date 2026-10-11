@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'National Irrigation Administration — Regional Office IX')

@section('content')
<div class="grid-4">
    <div class="metric-card accent-teal">
        <div class="metric-label">Total Assets Registered</div>
        <div class="metric-value">{{ $totalAssets }}</div>
        <div class="metric-sub metric-up">↑ Recorded in registry</div>
    </div>
    <div class="metric-card accent-green">
        <div class="metric-label">Active / Deployed Assets</div>
        <div class="metric-value">{{ $activeAssets }}</div>
        <div class="metric-sub" style="color:var(--gray-500);">{{ $totalAssets > 0 ? round(($activeAssets/$totalAssets)*100, 1) : 0 }}% of total inventory</div>
    </div>
    <div class="metric-card accent-amber">
        <div class="metric-label">Pending Verification</div>
        <div class="metric-value">{{ $pendingVerification }}</div>
        <div class="metric-sub metric-down">Needs physical inspection</div>
    </div>
    <div class="metric-card accent-red">
        <div class="metric-label">Discrepancies Found</div>
        <div class="metric-value">{{ $discrepancies }}</div>
        <div class="metric-sub metric-up">Requires audit reconciliation</div>
    </div>
</div>

<div class="grid-2" style="margin-top:16px;">
    <div class="card">
        <div class="card-header">
            <span>📊</span><span class="card-title">Recent Activity</span>
            <a href="{{ route('supply.audit.index') }}" style="margin-left:auto;font-size:12px;color:var(--teal);text-decoration:none;">View all →</a>
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
                    <div class="trail-action">{{ $activity->module ?? $activity->action_type }}</div>
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
        <div class="card-header">
            <span>⚠️</span><span class="card-title">Assets Requiring Attention</span>
            <a href="{{ route('supply.assets.index') }}" style="margin-left:auto;font-size:12px;color:var(--teal);text-decoration:none;">View all →</a>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Asset Code</th><th>Description</th><th>Status / Condition</th><th>Location</th></tr></thead>
                    <tbody>
                        @forelse($attentionAssets as $asset)
                        <tr>
                            <td class="td-code"><a href="{{ route('supply.assets.show', $asset->asset_id ?? $asset->id) }}" style="color:var(--navy);text-decoration:none;">{{ $asset->asset_code }}</a></td>
                            <td>{{ $asset->description }}</td>
                            <td>
                                @if($asset->status != 'active')
                                    <span class="badge badge-maintenance"><span class="dot"></span> {{ ucfirst(str_replace('_', ' ', $asset->status)) }}</span>
                                @elseif(!$asset->custodian_name)
                                    <span class="badge badge-missing"><span class="dot"></span> Unassigned</span>
                                @else
                                    <span class="badge badge-inactive"><span class="dot"></span> {{ $asset->condition }}</span>
                                @endif
                            </td>
                            <td>{{ $asset->location_name ?? 'Regional Office' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center" style="padding:20px;color:var(--gray-500);">All assets are currently active and accounted for</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
