<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AssetController extends Controller
{
    public function dashboard()
    {
        $totalAssets = DB::table('assets')->count();
        $activeAssets = DB::table('assets')->where('status', 'active')->count();
        $pendingVerification = DB::table('inventory_line_items')
            ->where('verified_status', 'pending')
            ->count();
        $discrepancies = DB::table('inventory_line_items')
            ->where('verified_status', 'discrepancy')
            ->count();

        // Recent activity
        $recentActivity = DB::table('audit_trail')
            ->join('personnel', 'audit_trail.performed_by', '=', 'personnel.id')
            ->leftJoin('assets', 'audit_trail.asset_id', '=', 'assets.id')
            ->select('audit_trail.*', 'personnel.full_name as user_name', 'assets.asset_code', 'assets.description')
            ->orderBy('audit_trail.performed_at', 'desc')
            ->limit(10)
            ->get();

        // Assets needing attention
        $attentionAssets = DB::table('assets')
            ->leftJoin('asset_locations', 'assets.id', '=', 'asset_locations.asset_id')
            ->leftJoin('asset_custodies', function($join) {
                $join->on('assets.id', '=', 'asset_custodies.asset_id')
                     ->where('asset_custodies.status', 'active');
            })
            ->leftJoin('personnel', 'asset_custodies.custodian_id', '=', 'personnel.id')
            ->select('assets.*', 'asset_locations.location_name', 'personnel.full_name as custodian_name')
            ->where('assets.status', '!=', 'active')
            ->orWhereNull('asset_custodies.id')
            ->limit(10)
            ->get();

        return view('supply.dashboard', compact(
            'totalAssets', 'activeAssets', 'pendingVerification', 'discrepancies',
            'recentActivity', 'attentionAssets'
        ));
    }

    public function index(Request $request)
    {
        $query = DB::table('assets')
            ->leftJoin('asset_categories', 'assets.category_id', '=', 'asset_categories.id')
            ->leftJoin('asset_accounts', 'assets.account_id', '=', 'asset_accounts.id')
            ->leftJoin('offices', 'assets.office_id', '=', 'offices.id')
            ->leftJoin('asset_custodies', function($join) {
                $join->on('assets.id', '=', 'asset_custodies.asset_id')
                     ->where('asset_custodies.status', 'active');
            })
            ->leftJoin('personnel', 'asset_custodies.custodian_id', '=', 'personnel.id')
            ->select(
                'assets.*',
                'asset_categories.name as category_name',
                'asset_accounts.account_code',
                'offices.name as office_name',
                'personnel.full_name as custodian_name'
            );

        // Search
        if ($request->has('search') && $request->input('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('assets.asset_code', 'like', "%{$search}%")
                  ->orWhere('assets.description', 'like', "%{$search}%")
                  ->orWhere('assets.serial_number', 'like', "%{$search}%")
                  ->orWhere('personnel.full_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->input('status')) {
            $query->where('assets.status', $request->input('status'));
        }

        // Filter by category
        if ($request->has('category') && $request->input('category')) {
            $query->where('assets.category_id', $request->input('category'));
        }

        $assets = $query->orderBy('assets.created_at', 'desc')->get();

        // Get categories for filter dropdown
        $categories = DB::table('asset_categories')->get();

        return view('supply.assets', compact('assets', 'categories'));
    }

    public function create()
    {
        $categories = DB::table('asset_categories')->get();
        $accounts = DB::table('asset_accounts')->get();
        $offices = DB::table('offices')->get();
        $personnel = DB::table('personnel')->where('is_active', 1)->get();

        return view('supply.asset_create', compact('categories', 'accounts', 'offices', 'personnel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|exists:asset_categories,id',
            'account_id' => 'nullable|exists:asset_accounts,id',
            'office_id' => 'nullable|exists:offices,id',
            'serial_number' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'acquisition_cost' => 'nullable|numeric',
            'acquisition_date' => 'nullable|date',
            'fund_source' => 'nullable|string|max:100',
            'condition' => 'required|in:good,fair,poor,for_repair',
        ]);

        // Generate asset code
        $lastAsset = DB::table('assets')->orderBy('id', 'desc')->first();
        $nextId = $lastAsset ? $lastAsset->id + 1 : 1;
        $assetCode = 'NIA-RO9-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $assetId = DB::table('assets')->insertGetId([
            'asset_code' => $assetCode,
            'qr_code_ref' => $assetCode,
            'description' => $request->input('description'),
            'brand' => $request->input('brand'),
            'model' => $request->input('model'),
            'serial_number' => $request->input('serial_number'),
            'acquisition_cost' => $request->input('acquisition_cost'),
            'acquisition_date' => $request->input('acquisition_date'),
            'fund_source' => $request->input('fund_source'),
            'category_id' => $request->input('category_id'),
            'account_id' => $request->input('account_id'),
            'office_id' => $request->input('office_id'),
            'status' => 'active',
            'condition' => $request->input('condition'),
            'remarks' => $request->input('remarks'),
            'created_at' => now()
        ]);

        // Log to audit trail
        $this->logAudit($assetId, 'registration', 'Asset registered', null, json_encode($request->all()));

        return redirect()->route('supply.assets.index')->with('success', "Asset {$assetCode} registered successfully!");
    }

    public function show(int $id)
    {
        $asset = DB::table('assets')
            ->leftJoin('asset_categories', 'assets.category_id', '=', 'asset_categories.id')
            ->leftJoin('asset_accounts', 'assets.account_id', '=', 'asset_accounts.id')
            ->leftJoin('offices', 'assets.office_id', '=', 'offices.id')
            ->leftJoin('asset_custodies', function($join) {
                $join->on('assets.id', '=', 'asset_custodies.asset_id')
                     ->where('asset_custodies.status', 'active');
            })
            ->leftJoin('personnel', 'asset_custodies.custodian_id', '=', 'personnel.id')
            ->select(
                'assets.*',
                'asset_categories.name as category_name',
                'asset_accounts.account_code',
                'asset_accounts.account_name',
                'offices.name as office_name',
                'personnel.full_name as custodian_name',
                'asset_custodies.par_number',
                'asset_custodies.effectivity_date'
            )
            ->where('assets.id', $id)
            ->first();

        if (!$asset) {
            return redirect()->route('supply.assets.index')->with('error', 'Asset not found');
        }

        // Get location history
        $locations = DB::table('asset_locations')
            ->where('asset_id', $id)
            ->orderBy('recorded_at', 'desc')
            ->get();

        // Get transfer history
        $transfers = DB::table('asset_transfers')
            ->where('asset_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('supply.asset_detail', compact('asset', 'locations', 'transfers'));
    }

    public function edit(int $id)
    {
        $asset = DB::table('assets')->where('id', $id)->first();
        if (!$asset) {
            return redirect()->route('supply.assets.index')->with('error', 'Asset not found');
        }

        $categories = DB::table('asset_categories')->get();
        $accounts = DB::table('asset_accounts')->get();
        $offices = DB::table('offices')->get();

        return view('supply.asset_edit', compact('asset', 'categories', 'accounts', 'offices'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|exists:asset_categories,id',
            'account_id' => 'nullable|exists:asset_accounts,id',
            'office_id' => 'nullable|exists:offices,id',
            'serial_number' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'acquisition_cost' => 'nullable|numeric',
            'acquisition_date' => 'nullable|date',
            'status' => 'required|in:active,maintenance,transferred,for_disposal,missing',
            'condition' => 'required|in:good,fair,poor,for_repair',
        ]);

        $oldData = DB::table('assets')->where('id', $id)->first();

        DB::table('assets')
            ->where('id', $id)
            ->update([
                'description' => $request->input('description'),
                'brand' => $request->input('brand'),
                'model' => $request->input('model'),
                'serial_number' => $request->input('serial_number'),
                'acquisition_cost' => $request->input('acquisition_cost'),
                'acquisition_date' => $request->input('acquisition_date'),
                'fund_source' => $request->input('fund_source'),
                'category_id' => $request->input('category_id'),
                'account_id' => $request->input('account_id'),
                'office_id' => $request->input('office_id'),
                'status' => $request->input('status'),
                'condition' => $request->input('condition'),
                'remarks' => $request->input('remarks'),
                'updated_at' => now()
            ]);

        $this->logAudit($id, 'update', 'Asset updated', json_encode($oldData), json_encode($request->all()));

        return redirect()->route('supply.assets.index')->with('success', 'Asset updated successfully!');
    }

    public function destroy(int $id)
    {
        $asset = DB::table('assets')->where('id', $id)->first();
        DB::table('assets')->where('id', $id)->delete();

        $this->logAudit($id, 'deletion', 'Asset deleted', json_encode($asset), null);

        return redirect()->route('supply.assets.index')->with('success', 'Asset deleted successfully!');
    }

    private function logAudit(?int $assetId, string $actionType, string $module, ?string $oldValues, ?string $newValues)
    {
        $userId = Session::get('personnel_id', 1);

        DB::table('audit_trail')->insert([
            'asset_id' => $assetId,
            'performed_by' => $userId,
            'action_type' => $actionType,
            'module' => $module,
            'previous_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'performed_at' => now()
        ]);
    }
}
