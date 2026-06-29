@extends('layouts.app')

@section('title', 'Asset Details')
@section('subtitle', $asset->asset_code ?? 'Asset')

@section('content')
@if($asset)
<div class="card">
    <div class="card-header">
        <span>📦</span>
        <span class="card-title">{{ $asset->asset_code }}</span>
        <span style="margin-left:auto;">
            @if($asset->status == 'active')
                <span class="badge badge-active"><span class="dot"></span>Active</span>
            @elseif($asset->status == 'maintenance')
                <span class="badge badge-maintenance"><span class="dot"></span>Maintenance</span>
            @elseif($asset->status == 'transferred')
                <span class="badge badge-transferred"><span class="dot"></span>Transferred</span>
            @elseif($asset->status == 'for_disposal')
                <span class="badge badge-disposed"><span class="dot"></span>For Disposal</span>
            @elseif($asset->status == 'missing')
                <span class="badge badge-missing"><span class="dot"></span>Missing</span>
            @endif
        </span>
    </div>
    <div class="card-body">
        <div class="grid-2">
            <div>
                <div style="margin-bottom:12px;">
                    <div style="font-size:12px;color:var(--gray-500);">Description</div>
                    <div style="font-weight:500;">{{ $asset->description }}</div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div><div style="font-size:12px;color:var(--gray-500);">Category</div><div>{{ $asset->category_name ?? 'Uncategorized' }}</div></div>
                    <div><div style="font-size:12px;color:var(--gray-500);">Account Code</div><div>{{ $asset->account_code ?? 'N/A' }}</div></div>
                    <div><div style="font-size:12px;color:var(--gray-500);">Serial Number</div><div style="font-family:monospace;">{{ $asset->serial_number ?? 'N/A' }}</div></div>
                    <div><div style="font-size:12px;color:var(--gray-500);">Brand / Model</div><div>{{ $asset->brand ?? 'N/A' }} {{ $asset->model ?? '' }}</div></div>
                    <div><div style="font-size:12px;color:var(--gray-500);">Condition</div><div style="text-transform:capitalize;">{{ $asset->condition }}</div></div>
                    <div><div style="font-size:12px;color:var(--gray-500);">Acquisition Cost</div><div>₱{{ number_format($asset->acquisition_cost ?? 0, 2) }}</div></div>
                    <div><div style="font-size:12px;color:var(--gray-500);">Acquisition Date</div><div>{{ $asset->acquisition_date ?? 'N/A' }}</div></div>
                    <div><div style="font-size:12px;color:var(--gray-500);">Fund Source</div><div>{{ $asset->fund_source ?? 'N/A' }}</div></div>
                </div>
            </div>
            <div>
                <div style="margin-bottom:12px;">
                    <div style="font-size:12px;color:var(--gray-500);">Current Location</div>
                    <div style="font-weight:500;">{{ $asset->office_name ?? 'Unassigned' }}</div>
                </div>
                <div style="margin-bottom:12px;">
                    <div style="font-size:12px;color:var(--gray-500);">Custodian</div>
                    <div style="font-weight:500;">{{ $asset->custodian_name ?? 'Unassigned' }}</div>
                    @if($asset->par_number)
                    <div style="font-size:12px;color:var(--gray-500);">PAR: {{ $asset->par_number }}</div>
                    @endif
                </div>
                @if($asset->remarks)
                <div>
                    <div style="font-size:12px;color:var(--gray-500);">Remarks</div>
                    <div>{{ $asset->remarks }}</div>
                </div>
                @endif
                <div style="margin-top:12px;display:flex;gap:8px;">
                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-primary">✎ Edit</a>
                    <button class="btn" onclick="showQRModal('{{ $asset->id }}', '{{ $asset->asset_code }}', '{{ $asset->description }}')">📱 QR</button>
                    <a href="{{ route('assets.index') }}" class="btn">← Back</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><span>📍</span><span class="card-title">Location History</span></div>
        <div class="card-body" style="padding:0 16px;">
            @forelse($locations as $loc)
            <div class="trail-item">
                <div class="trail-dot trail-dot-update">📍</div>
                <div class="trail-content">
                    <div class="trail-action">{{ $loc->location_name }}</div>
                    <div class="trail-meta">{{ $loc->recorded_at }}</div>
                </div>
            </div>
            @empty
            <div class="empty-state"><div class="ei">📍</div><div>No location history</div></div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span>🔄</span><span class="card-title">Transfer History</span></div>
        <div class="card-body" style="padding:0 16px;">
            @forelse($transfers as $t)
            <div class="trail-item">
                <div class="trail-dot trail-dot-transfer">⇄</div>
                <div class="trail-content">
                    <div class="trail-action">{{ $t->transfer_number }}</div>
                    <div class="trail-meta">{{ $t->transfer_date }} · {{ ucfirst($t->status) }}</div>
                </div>
            </div>
            @empty
            <div class="empty-state"><div class="ei">🔄</div><div>No transfer history</div></div>
            @endforelse
        </div>
    </div>
</div>

<script>
function showQRModal(id, code, desc) {
    const content = `
        <div style="text-align:center;">
            <div class="qr-card" style="max-width:280px;margin:0 auto 16px;">
                <div class="qr-asset-code">${code}</div>
                <div class="qr-wrap" id="modalQR"></div>
                <div class="qr-asset-name">${desc}</div>
                <div style="font-size:10px;color:rgba(255,255,255,.5);">National Irrigation Administration — RO IX</div>
            </div>
            <p style="font-size:12px;color:var(--gray-500);margin-bottom:12px;">Print this QR label and affix it to the physical asset.</p>
            <button class="btn btn-primary" onclick="showToast('QR label sent to printer.')">🖨️ Print Label</button>
        </div>
    `;
    showModal('QR Label — ' + code, content);

    setTimeout(function() {
        const el = document.getElementById('modalQR');
        if (el && typeof QRCode !== 'undefined') {
            new QRCode(el, {
                text: code + '|' + desc + '|NIA RO IX',
                width: 120,
                height: 120,
                colorDark: '#0F2044',
                colorLight: '#ffffff'
            });
        }
    }, 100);
}
</script>
@else
<div class="alert alert-danger"><span>❌</span><div>Asset not found</div></div>
@endif
@endsection
