@extends('layouts.app')

@section('title', 'Register Asset')
@section('subtitle', 'Add new government property record')

@section('content')
<form action="{{ route('supply.assets.store') }}" method="POST">
    @csrf
    <div class="grid-2">
        <div>
            <div class="card">
                <div class="card-header"><span>📋</span><span class="card-title">Asset Information</span></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Asset Code (auto-generated)</label>
                            <input class="form-control" value="NIA-RO9-AUTO" readonly style="background:var(--gray-50);font-family:monospace;font-weight:600;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Property Number (PAR / Tag No.)</label>
                            <input class="form-control" name="property_number" placeholder="e.g. PAR-2026-001">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asset Description *</label>
                        <input class="form-control" name="description" id="assetDescInput" placeholder="e.g. Backhoe Loader CAT 315, Year 2024" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Asset Account / Classification *</label>
                            <select class="form-control" name="account_id" required>
                                <option value="">Select account classification...</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->asset_accounts_id ?? $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Assigned Office / Station *</label>
                            <select class="form-control" name="office_id" required>
                                <option value="">Select station office...</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->office_id ?? $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Serial Number</label>
                            <input class="form-control" name="serial_number" placeholder="Manufacturer serial no.">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Brand / Model</label>
                            <input class="form-control" name="brand" placeholder="e.g. Caterpillar / CAT 315">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Acquisition Date</label>
                            <input class="form-control" name="acquisition_date" type="date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Acquisition Cost (₱)</label>
                            <input class="form-control" name="acquisition_cost" type="number" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fund Source / Fund Cluster</label>
                        <input class="form-control" name="fund_source" placeholder="e.g. GAA Fund 101, Foreign Assisted Project">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><span>📍</span><span class="card-title">Condition & Initial Custody</span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Physical Condition *</label>
                        <select class="form-control" name="condition" required>
                            <option value="A1">A1 — Serviceable (Excellent / Brand New / Good)</option>
                            <option value="A2">A2 — Serviceable (Fair condition)</option>
                            <option value="A3">A3 — Serviceable (Poor / Needs Minor Repair)</option>
                            <option value="R">R — Unserviceable (Recommended for Disposal / Scrap)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Remarks / Additional Notes</label>
                        <textarea class="form-control" name="remarks" rows="2" placeholder="Any relevant remarks, delivery notes, or specs..."></textarea>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:10px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">Register & Generate QR Label</button>
                        <a href="{{ route('supply.assets.index') }}" class="btn">Cancel</a>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><span>📱</span><span class="card-title">QR Label Preview</span></div>
                <div class="card-body" style="text-align:center;">
                    <div class="qr-card">
                        <div class="qr-asset-code" id="qrCodeLabel">NIA-RO9-AUTO</div>
                        <div class="qr-wrap" id="qrRender"></div>
                        <div class="qr-asset-name" id="qrNameLabel">New Asset</div>
                        <div style="font-size:10px;color:rgba(255,255,255,.5);">National Irrigation Administration — RO IX</div>
                    </div>
                    <div style="margin-top:16px;text-align:left;">
                        <div class="alert alert-info">
                            <span>ℹ️</span>
                            <div style="font-size:12px;">Upon successful registration, the unique QR code tag will be linked to this asset record and can be printed immediately for physical affixing.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('assetDescInput')?.addEventListener('input', function() {
    document.getElementById('qrNameLabel').textContent = this.value || 'New Asset';
});

// Initialize QR with placeholder
setTimeout(function() {
    const el = document.getElementById('qrRender');
    if (el && typeof QRCode !== 'undefined') {
        new QRCode(el, {
            text: 'NIA-RO9-PROPERTY|National Irrigation Administration RO IX',
            width: 120,
            height: 120,
            colorDark: '#0F2044',
            colorLight: '#ffffff'
        });
    }
}, 200);
</script>
@endsection
