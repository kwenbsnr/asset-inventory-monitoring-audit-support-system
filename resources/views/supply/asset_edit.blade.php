@extends('layouts.app')

@section('title', 'Edit Asset')
@section('subtitle', 'Update property details for ' . ($asset->asset_code ?? ''))

@section('content')
<form action="{{ route('supply.assets.update', $asset->asset_id ?? $asset->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="grid-2">
        <div>
            <div class="card">
                <div class="card-header"><span>📋</span><span class="card-title">Asset Information</span></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Asset Code</label>
                            <input class="form-control" value="{{ $asset->asset_code }}" readonly style="background:var(--gray-50);font-family:monospace;font-weight:600;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status *</label>
                            <select class="form-control" name="status" required>
                                <option value="active" {{ $asset->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="maintenance" {{ $asset->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="transferred" {{ $asset->status == 'transferred' ? 'selected' : '' }}>Transferred</option>
                                <option value="for_disposal" {{ in_array($asset->status, ['for_disposal','pending_disposal']) ? 'selected' : '' }}>For Disposal</option>
                                <option value="missing" {{ $asset->status == 'missing' ? 'selected' : '' }}>Missing</option>
                                <option value="disposed" {{ $asset->status == 'disposed' ? 'selected' : '' }}>Disposed</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asset Description *</label>
                        <input class="form-control" name="description" value="{{ $asset->description }}" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Asset Account / Classification</label>
                            <select class="form-control" name="account_id">
                                <option value="">Select account classification...</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->asset_accounts_id ?? $acc->id }}" {{ ($asset->asset_accounts_id ?? $asset->account_id) == ($acc->asset_accounts_id ?? $acc->id) ? 'selected' : '' }}>{{ $acc->account_code }} - {{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Station Office</label>
                            <select class="form-control" name="office_id">
                                <option value="">Select station office...</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->office_id ?? $office->id }}" {{ ($asset->office_id ?? '') == ($office->office_id ?? $office->id) ? 'selected' : '' }}>{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Serial Number</label>
                            <input class="form-control" name="serial_number" value="{{ $asset->serial_number }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Brand / Model</label>
                            <input class="form-control" name="brand" value="{{ $asset->brand }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Acquisition Date</label>
                            <input class="form-control" name="acquisition_date" type="date" value="{{ $asset->acquisition_date }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Acquisition Cost (₱)</label>
                            <input class="form-control" name="acquisition_cost" type="number" step="0.01" value="{{ $asset->acquisition_cost }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fund Source / Cluster</label>
                        <input class="form-control" name="fund_source" value="{{ $asset->fund_cluster ?? $asset->fund_source }}">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><span>📍</span><span class="card-title">Condition & Remarks</span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Physical Condition *</label>
                        <select class="form-control" name="condition" required>
                            <option value="A1" {{ in_array($asset->condition, ['A1','good']) ? 'selected' : '' }}>A1 — Serviceable (Good / Excellent)</option>
                            <option value="A2" {{ in_array($asset->condition, ['A2','fair']) ? 'selected' : '' }}>A2 — Serviceable (Fair condition)</option>
                            <option value="A3" {{ in_array($asset->condition, ['A3','poor','for_repair']) ? 'selected' : '' }}>A3 — Serviceable (Poor / Needs Repair)</option>
                            <option value="R" {{ in_array($asset->condition, ['R','unserviceable']) ? 'selected' : '' }}>R — Unserviceable (Disposal / Scrap)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2">{{ $asset->remarks }}</textarea>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:12px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">Save Changes</button>
                        <a href="{{ route('supply.assets.show', $asset->asset_id ?? $asset->id) }}" class="btn" style="flex:1;text-align:center;">Cancel</a>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><span>📱</span><span class="card-title">QR Barcode Tag</span></div>
                <div class="card-body" style="text-align:center;">
                    <div class="qr-card">
                        <div class="qr-asset-code">{{ $asset->asset_code }}</div>
                        <div class="qr-wrap" id="qrRender"></div>
                        <div class="qr-asset-name">{{ $asset->description }}</div>
                        <div style="font-size:10px;color:rgba(255,255,255,.5);">National Irrigation Administration — RO IX</div>
                    </div>
                    <div style="margin-top:14px;">
                        <button type="button" class="btn btn-sm" onclick="window.print()">🖨️ Print QR Label</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
setTimeout(function() {
    const el = document.getElementById('qrRender');
    if (el && typeof QRCode !== 'undefined') {
        new QRCode(el, {
            text: '{{ $asset->asset_code }}|{{ addslashes($asset->description) }}|NIA RO IX',
            width: 120,
            height: 120,
            colorDark: '#0F2044',
            colorLight: '#ffffff'
        });
    }
}, 200);
</script>
@endsection
