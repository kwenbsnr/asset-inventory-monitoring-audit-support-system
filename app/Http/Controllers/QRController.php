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
            ->join('assets', 'qr_scans.asset_id', '=', 'assets.id')
            ->join('personnel', 'qr_scans.scanned_by', '=', 'personnel.id')
            ->select('qr_scans.*', 'assets.asset_code', 'assets.description', 'personnel.full_name as scanner_name')
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

        // Get QR data from request using input() method
        $qrData = $request->input('qr_data');
        $scanPurpose = $request->input('scan_purpose', 'verification');

        // Get user agent safely
        $userAgent = 'Unknown Device';
        if ($request->hasHeader('User-Agent')) {
            $userAgent = $request->header('User-Agent');
        }

        // Find asset by QR code reference (asset_code)
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
            ->where('assets.asset_code', $qrData)
            ->orWhere('assets.qr_code_ref', $qrData)
            ->first();

        if (!$asset) {
            return response()->json(['error' => 'Asset not found'], 404);
        }

        // Get user ID from session
        $userId = Session::get('user_id', 1);

        // Log QR scan
        DB::table('qr_scans')->insert([
            'asset_id' => $asset->id,
            'scanned_by' => $userId,
            'scan_purpose' => $scanPurpose,
            'device_info' => $userAgent,
            'scanned_at' => now()
        ]);

        // Log to audit trail
        $this->logAudit($asset->id, 'qr_scan', 'QR Code scanned', null, json_encode(['qr_data' => $qrData]));

        // Get location history
        $locations = DB::table('asset_locations')
            ->where('asset_id', $asset->id)
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
            ->leftJoin('asset_categories', 'assets.category_id', '=', 'asset_categories.id')
            ->leftJoin('offices', 'assets.office_id', '=', 'offices.id')
            ->leftJoin('asset_custodies', function($join) {
                $join->on('assets.id', '=', 'asset_custodies.asset_id')
                     ->where('asset_custodies.status', 'active');
            })
            ->leftJoin('personnel', 'asset_custodies.custodian_id', '=', 'personnel.id')
            ->select(
                'assets.*',
                'asset_categories.name as category_name',
                'offices.name as office_name',
                'personnel.full_name as custodian_name'
            )
            ->where('assets.asset_code', 'like', "%{$query}%")
            ->orWhere('assets.description', 'like', "%{$query}%")
            ->orWhere('assets.serial_number', 'like', "%{$query}%")
            ->orWhere('personnel.full_name', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($assets);
    }

    public function generateQR(int $assetId)
    {
        $asset = DB::table('assets')->where('id', $assetId)->first();

        if (!$asset) {
            return response()->json(['error' => 'Asset not found'], 404);
        }

        // QR code data (just the asset code)
        $qrData = $asset->asset_code;

        return response()->json([
            'qr_data' => $qrData,
            'asset_code' => $asset->asset_code,
            'description' => $asset->description
        ]);
    }

    private function logAudit(?int $assetId, string $actionType, string $module, ?string $oldValues, ?string $newValues)
    {
        $userId = Session::get('user_id', 1);

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
