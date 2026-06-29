@extends('layouts.app')

@section('title', 'Register Asset')
@section('subtitle', 'Add new government asset')

@section('content')
<form action="{{ route('assets.store') }}" method="POST">
    @csrf
    <div class="grid-2">
        <div>
            <div class="card">
                <div class="card-header"><span>📋</span><span class="card-title">Asset Information</span></div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Asset Code (auto-generated)</label>
                            <input class="form-control" value="NIA-RO9-XXXX" readonly style="background:var(--gray-50);font-family:monospace;font-weight:600;">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Property Number</label>
                            <input class="form-control" name="property_number" placeholder="e.g. 2026-06-001">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asset Description *</label>
                        <input class="form-control" name="description" placeholder="e.g. Backhoe Loader, CAT 315, Year 2024" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-control" name="category_id">
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Code</label>
                            <select class="form-control" name="account_id">
                                <option value="">Select account</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_code }} - {{ $acc->account_name }}</option>
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
                            <input class="form-control" name="brand" placeholder="Brand, model, year">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Acquisition Date</label>
                            <input class="form-control" name="acquisition_date" type="date">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Acquisition Cost (₱)</label>
                            <input class="form-control" name="acquisition_cost" type="number" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fund Source</label>
                        <input class="form-control" name="fund_source" placeholder="e.g. GAA 2026, Foreign Assisted">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><span>📍</span><span class="card-title">Location & Custodian</span></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Assigned Office</label>
                        <select class="form-control" name="office_id">
                            <option value="">Select office</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}">{{ $office->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Condition *</label>
                        <select class="form-control" name="condition" required>
                            <option value="good">Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="for_repair">For Repair</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Remarks / Additional Notes</label>
                        <textarea class="form-control" name="remarks" rows="2" placeholder="Any relevant notes about this asset..."></textarea>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">Register & Generate QR</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><span>📱</span><span class="card-title">QR Code Preview</span></div>
                <div class="card-body" style="text-align:center;">
                    <div class="qr-card">
                        <div class="qr-asset-code" id="qrCodeLabel">NIA-RO9-XXXX</div>
                        <div class="qr-wrap" id="qrRender"></div>
                        <div class="qr-asset-name" id="qrNameLabel">New Asset</div>
                        <div style="font-size:10px;color:rgba(255,255,255,.5);">National Irrigation Administration — RO IX</div>
                    </div>
                    <div style="margin-top:14px;text-align:left;">
                        <div class="alert alert-info">
                            <span>ℹ️</span>
                            <div style="font-size:12px;">After registration, print the QR label and affix it to the asset. Supply officers can scan it to instantly retrieve and update asset records.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.querySelector('input[name="description"]').addEventListener('input', function() {
    document.getElementById('qrNameLabel').textContent = this.value || 'New Asset';
});

// Initialize QR with placeholder
setTimeout(function() {
    const el = document.getElementById('qrRender');
    if (el && typeof QRCode !== 'undefined') {
        new QRCode(el, {
            text: 'NIA-RO9-XXXX|New Asset|NIA RO IX',
            width: 120,
            height: 120,
            colorDark: '#0F2044',
            colorLight: '#ffffff'
        });
    }
}, 200);
</script>
@endsection
