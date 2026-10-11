@extends('layouts.app')

@section('title', 'Asset Registry')
@section('subtitle', 'Browse and manage all registered government property')

@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
    <div class="search-bar" style="max-width:380px;">
        <span>🔍</span>
        <input type="text" id="assetSearch" placeholder="Search by code, description, serial, custodian..." onkeyup="filterAssets()">
    </div>
    <select class="form-control" id="statusFilter" style="width:160px;" onchange="filterAssets()">
        <option value="">All Statuses</option>
        <option value="active">Active</option>
        <option value="maintenance">Maintenance</option>
        <option value="transferred">Transferred</option>
        <option value="for_disposal">For Disposal</option>
        <option value="missing">Missing</option>
        <option value="disposed">Disposed</option>
    </select>
    <select class="form-control" id="categoryFilter" style="width:200px;" onchange="filterAssets()">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id ?? $cat->asset_accounts_id }}">{{ $cat->name }}</option>
        @endforeach
    </select>
    <a href="{{ route('supply.assets.create') }}" class="btn btn-primary btn-sm" style="margin-left:auto;">+ Register Asset</a>
</div>

<div class="card">
    <div class="card-header">
        <span>📦</span><span class="card-title">Registered Property & Equipment</span>
        <span style="margin-left:auto;font-size:12px;color:var(--gray-500);" id="assetCount">{{ count($assets) }} assets</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Asset Code</th>
                    <th>Description</th>
                    <th>Account / Category</th>
                    <th>Serial No.</th>
                    <th>Office / Station</th>
                    <th>Current Custodian</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="assetTableBody">
                @forelse($assets as $asset)
                <tr data-category-id="{{ $asset->asset_accounts_id ?? $asset->category_id ?? '' }}">
                    <td class="td-code">{{ $asset->asset_code }}</td>
                    <td>
                        <div style="font-weight:500;">{{ $asset->description }}</div>
                        @if($asset->brand || $asset->model)
                            <div style="font-size:11px;color:var(--gray-500);">{{ $asset->brand }} {{ $asset->model }}</div>
                        @endif
                    </td>
                    <td><span style="font-size:11px;background:var(--gray-100);padding:2px 7px;border-radius:10px;">{{ $asset->category_name ?? 'General Asset' }}</span></td>
                    <td style="font-family:monospace;font-size:12px;">{{ $asset->serial_number ?? 'N/A' }}</td>
                    <td>{{ $asset->office_name ?? 'Regional Office' }}</td>
                    <td>
                        @if($asset->custodian_name)
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div class="user-avatar" style="width:26px;height:26px;font-size:10px;">{{ substr($asset->custodian_name, 0, 2) }}</div>
                            <span>{{ $asset->custodian_name }}</span>
                        </div>
                        @else
                        <span style="color:var(--gray-400);">Unassigned</span>
                        @endif
                    </td>
                    <td>
                        @if($asset->status == 'active')
                            <span class="badge badge-active"><span class="dot"></span>Active</span>
                        @elseif($asset->status == 'maintenance')
                            <span class="badge badge-maintenance"><span class="dot"></span>Maintenance</span>
                        @elseif($asset->status == 'transferred')
                            <span class="badge badge-transferred"><span class="dot"></span>Transferred</span>
                        @elseif($asset->status == 'for_disposal' || $asset->status == 'pending_disposal')
                            <span class="badge badge-disposed"><span class="dot"></span>For Disposal</span>
                        @elseif($asset->status == 'missing')
                            <span class="badge badge-missing"><span class="dot"></span>Missing</span>
                        @elseif($asset->status == 'disposed')
                            <span class="badge badge-disposed"><span class="dot"></span>Disposed</span>
                        @else
                            <span class="badge badge-inactive"><span class="dot"></span>{{ ucfirst($asset->status) }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            <a href="{{ route('supply.assets.show', $asset->asset_id ?? $asset->id) }}" class="btn btn-sm">View</a>
                            <a href="{{ route('supply.assets.edit', $asset->asset_id ?? $asset->id) }}" class="btn btn-sm btn-primary">Edit</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding:30px;color:var(--gray-500);">
                        <div>📭 No assets found</div>
                        <div style="font-size:12px;margin-top:4px;"><a href="{{ route('supply.assets.create') }}" style="color:var(--teal);">Register your first asset</a></div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function filterAssets() {
    const search = document.getElementById('assetSearch').value.toLowerCase().trim();
    const status = document.getElementById('statusFilter').value.toLowerCase().trim();
    const category = document.getElementById('categoryFilter').value.trim();

    const rows = document.querySelectorAll('#assetTableBody tr');
    let visible = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const rowStatus = row.querySelector('td:nth-child(7)')?.textContent?.trim()?.toLowerCase() || '';
        const rowCatId = row.dataset.categoryId || '';

        let show = true;
        if (search && !text.includes(search)) show = false;
        if (status && !rowStatus.includes(status)) show = false;
        if (category && rowCatId !== category) show = false;

        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('assetCount').textContent = visible + ' assets';
}
</script>
@endsection
