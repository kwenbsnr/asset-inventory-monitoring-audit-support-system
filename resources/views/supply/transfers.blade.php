@extends('layouts.app')

@section('title', 'Transfer Management')
@section('subtitle', 'Track asset movements')

@section('content')
<div class="tabs">
    <div class="tab active" onclick="switchTab(this, 'tab-pending')">Pending ({{ count($pendingTransfers) }})</div>
    <div class="tab" onclick="switchTab(this, 'tab-completed')">Completed</div>
    <div class="tab" onclick="switchTab(this, 'tab-all')">All Transfers</div>
</div>

<div id="tab-pending">
    @if(count($pendingTransfers) > 0)
    <div class="alert alert-warning"><span>⚠️</span><div>{{ count($pendingTransfers) }} transfer requests are awaiting custodian acknowledgment. Assets remain under previous custodian's responsibility until acknowledged.</div></div>
    @endif
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead><tr><th>Ref. No.</th><th>Asset Code</th><th>Description</th><th>From</th><th>To</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($pendingTransfers as $transfer)
                    <tr>
                        <td class="td-code">{{ $transfer->transfer_number }}</td>
                        <td class="td-code">{{ $transfer->asset_code }}</td>
                        <td>{{ $transfer->description }}</td>
                        <td>{{ $transfer->from_office_name ?? 'Unknown' }}</td>
                        <td>{{ $transfer->to_office_name }}</td>
                        <td>{{ $transfer->transfer_date }}</td>
                        <td>
                            <form action="{{ route('transfers.acknowledge', $transfer->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Acknowledge this transfer?')">Acknowledge</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center" style="padding:20px;color:var(--gray-500);">No pending transfers</td></tr>
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
                <thead><tr><th>Ref. No.</th><th>Asset Code</th><th>Description</th><th>From → To</th><th>Date Completed</th></tr></thead>
                <tbody>
                    @forelse($completedTransfers as $transfer)
                    <tr>
                        <td class="td-code">{{ $transfer->transfer_number }}</td>
                        <td class="td-code">{{ $transfer->asset_code }}</td>
                        <td>{{ $transfer->description }}</td>
                        <td>{{ $transfer->from_office_name ?? 'Unknown' }} → {{ $transfer->to_office_name }}</td>
                        <td>{{ $transfer->acknowledged_at ?? $transfer->transfer_date }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center" style="padding:20px;color:var(--gray-500);">No completed transfers</td></tr>
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
                <thead><tr><th>Ref. No.</th><th>Asset</th><th>Route</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($allTransfers as $transfer)
                    <tr>
                        <td class="td-code">{{ $transfer->transfer_number }}</td>
                        <td>{{ $transfer->asset_code }}</td>
                        <td>{{ $transfer->from_office_name ?? 'Unknown' }} → {{ $transfer->to_office_name }}</td>
                        <td>
                            @if($transfer->status == 'pending')
                                <span class="badge badge-pending"><span class="dot"></span>Pending</span>
                            @else
                                <span class="badge badge-completed"><span class="dot"></span>Completed</span>
                            @endif
                        </td>
                        <td>{{ $transfer->transfer_date }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center" style="padding:20px;color:var(--gray-500);">No transfers found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top:16px;">
    <button class="btn btn-primary" onclick="showTransferModal()">+ New Transfer Request</button>
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
        <form action="{{ route('transfers.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Asset *</label>
                <select class="form-control" name="asset_id" required>
                    <option value="">Select asset...</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->asset_code }} - {{ $asset->description }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Receiving Office *</label>
                <select class="form-control" name="to_office_id" required>
                    <option value="">Select receiving office...</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}">{{ $office->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Receiving Custodian *</label>
                <select class="form-control" name="to_custodian_id" required>
                    <option value="">Select receiving custodian...</option>
                    @foreach($personnel as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->position ?? 'No position' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Transfer Date *</label>
                <input class="form-control" name="transfer_date" type="date" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Purpose / Remarks</label>
                <textarea class="form-control" name="purpose" rows="2" placeholder="Reason for transfer, project assignment, etc."></textarea>
            </div>
            <div class="alert alert-info" style="margin-top:8px;">
                <span>ℹ️</span>
                <div style="font-size:12px;">A transfer will be logged in the audit trail. The receiving custodian must acknowledge receipt to complete the transfer.</div>
            </div>
            <div style="display:flex;gap:8px;margin-top:12px;">
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">Submit Transfer Request</button>
            </div>
        </form>
    `;
    showModal('New Transfer Request', content);
}

function closeModal() {
    document.getElementById('modal-container').innerHTML = '';
}
</script>
@endsection
