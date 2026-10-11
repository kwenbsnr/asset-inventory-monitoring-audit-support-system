@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('subtitle', 'Government property inventory compliance and audit reporting')

@section('content')
<div class="grid-4">
    <div class="metric-card accent-teal">
        <div class="metric-label">Total Assets Recorded</div>
        <div class="metric-value">{{ $totalAssets }}</div>
        <div class="metric-sub metric-up">Complete Ledger</div>
    </div>
    <div class="metric-card accent-green">
        <div class="metric-label">Total Acquisition Cost</div>
        <div class="metric-value">₱{{ number_format($totalValue, 2) }}</div>
        <div class="metric-sub metric-up">Accountable Valuation</div>
    </div>
    <div class="metric-card accent-amber">
        <div class="metric-label">Audit Verification Rate</div>
        <div class="metric-value">{{ $accuracyRate }}%</div>
        <div class="metric-sub" style="color:var(--gray-500);">Physical Count Coverage</div>
    </div>
    <div class="metric-card accent-navy">
        <div class="metric-label">Active Account Classes</div>
        <div class="metric-value">{{ count($categoryBreakdown) }}</div>
        <div class="metric-sub" style="color:var(--gray-500);">Asset Classifications</div>
    </div>
</div>

<div class="grid-2" style="margin-top:16px;">
    <div class="card">
        <div class="card-header"><span>📑</span><span class="card-title">Generate Official Reports</span></div>
        <div class="card-body">
            <form action="{{ route('supply.reports.generate') }}" method="POST" target="_blank">
                @csrf
                <div class="form-group">
                    <label class="form-label">Report Type / Schedule *</label>
                    <select class="form-control" name="report_type" required>
                        <option value="complete_inventory">Report on the Physical Count of Property (RPCPPE)</option>
                        <option value="by_category">Inventory Breakdown by Account Classification</option>
                        <option value="by_location">Asset Allocation by Station Office</option>
                        <option value="custodian_assignment">Property Acknowledgment Receipt (PAR) Custodian Summary</option>
                        <option value="transfer_history">Property Transfer Report (PTR) Movement History</option>
                        <option value="unverified">Unverified & Pending Physical Count Assets</option>
                        <option value="discrepancies">Inspection Discrepancies & Discrepancy Log</option>
                        <option value="audit_trail_report">System Audit Trail & Security Transaction Log</option>
                        <option value="for_disposal">Inventory and Inspection Report of Unserviceable Property (IIRUP)</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date From (optional)</label>
                        <input class="form-control" name="date_from" type="date">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date To (optional)</label>
                        <input class="form-control" name="date_to" type="date">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Filter by Office Station (optional)</label>
                    <select class="form-control" name="office_id">
                        <option value="">All Regional & Field Stations</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->office_id ?? $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Generate & Preview Official Report →</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span>📊</span><span class="card-title">Asset Classification Breakdown</span></div>
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Account Classification</th>
                            <th style="text-align:right;">Quantity</th>
                            <th style="text-align:right;">Total Valuation (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categoryBreakdown as $cat)
                        <tr>
                            <td><strong>{{ $cat->category }}</strong></td>
                            <td style="text-align:right;">{{ $cat->count }}</td>
                            <td style="text-align:right;font-family:monospace;">₱{{ number_format($cat->total_value ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center" style="padding:20px;color:var(--gray-500);">No category data found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
