@extends('layouts.app')

@section('title', 'Transfer Management')
@section('subtitle', 'Track and manage government asset movements and custodian turnovers')

@section('content')
<div class="tabs">
    <div class="tab active" onclick="switchTab(this, 'tab-pending')">Pending Transfers ({{ count($pendingTransfers) }})</div>
    <div class="tab" onclick="switchTab(this, 'tab-completed')">Completed / Acknowledged ({{ count($completedTransfers) }})</div>
    <div class="tab" onclick="switchTab(this, 'tab-all')">All Transfer History ({{ count($allTransfers) }})</div>
</div>

<div id="tab-pending">
    @if(count($pendingTransfers) > 0)
    <div class="alert alert-warning">
        <span>⚠️</span>
        <div>{{ count($pendingTransfers) }} transfer request(s) are awaiting acknowledgment. Assets remain under previous custodian responsibility until receipt is formally acknowledged.</div>
    </div>
    @endif
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ref. No.</th><th>Asset Code</th><th>Description</th><th>From Station</th><th>To Station & Custodian</th><th>Transfer Date</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($pendingTransfers as $transfer)
                    <tr>
                        <td class="td-code">{{ $transfer->transfer_number }}</td>
                        <td class="td-code"><a href="{{ route('supply.assets.show', $transfer->asset_id) }}" style="color:var(--navy);text-decoration:none;">{{ $transfer->asset_code }}</a></td>
                        <td>{{ $transfer->description }}</td>
                        <td>{{ $transfer->from_office_name ?? 'Regional Office' }} @if($transfer->from_custodian_name)({{ $transfer->from_custodian_name }})@endif</td>
                        <td><strong>{{ $transfer->to_office_name }}</strong><br><small style="color:var(--gray-500);">Custodian: {{ $transfer->to_custodian_name }}</small></td>
                        <td>{{ $transfer->transfer_date }}</td>
                        <td>
                            <form action="{{ route('supply.transfers.acknowledge', $transfer->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Acknowledge and accept receipt of this asset?')">✓ Acknowledge</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center" style="padding:24px;color:var(--gray-500);">No pending transfers awaiting acknowledgment</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="tab-completed" style="display:none;">
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ref. No.</th><th>Asset Code</th><th>Description</th><th>Movement Route</th><th>Date Acknowledged</th></tr></thead>
                <tbody>
                    @forelse($completedTransfers as $transfer)
                    <tr>
                        <td class="td-code">{{ $transfer->transfer_number }}</td>
                        <td class="td-code"><a href="{{ route('supply.assets.show', $transfer->asset_id) }}" style="color:var(--navy);text-decoration:none;">{{ $transfer->asset_code }}</a></td>
                        <td>{{ $transfer->description }}</td>
                        <td>{{ $transfer->from_office_name ?? 'Previous Office' }} → <strong>{{ $transfer->to_office_name }}</strong> ({{ $transfer->to_custodian_name }})</td>
                        <td>{{ $transfer->acknowledged_at ?? $transfer->transfer_date }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center" style="padding:24px;color:var(--gray-500);">No acknowledged transfers recorded yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="tab-all" style="display:none;">
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ref. No.</th><th>Asset Code</th><th>Route</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($allTransfers as $transfer)
                    <tr>
                        <td class="td-code">{{ $transfer->transfer_number }}</td>
                        <td>{{ $transfer->asset_code }}</td>
                        <td>{{ $transfer->from_office_name ?? 'Station' }} → {{ $transfer->to_office_name }}</td>
                        <td>
                            @if($transfer->status == 'acknowledged')
                                <span class="badge badge-active"><span class="dot"></span>Acknowledged</span>
                            @elseif($transfer->status == 'approved')
                                <span class="badge badge-maintenance"><span class="dot"></span>Approved / In Transit</span>
                            @else
                                <span class="badge badge-pending"><span class="dot"></span>Pending</span>
                            @endif
                        </td>
                        <td>{{ $transfer->transfer_date }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center" style="padding:24px;color:var(--gray-500);">No transfer history records found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top:16px;">
    <button class="btn btn-primary" onclick="showTransferModal()">+ Initiate Property Transfer</button>
</div>

<script>
function switchTab(el, id) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    ['tab-pending','tab-completed','tab-all'].forEach(t => {
        document.getElementById(t).style.display = t === id ? 'block' : 'none';
    });
}

function showTransferModal() {
    const content = `
        <form action="{{ route('supply.transfers.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Asset to Transfer *</label>
                <select class="form-control" name="asset_id" required>
                    <option value="">Select asset from active inventory...</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->asset_id ?? $asset->id }}">{{ $asset->asset_code }} — {{ $asset->description }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Destination Office / Station *</label>
                <select class="form-control" name="to_office_id" required>
                    <option value="">Select receiving office...</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->office_id ?? $office->id }}">{{ $office->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Receiving Custodian (Personnel) *</label>
                <select class="form-control" name="to_custodian_id" required>
                    <option value="">Select accountable receiving personnel...</option>
                    @foreach($personnel as $p)
                        <option value="{{ $p->personnel_id ?? $p->id }}">{{ $p->full_name }} ({{ $p->position ?? 'Personnel' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Effective Transfer Date *</label>
                <input class="form-control" name="transfer_date" type="date" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Purpose / Justification</label>
                <textarea class="form-control" name="purpose" rows="2" placeholder="Reassignment, field project deployment, or station relocation..."></textarea>
            </div>
            <div class="alert alert-info" style="margin-top:10px;">
                <span>ℹ️</span>
                <div style="font-size:12px;">This action generates a Property Transfer Report (PTR). The receiving officer must confirm receipt to officially update the accountability records.</div>
            </div>
            <div style="display:flex;gap:8px;margin-top:14px;">
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">Submit Transfer Request</button>
            </div>
        </form>
    `;
    showModal('Initiate Asset Transfer', content);
}
</script>
@endsection
