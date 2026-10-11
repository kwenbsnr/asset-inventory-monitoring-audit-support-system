<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class QRController extends Controller
{
    public function index()
    {
        // Get recent scans
        $recentScans = DB::table('qr_scans')
            ->leftJoin('assets', 'qr_scans.asset_id', '=', 'assets.asset_id')
            ->leftJoin('users', 'qr_scans.scanned_by', '=', 'users.users_id')
            ->leftJoin('personnel', function($join) {
                $join->on('users.personnel_id', '=', 'personnel.personnel_id')
                     ->orOn('qr_scans.scanned_by', '=', 'personnel.personnel_id');
            })
            ->select(
                'qr_scans.*',
                'assets.asset_code',
                'assets.description',
                DB::raw('COALESCE(personnel.full_name, users.username, "Scanner") as scanner_name')
            )
            ->orderBy('qr_scans.scanned_at', 'desc')
            ->limit(10)
            ->get();

        return view('supply.qr_scanner', compact('recentScans'));
    }

    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string'
        ]);

        $qrData = trim($request->input('qr_data'));
        $scanPurpose = $request->input('scan_purpose', 'verification');

        $userAgent = 'Unknown Device';
        if ($request->hasHeader('User-Agent')) {
            $userAgent = $request->header('User-Agent');
        }

        // Find asset by code or reference
        $asset = DB::table('assets')
            ->leftJoin('asset_accounts', 'assets.asset_accounts_id', '=', 'asset_accounts.asset_accounts_id')
            ->leftJoin('asset_custodies', function($join) {
                $join->on('assets.asset_id', '=', 'asset_custodies.asset_id')
                     ->where('asset_custodies.status', 'active');
            })
            ->leftJoin('offices', function($join) {
                $join->on('asset_custodies.office_id', '=', 'offices.office_id')
                     ->orOn('assets.office_id', '=', 'offices.office_id');
            })
            ->leftJoin('personnel', 'asset_custodies.custodian_id', '=', 'personnel.personnel_id')
            ->select(
                'assets.*',
                'asset_accounts.account_name as category_name',
                'asset_accounts.account_code',
                'asset_accounts.account_name',
                'offices.name as office_name',
                'personnel.full_name as custodian_name',
                'asset_custodies.property_number as par_number',
                'asset_custodies.effectivity_date'
            )
            ->where(function($q) use ($qrData) {
                $q->where('assets.asset_code', $qrData)
                  ->orWhere('assets.qr_code_ref', $qrData);
                if (is_numeric($qrData)) {
                    $q->orWhere('assets.asset_id', (int)$qrData);
                }
            })
            ->first();

        if (!$asset) {
            return response()->json(['error' => 'Asset not found'], 404);
        }

        $userId = Session::get('user_id', 1);

        // Log QR scan
        DB::table('qr_scans')->insert([
            'asset_id' => $asset->asset_id,
            'scanned_by' => $userId,
            'scan_purpose' => $scanPurpose,
            'device_info' => $userAgent,
            'scanned_at' => now()
        ]);

        // Log to audit trail
        $this->logAudit($asset->asset_id, 'qr_scan', 'QR Code scanned', null, json_encode(['qr_data' => $qrData]));

        // Get location history
        $locations = DB::table('asset_locations')
            ->where('asset_id', $asset->asset_id)
            ->orderBy('recorded_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'asset' => $asset,
            'locations' => $locations
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $query = $request->input('query');
        $assets = DB::table('assets')
            ->leftJoin('asset_accounts', 'assets.asset_accounts_id', '=', 'asset_accounts.asset_accounts_id')
            ->leftJoin('asset_custodies', function($join) {
                $join->on('assets.asset_id', '=', 'asset_custodies.asset_id')
                     ->where('asset_custodies.status', 'active');
            })
            ->leftJoin('offices', function($join) {
                $join->on('asset_custodies.office_id', '=', 'offices.office_id')
                     ->orOn('assets.office_id', '=', 'offices.office_id');
            })
            ->leftJoin('personnel', 'asset_custodies.custodian_id', '=', 'personnel.personnel_id')
            ->select(
                'assets.*',
                'asset_accounts.account_name as category_name',
                'offices.name as office_name',
                'personnel.full_name as custodian_name'
            )
            ->where(function($q) use ($query) {
                $q->where('assets.asset_code', 'like', "%{$query}%")
                  ->orWhere('assets.description', 'like', "%{$query}%")
                  ->orWhere('assets.serial_number', 'like', "%{$query}%")
                  ->orWhere('personnel.full_name', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        return response()->json($assets);
    }

    public function generateQR(int $assetId)
    {
        $asset = DB::table('assets')
            ->where('asset_id', $assetId)
            ->orWhere('id', $assetId)
            ->first();

        if (!$asset) {
            return response()->json(['error' => 'Asset not found'], 404);
        }

        return response()->json([
            'qr_data' => $asset->asset_code,
            'asset_code' => $asset->asset_code,
            'description' => $asset->description
        ]);
    }

    private function logAudit(?int $assetId, string $actionType, string $module, ?string $oldValues, ?string $newValues)
    {
        $userId = Session::get('personnel_id') ?: Session::get('user_id', 1);

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
