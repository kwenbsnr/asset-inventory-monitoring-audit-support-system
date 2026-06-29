@extends('layouts.app')

@section('title', 'QR Scanner')
@section('subtitle', 'Identify assets by QR code')

@section('content')
<div class="grid-2">
    <div>
        <div class="card">
            <div class="card-header"><span>📷</span><span class="card-title">QR Code Scanner</span></div>
            <div class="card-body">
                <div class="scanner-area" onclick="simulateScan()">
                    <div class="scan-icon">📷</div>
                    <div style="font-weight:600;color:var(--gray-700);margin-bottom:4px;">Tap to scan QR code</div>
                    <div style="font-size:12px;color:var(--gray-400);">Point camera at asset QR label to retrieve record instantly</div>
                </div>
                <div style="margin-top:14px;">
                    <div style="font-size:12px;color:var(--gray-500);margin-bottom:8px;">— or search manually —</div>
                    <div style="display:flex;gap:8px;">
                        <input class="form-control" id="manualSearch" placeholder="Asset code, serial no., or description..." style="flex:1;">
                        <button class="btn btn-primary" onclick="manualSearch()">Search</button>
                    </div>
                    <div id="searchResults" style="margin-top:8px;"></div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span>🕐</span><span class="card-title">Recent Scans</span></div>
            <div class="card-body" style="padding:0 16px;">
                @forelse($recentScans as $scan)
                <div class="trail-item">
                    <div class="trail-dot trail-dot-scan">📷</div>
                    <div class="trail-content">
                        <div class="trail-action">{{ $scan->asset_code }} — {{ $scan->description }}</div>
                        <div class="trail-meta">Scanned by {{ $scan->scanner_name }} · {{ $scan->scanned_at }}</div>
                    </div>
                    <button class="btn btn-sm" onclick="viewAsset('{{ $scan->asset_id }}')">View</button>
                </div>
                @empty
                <div class="empty-state">
                    <div class="ei">📭</div>
                    <div>No recent scans</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div id="scanResultPanel">
        <div class="card" style="border:2px dashed var(--gray-200);">
            <div class="empty-state">
                <div class="ei">📱</div>
                <div style="font-weight:500;color:var(--gray-600);">No asset scanned yet</div>
                <div style="font-size:12px;margin-top:4px;">Scan a QR code or search for an asset to view its complete record here.</div>
            </div>
        </div>
    </div>
</div>

<script>
const assetCodes = @json($recentScans->pluck('asset_code')->toArray());

function simulateScan() {
    // In a real app, this would use the camera API
    // For demo, we'll pick a random asset
    const codes = ['NIA-RO9-0047', 'NIA-RO9-0112', 'NIA-RO9-0031', 'NIA-RO9-0251'];
    const pick = codes[Math.floor(Math.random() * codes.length)];
    scanQR(pick);
}

function scanQR(qrData) {
    fetch('{{ route("qr.scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qr_data: qrData, scan_purpose: 'verification' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            showToast('Asset not found');
            return;
        }
        displayAssetResult(data);
        showToast('Asset record retrieved');
    })
    .catch(error => {
        showToast('Error scanning QR code');
    });
}

function displayAssetResult(data) {
    const asset = data.asset;
    const panel = document.getElementById('scanResultPanel');

    const statusBadge = {
        active: '<span class="badge badge-active"><span class="dot"></span>Active</span>',
        maintenance: '<span class="badge badge-maintenance"><span class="dot"></span>Maintenance</span>',
        transferred: '<span class="badge badge-transferred"><span class="dot"></span>Transferred</span>',
        for_disposal: '<span class="badge badge-disposed"><span class="dot"></span>For Disposal</span>',
        missing: '<span class="badge badge-missing"><span class="dot"></span>Missing</span>'
    }[asset.status] || `<span class="badge badge-inactive"><span class="dot"></span>${asset.status}</span>`;

    panel.innerHTML = `
        <div class="card" style="border:2px solid var(--teal);">
            <div class="card-header">
                <span>📋</span>
                <span class="card-title">Asset Record</span>
                <span style="margin-left:auto;">${statusBadge}</span>
            </div>
            <div class="card-body">
                <div style="margin-bottom:12px;">
                    <div style="font-size:13px;color:var(--gray-500);">Asset Code</div>
                    <div style="font-size:18px;font-weight:700;font-family:monospace;color:var(--navy);">${asset.asset_code}</div>
                    <div style="font-size:14px;color:var(--gray-700);">${asset.description}</div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;">
                    <div><div style="font-size:11px;color:var(--gray-500);">Category</div><div style="font-weight:500;">${asset.category_name || 'Uncategorized'}</div></div>
                    <div><div style="font-size:11px;color:var(--gray-500);">Serial Number</div><div style="font-weight:500;font-family:monospace;">${asset.serial_number || 'N/A'}</div></div>
                    <div><div style="font-size:11px;color:var(--gray-500);">Location</div><div style="font-weight:500;">${asset.office_name || 'Unassigned'}</div></div>
                    <div><div style="font-size:11px;color:var(--gray-500);">Custodian</div><div style="font-weight:500;">${asset.custodian_name || 'Unassigned'}</div></div>
                    <div><div style="font-size:11px;color:var(--gray-500);">Condition</div><div style="font-weight:500;text-transform:capitalize;">${asset.condition}</div></div>
                    <div><div style="font-size:11px;color:var(--gray-500);">Acquisition Cost</div><div style="font-weight:500;">₱${Number(asset.acquisition_cost || 0).toLocaleString()}</div></div>
                </div>
                <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;">
                    <button class="btn btn-primary btn-sm" onclick="showToast('Verification recorded in audit trail')">✓ Mark as Verified</button>
                    <a href="{{ route('transfers.index') }}" class="btn btn-sm">⇄ Transfer</a>
                    <a href="{{ route('assets.show', '') }}/${asset.id}" class="btn btn-sm">📋 Full Details</a>
                </div>
            </div>
        </div>
    `;
}

function manualSearch() {
    const query = document.getElementById('manualSearch').value.trim();
    if (!query) {
        showToast('Please enter a search term');
        return;
    }

    fetch('{{ route("qr.search") }}?query=' + encodeURIComponent(query))
    .then(response => response.json())
    .then(results => {
        const container = document.getElementById('searchResults');
        if (results.length === 0) {
            container.innerHTML = '<div class="alert alert-warning" style="margin-top:8px;"><span>🔍</span><div>No assets found matching your search.</div></div>';
            return;
        }
        let html = '<div class="alert alert-info" style="margin-top:8px;"><span>📋</span><div>Found ' + results.length + ' assets:</div></div>';
        html += '<div style="max-height:200px;overflow-y:auto;">';
        results.forEach(asset => {
            html += `
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px;border-bottom:1px solid var(--gray-100);cursor:pointer;" onclick="viewAsset(${asset.id})">
                    <div>
                        <strong style="font-family:monospace;">${asset.asset_code}</strong>
                        <span style="font-size:13px;margin-left:8px;">${asset.description}</span>
                    </div>
                    <button class="btn btn-sm btn-primary">Select</button>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    });
}

function viewAsset(id) {
    fetch('{{ route("qr.scan") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qr_data: 'NIA-RO9-' + String(id).padStart(4, '0'), scan_purpose: 'verification' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            // Fallback: Try to fetch by ID directly
            window.location.href = '{{ route("assets.show", "") }}/' + id;
            return;
        }
        displayAssetResult(data);
    });
}

function showToast(msg) {
    const t = document.getElementById('toast');
    document.getElementById('toast-msg').textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}
</script>
@endsection
