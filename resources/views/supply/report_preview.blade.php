<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }} — NIA RO IX AIMS</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;color:#1e293b;background:#f8fafc;padding:24px;font-size:12px;line-height:1.4;}
        .report-page{max-width:1100px;margin:0 auto;background:#fff;padding:36px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.08);}
        .report-header{text-align:center;border-bottom:2px solid #0f2044;padding-bottom:16px;margin-bottom:20px;}
        .agency-name{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#475569;}
        .agency-title{font-size:15px;font-weight:700;color:#0f2044;margin-top:2px;}
        .agency-region{font-size:12px;font-weight:600;color:#0d9488;}
        .report-title{font-size:17px;font-weight:800;color:#0f2044;margin-top:12px;text-transform:uppercase;letter-spacing:.02em;}
        .report-meta{display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-top:8px;}
        .actions-bar{display:flex;gap:10px;margin-bottom:20px;max-width:1100px;margin-left:auto;margin-right:auto;}
        .btn{padding:8px 16px;font-size:13px;font-weight:600;border-radius:6px;cursor:pointer;border:1px solid #cbd5e1;background:#fff;color:#334155;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
        .btn-primary{background:#0d9488;color:#fff;border-color:#0d9488;}
        .btn-primary:hover{background:#0f766e;}
        table{width:100%;border-collapse:collapse;margin-top:12px;}
        th{background:#f1f5f9;color:#334155;font-weight:700;font-size:11px;text-transform:uppercase;padding:8px 10px;border:1px solid #cbd5e1;text-align:left;}
        td{padding:8px 10px;border:1px solid #e2e8f0;font-size:12px;color:#334155;}
        tr:nth-child(even){background:#f8fafc;}
        .text-right{text-align:right;}
        .text-center{text-align:center;}
        .mono{font-family:monospace;font-weight:600;}
        .report-footer{display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px;margin-top:40px;padding-top:20px;border-top:1px solid #e2e8f0;}
        .sign-box{text-align:center;}
        .sign-line{border-bottom:1px solid #000;margin:40px 20px 6px;}
        .sign-name{font-weight:700;font-size:12px;}
        .sign-title{font-size:11px;color:#64748b;}
        @media print{
            body{background:#fff;padding:0;}
            .report-page{box-shadow:none;padding:0;max-width:100%;}
            .actions-bar{display:none;}
        }
    </style>
</head>
<body>
    <div class="actions-bar">
        <a href="{{ route('supply.reports.index') }}" class="btn">← Back to Reports</a>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print / Save as PDF</button>
    </div>

    <div class="report-page">
        <div class="report-header">
            <div class="agency-name">Republic of the Philippines · Office of the President</div>
            <div class="agency-title">NATIONAL IRRIGATION ADMINISTRATION</div>
            <div class="agency-region">Regional Office No. IX · Zamboanga Peninsula</div>
            <div class="report-title">{{ $reportTitle }}</div>
            <div class="report-meta">
                <span>As of Date: {{ $dateTo ?: date('F d, Y') }} @if($dateFrom) (From: {{ $dateFrom }}) @endif</span>
                <span>Generated: {{ $generatedAt }}</span>
            </div>
        </div>

        @if($reportType === 'complete_inventory' || $reportType === 'unverified')
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Property No. / Asset Code</th>
                        <th>Article & Description</th>
                        <th>Classification</th>
                        <th>Serial No.</th>
                        <th>Station</th>
                        <th>Custodian</th>
                        <th class="text-right">Acquisition Cost (₱)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @forelse($data as $idx => $row)
                    @php $grandTotal += ($row->acquisition_cost ?? 0); @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="mono">{{ $row->asset_code }}</td>
                        <td>
                            <strong>{{ $row->description }}</strong>
                            @if($row->brand || $row->model)
                                <div style="font-size:11px;color:#64748b;">{{ $row->brand }} {{ $row->model }}</div>
                            @endif
                        </td>
                        <td>{{ $row->category_name ?? 'General Asset' }}</td>
                        <td class="mono">{{ $row->serial_number ?? 'N/A' }}</td>
                        <td>{{ $row->office_name ?? 'Regional Office' }}</td>
                        <td>{{ $row->custodian_name ?? 'Unassigned' }}</td>
                        <td class="text-right mono">₱{{ number_format($row->acquisition_cost ?? 0, 2) }}</td>
                        <td><span style="font-weight:600;text-transform:capitalize;">{{ str_replace('_', ' ', $row->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center" style="padding:20px;">No inventory property records found matching criteria</td></tr>
                    @endforelse
                </tbody>
                @if(count($data) > 0)
                <tfoot>
                    <tr style="background:#f1f5f9;font-weight:700;">
                        <td colspan="7" class="text-right">Total Accountable Valuation:</td>
                        <td class="text-right mono">₱{{ number_format($grandTotal, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        @elseif($reportType === 'by_category')
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Account Code</th>
                        <th>Classification Description</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Total Valuation (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalQty = 0; $totalVal = 0; @endphp
                    @forelse($data as $idx => $row)
                    @php $totalQty += $row->count; $totalVal += ($row->total_value ?? 0); @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="mono">{{ $row->account_code ?? 'N/A' }}</td>
                        <td><strong>{{ $row->category }}</strong></td>
                        <td class="text-right">{{ $row->count }}</td>
                        <td class="text-right mono">₱{{ number_format($row->total_value ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center" style="padding:20px;">No category records found</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:#f1f5f9;font-weight:700;">
                        <td colspan="3" class="text-right">Grand Total:</td>
                        <td class="text-right">{{ $totalQty }}</td>
                        <td class="text-right mono">₱{{ number_format($totalVal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @elseif($reportType === 'by_location')
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Office / Station Station</th>
                        <th class="text-right">Assigned Assets Count</th>
                        <th class="text-right">Total Asset Valuation (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalQty = 0; $totalVal = 0; @endphp
                    @forelse($data as $idx => $row)
                    @php $totalQty += $row->count; $totalVal += ($row->total_value ?? 0); @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $row->office }}</strong></td>
                        <td class="text-right">{{ $row->count }}</td>
                        <td class="text-right mono">₱{{ number_format($row->total_value ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center" style="padding:20px;">No location records found</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:#f1f5f9;font-weight:700;">
                        <td colspan="2" class="text-right">Grand Total:</td>
                        <td class="text-right">{{ $totalQty }}</td>
                        <td class="text-right mono">₱{{ number_format($totalVal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @elseif($reportType === 'custodian_assignment')
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Accountable Custodian</th>
                        <th>Station Office</th>
                        <th>PAR Ref No.</th>
                        <th class="text-right">Total Quantity</th>
                        <th class="text-right">Total Accountable Valuation (₱)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalQty = 0; $totalVal = 0; @endphp
                    @forelse($data as $idx => $row)
                    @php $totalQty += $row->asset_count; $totalVal += ($row->total_value ?? 0); @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><strong>{{ $row->custodian }}</strong></td>
                        <td>{{ $row->office }}</td>
                        <td class="mono">{{ $row->par_number ?? 'N/A' }}</td>
                        <td class="text-right">{{ $row->asset_count }}</td>
                        <td class="text-right mono">₱{{ number_format($row->total_value ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center" style="padding:20px;">No custodian assignment records found</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:#f1f5f9;font-weight:700;">
                        <td colspan="4" class="text-right">Grand Total:</td>
                        <td class="text-right">{{ $totalQty }}</td>
                        <td class="text-right mono">₱{{ number_format($totalVal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @elseif($reportType === 'transfer_history')
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>PTR Number</th>
                        <th>Asset Code & Description</th>
                        <th>From Custodian / Station</th>
                        <th>To Custodian / Station</th>
                        <th>Transfer Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="mono">{{ $row->transfer_number }}</td>
                        <td><strong>{{ $row->asset_code }}</strong> — {{ $row->description }}</td>
                        <td>{{ $row->from_office ?? 'Station' }} @if($row->from_custodian)({{ $row->from_custodian }})@endif</td>
                        <td><strong>{{ $row->to_office ?? 'Station' }}</strong> @if($row->to_custodian)({{ $row->to_custodian }})@endif</td>
                        <td>{{ $row->transfer_date }}</td>
                        <td><span style="font-weight:600;text-transform:capitalize;">{{ $row->status }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center" style="padding:20px;">No property movements found</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif($reportType === 'discrepancies')
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Asset Code</th>
                        <th>Description</th>
                        <th>Classification</th>
                        <th>Reported Discrepancy Findings</th>
                        <th>Inspector</th>
                        <th>Inspection Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="mono">{{ $row->asset_code }}</td>
                        <td>{{ $row->description }}</td>
                        <td>{{ $row->category_name ?? 'General' }}</td>
                        <td style="color:#b91c1c;font-weight:600;">{{ $row->findings ?? $row->discrepancy_type ?? 'Physical discrepancy noted during inventory count' }}</td>
                        <td>{{ $row->verified_by_name ?? 'Inspection Officer' }}</td>
                        <td>{{ $row->verified_at ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center" style="padding:20px;">No discrepancy findings recorded</td></tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Asset Code</th>
                        <th>Description</th>
                        <th>Office / Station</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="mono">{{ $row->asset_code ?? 'N/A' }}</td>
                        <td>{{ $row->description ?? ($row->action_type ?? 'N/A') }}</td>
                        <td>{{ $row->office_name ?? ($row->user_name ?? 'Regional Office') }}</td>
                        <td>{{ $row->status ?? ($row->action ?? 'Active') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center" style="padding:20px;">No records found</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        <div class="report-footer">
            <div class="sign-box">
                <div>Prepared by:</div>
                <div class="sign-line"></div>
                <div class="sign-name">{{ session('user_name', 'Property Officer') }}</div>
                <div class="sign-title">Property & Supply Officer</div>
            </div>
            <div class="sign-box">
                <div>Verified & Audited by:</div>
                <div class="sign-line"></div>
                <div class="sign-name">Internal Audit Committee</div>
                <div class="sign-title">COA / Inspection Unit</div>
            </div>
            <div class="sign-box">
                <div>Approved by:</div>
                <div class="sign-line"></div>
                <div class="sign-name">Regional Irrigation Manager</div>
                <div class="sign-title">Head of Procuring Entity</div>
            </div>
        </div>
    </div>
</body>
</html>
