@extends('layouts.app')

@section('title', 'Edit Asset')
@section('subtitle', 'Update asset information')

@section('content')
<form action="{{ route('assets.update', $asset->id) }}" method="POST">
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
                                <option value="for_disposal" {{ $asset->status == 'for_disposal' ? 'selected' : '' }}>For Disposal</option>
                                <option value="missing" {{ $asset->status == 'missing' ? 'selected' : '' }}>Missing</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asset Description *</label>
                        <input class="form-control" name="description" value="{{ $asset->description }}" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-control" name="category_id">
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $asset->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Account Code</label>
                            <select class="form-control" name="account_id">
                                <option value="">Select account</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}" {{ $asset->account_id == $acc->id ? 'selected' : '' }}>{{ $acc->account_code }} - {{ $acc->account_name }}</option>
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
                        <label class="form-label">Fund Source</label>
                        <input class="form-control" name="fund_source" value="{{ $asset->fund_source }}">
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
                                <option value="{{ $office->id }}" {{ $asset->office_id == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Condition *</label>
                        <select class="form-control" name="condition" required>
                            <option value="good" {{ $asset->condition == 'good' ? 'selected' : '' }}>Good</option>
                            <option value="fair" {{ $asset->condition == 'fair' ? 'selected' : '' }}>Fair</option>
                            <option value="poor" {{ $asset->condition == 'poor' ? 'selected' : '' }}>Poor</option>
                            <option value="for_repair" {{ $asset->condition == 'for_repair' ? 'selected' : '' }}>For Repair</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2">{{ $asset->remarks }}</textarea>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        <button type="submit" class="btn btn-primary" style="flex:1;">Update Asset</button>
                        <a href="{{ route('assets.show', $asset->id) }}" class="btn" style="flex:1;">Cancel</a>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><span>📱</span><span class="card-title">QR Code</span></div>
                <div class="card-body" style="text-align:center;">
                    <div class="qr-card">
                        <div class="qr-asset-code">{{ $asset->asset_code }}</div>
                        <div class="qr-wrap" id="qrRender"></div>
                        <div class="qr-asset-name">{{ $asset->description }}</div>
                        <div style="font-size:10px;color:rgba(255,255,255,.5);">National Irrigation Administration — RO IX</div>
                    </div>
                    <div style="margin-top:14px;">
                        <button class="btn btn-sm" onclick="showToast('QR label sent to printer.')">🖨️ Print QR Label</button>
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
            text: '{{ $asset->asset_code }}|{{ $asset->description }}|NIA RO IX',
            width: 120,
            height: 120,
            colorDark: '#0F2044',
            colorLight: '#ffffff'
        });
    }
}, 200);
</script>
@endsection
