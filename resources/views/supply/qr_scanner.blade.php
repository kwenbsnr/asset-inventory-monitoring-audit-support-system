@extends('layouts.app')

@section('title', 'QR Code Scanner')
@section('subtitle', 'Rapid optical identification and physical asset audit verification')

@section('content')
<div class="grid-2">
    <div>
        <div class="card">
            <div class="card-header"><span>📷</span><span class="card-title">Barcode / QR Scanner Interface</span></div>
            <div class="card-body">
                <div class="scanner-area" onclick="simulateScan()">
                    <div class="scan-icon">📷</div>
                    <div style="font-weight:600;color:var(--gray-700);margin-bottom:4px;">Tap to Scan Physical Asset QR Code</div>
                    <div style="font-size:12px;color:var(--gray-400);">Simulates scanner reader to retrieve asset ledger records in real time</div>
                </div>
                <div style="margin-top:16px;">
                    <div style="font-size:12px;color:var(--gray-500);margin-bottom:8px;">— or query property barcode manually —</div>
                    <div style="display:flex;gap:8px;">
                        <input class="form-control" id="manualSearch" placeholder="Enter Asset code (e.g. AST-001) or serial number..." style="flex:1;" onkeydown="if(event.key==='Enter')manualSearch()">
                        <button class="btn btn-primary" onclick="manualSearch()">Search</button>
                    </div>
                    <div id="searchResults" style="margin-top:8px;"></div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span>🕐</span><span class="card-title">Recent Optical Scans</span></div>
            <div class="card-body" style="padding:0 16px;">
                @forelse($recentScans as $scan)
                <div class="trail-item">
                    <div class="trail-dot trail-dot-scan">📷</div>
                    <div class="trail-content">
                        <div class="trail-action">{{ $scan->asset_code ?? 'Asset' }} — {{ $scan->description ?? 'Property Item' }}</div>
                        <div class="trail-meta">Scanned by {{ $scan->scanner_name }} · {{ $scan->scanned_at }}</div>
                    </div>
                    <button class="btn btn-sm" onclick="viewAsset('{{ $scan->asset_id }}')">View</button>
                </div>
                @empty
                <div class="empty-state">
                    <div class="ei">📭</div>
                    <div>No optical scan events logged yet today</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div id="scanResultPanel">
        <div class="card" style="border:2px dashed var(--gray-200);min-height:300px;display:flex;align-items:center;justify-content:center;">
            <div class="empty-state">
                <div class="ei">📱</div>
                <div style="font-weight:600;color:var(--gray-700);font-size:15px;">Awaiting QR Tag Scan</div>
                <div style="font-size:12px;margin-top:6px;max-width:320px;">Tap the scanner area or search by asset code above to inspect full specifications, accountability records, and verification status.</div>
            </div>
        </div>
    </div>
</div>

<script>
function simulateScan() {
    // Pick an existing asset from the system
    const sampleCodes = ['AST-001', 'AST-002', 'AST-003', 'AST-004', 'AST-005'];
    const pick = sampleCodes[Math.floor(Math.random() * sampleCodes.length)];
    scanQR(pick);
}

function scanQR(qrData) {
    fetch('{{ route("supply.qr.scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qr_data: qrData, scan_purpose: 'verification' })
    })
    .then(response => {
        if (!response.ok) throw new Error('Asset not found');
        return response.json();
    })
    .then(data => {
        if (data.error) {
            showToast('Asset not found');
            return;
        }
        displayAssetResult(data);
        showToast('Asset record retrieved successfully!');
    })
    .catch(error => {
        showToast('QR Scan error or asset not recognized');
    });
}

function displayAssetResult(data) {
    const asset = data.asset;
    const panel = document.getElementById('scanResultPanel');
    const assetId = asset.asset_id || asset.id;

    const statusBadge = {
        active: '<span class="badge badge-active"><span class="dot"></span>Active</span>',
        maintenance: '<span class="badge badge-maintenance"><span class="dot"></span>Maintenance</span>',
        transferred: '<span class="badge badge-transferred"><span class="dot"></span>Transferred</span>',
        for_disposal: '<span class="badge badge-disposed"><span class="dot"></span>For Disposal</span>',
        pending_disposal: '<span class="badge badge-disposed"><span class="dot"></span>Pending Disposal</span>',
        missing: '<span class="badge badge-missing"><span class="dot"></span>Missing</span>',
        disposed: '<span class="badge badge-disposed"><span class="dot"></span>Disposed</span>'
    }[asset.status] || `<span class="badge badge-inactive"><span class="dot"></span>${asset.status}</span>`;

    panel.innerHTML = `
        <div class="card" style="border:2px solid var(--teal);">
            <div class="card-header">
                <span>📋</span>
                <span class="card-title">Verified Asset Ledger Record</span>
                <span style="margin-left:auto;">${statusBadge}</span>
            </div>
            <div class="card-body">
                <div style="margin-bottom:14px;">
                    <div style="font-size:12px;color:var(--gray-500);">Asset Code / Identifier</div>
                    <div style="font-size:20px;font-weight:700;font-family:monospace;color:var(--navy);">${asset.asset_code}</div>
                    <div style="font-size:14px;color:var(--gray-800);margin-top:2px;">${asset.description}</div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 16px;">
                    <div><div style="font-size:11px;color:var(--gray-500);">Account / Category</div><div style="font-weight:500;">${asset.category_name || 'General Property'}</div></div>
                    <div><div style="font-size:11px;color:var(--gray-500);">Serial Number</div><div style="font-weight:500;font-family:monospace;">${asset.serial_number || 'N/A'}</div></div>
                    <div><div style="font-size:11px;color:var(--gray-500);">Station / Location</div><div style="font-weight:500;">${asset.office_name || 'Regional Office'}</div></div>
                    <div><div style="font-size:11px;color:var(--gray-500);">Accountable Custodian</div><div style="font-weight:500;">${asset.custodian_name || 'Unassigned'}</div></div>
                    <div><div style="font-size:11px;color:var(--gray-500);">Physical Condition</div><div style="font-weight:600;">${asset.condition || 'A1'}</div></div>
                    <div><div style="font-size:11px;color:var(--gray-500);">Acquisition Cost</div><div style="font-weight:600;">₱${Number(asset.acquisition_cost || 0).toLocaleString(undefined, {minimumFractionDigits:2})}</div></div>
                </div>
                <div style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap;border-top:1px solid var(--gray-100);padding-top:12px;">
                    <button class="btn btn-primary btn-sm" onclick="showToast('Verification record confirmed in system audit trail')">✓ Physical Verification Confirmed</button>
                    <a href="{{ route('supply.transfers.index') }}" class="btn btn-sm">⇄ Transfer Property</a>
                    <a href="{{ url('supply/assets') }}/${assetId}" class="btn btn-sm">📋 Full Details & History</a>
                </div>
            </div>
        </div>
    `;
}

function manualSearch() {
    const query = document.getElementById('manualSearch').value.trim();
    if (!query) {
        showToast('Please enter an asset code or description to search');
        return;
    }

    fetch('{{ route("supply.qr.search") }}?query=' + encodeURIComponent(query))
    .then(response => response.json())
    .then(results => {
        const container = document.getElementById('searchResults');
        if (!results || results.length === 0) {
            container.innerHTML = '<div class="alert alert-warning" style="margin-top:8px;"><span>🔍</span><div>No matching property records found.</div></div>';
            return;
        }
        let html = '<div class="alert alert-info" style="margin-top:8px;"><span>📋</span><div>Found ' + results.length + ' matching asset(s):</div></div>';
        html += '<div style="max-height:220px;overflow-y:auto;border:1px solid var(--gray-200);border-radius:var(--radius);">';
        results.forEach(asset => {
            const assetId = asset.asset_id || asset.id;
            html += `
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-bottom:1px solid var(--gray-100);cursor:pointer;background:#fff;" onclick="viewAsset('${asset.asset_code}')">
                    <div>
                        <strong style="font-family:monospace;color:var(--navy);">${asset.asset_code}</strong>
                        <span style="font-size:13px;margin-left:8px;color:var(--gray-700);">${asset.description}</span>
                    </div>
                    <button class="btn btn-sm btn-primary" type="button">Select</button>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    })
    .catch(() => {
        showToast('Search request encountered an error');
    });
}

function viewAsset(identifier) {
    scanQR(identifier);
}
</script>
@endsection
