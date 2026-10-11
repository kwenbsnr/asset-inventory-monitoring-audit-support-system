<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class TransferController extends Controller
{
    public function index()
    {
        $pendingTransfers = DB::table('asset_transfers')
            ->leftJoin('assets', 'asset_transfers.asset_id', '=', 'assets.asset_id')
            ->leftJoin('personnel as from_p', 'asset_transfers.from_custodian_id', '=', 'from_p.personnel_id')
            ->leftJoin('personnel as to_p', 'asset_transfers.to_custodian_id', '=', 'to_p.personnel_id')
            ->leftJoin('offices as from_o', 'asset_transfers.from_office_id', '=', 'from_o.office_id')
            ->leftJoin('offices as to_o', 'asset_transfers.to_office_id', '=', 'to_o.office_id')
            ->leftJoin('personnel as approver', 'asset_transfers.approved_by', '=', 'approver.personnel_id')
            ->select(
                'asset_transfers.*',
                'assets.asset_code',
                'assets.description',
                'from_p.full_name as from_custodian_name',
                'to_p.full_name as to_custodian_name',
                'from_o.name as from_office_name',
                'to_o.name as to_office_name',
                'approver.full_name as approver_name'
            )
            ->whereIn('asset_transfers.status', ['pending', 'approved'])
            ->orderBy('asset_transfers.created_at', 'desc')
            ->get();

        $completedTransfers = DB::table('asset_transfers')
            ->leftJoin('assets', 'asset_transfers.asset_id', '=', 'assets.asset_id')
            ->leftJoin('personnel as from_p', 'asset_transfers.from_custodian_id', '=', 'from_p.personnel_id')
            ->leftJoin('personnel as to_p', 'asset_transfers.to_custodian_id', '=', 'to_p.personnel_id')
            ->leftJoin('offices as from_o', 'asset_transfers.from_office_id', '=', 'from_o.office_id')
            ->leftJoin('offices as to_o', 'asset_transfers.to_office_id', '=', 'to_o.office_id')
            ->select(
                'asset_transfers.*',
                'assets.asset_code',
                'assets.description',
                'from_p.full_name as from_custodian_name',
                'to_p.full_name as to_custodian_name',
                'from_o.name as from_office_name',
                'to_o.name as to_office_name'
            )
            ->where('asset_transfers.status', 'acknowledged')
            ->orderBy('asset_transfers.created_at', 'desc')
            ->get();

        $allTransfers = DB::table('asset_transfers')
            ->leftJoin('assets', 'asset_transfers.asset_id', '=', 'assets.asset_id')
            ->leftJoin('personnel as from_p', 'asset_transfers.from_custodian_id', '=', 'from_p.personnel_id')
            ->leftJoin('personnel as to_p', 'asset_transfers.to_custodian_id', '=', 'to_p.personnel_id')
            ->leftJoin('offices as from_o', 'asset_transfers.from_office_id', '=', 'from_o.office_id')
            ->leftJoin('offices as to_o', 'asset_transfers.to_office_id', '=', 'to_o.office_id')
            ->select(
                'asset_transfers.*',
                'assets.asset_code',
                'assets.description',
                'from_p.full_name as from_custodian_name',
                'to_p.full_name as to_custodian_name',
                'from_o.name as from_office_name',
                'to_o.name as to_office_name'
            )
            ->orderBy('asset_transfers.created_at', 'desc')
            ->limit(50)
            ->get();

        // For the form dropdowns
        $assets = DB::table('assets')->where('status', 'active')->orderBy('asset_code')->get();
        $personnel = DB::table('personnel')->where('is_active', 1)->orderBy('full_name')->get();
        $offices = DB::table('offices')->orderBy('name')->get();

        return view('supply.transfers', compact(
            'pendingTransfers', 'completedTransfers', 'allTransfers',
            'assets', 'personnel', 'offices'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required',
            'to_custodian_id' => 'required',
            'to_office_id' => 'required',
            'transfer_date' => 'required|date',
            'purpose' => 'nullable|string|max:255'
        ]);

        $assetId = $request->input('asset_id');

        // Get current active custody
        $currentCustody = DB::table('asset_custodies')
            ->where('asset_id', $assetId)
            ->where('status', 'active')
            ->first();

        // Generate transfer number
        $lastTransfer = DB::table('asset_transfers')->orderBy('id', 'desc')->first();
        $nextId = $lastTransfer ? ($lastTransfer->id + 1) : 1;
        $transferNumber = 'TR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Insert transfer
        $transferId = DB::table('asset_transfers')->insertGetId([
            'asset_id' => $assetId,
            'from_custodian_id' => $currentCustody ? $currentCustody->custodian_id : null,
            'to_custodian_id' => $request->input('to_custodian_id'),
            'from_office_id' => $currentCustody ? $currentCustody->office_id : null,
            'to_office_id' => $request->input('to_office_id'),
            'transfer_number' => $transferNumber,
            'purpose' => $request->input('purpose'),
            'transfer_date' => $request->input('transfer_date'),
            'approved_by' => Session::get('personnel_id', 1),
            'status' => 'approved',
            'remarks' => $request->input('purpose'),
            'created_at' => now()
        ]);

        // Update asset status
        DB::table('assets')
            ->where('asset_id', $assetId)
            ->orWhere('id', $assetId)
            ->update([
                'status' => 'transferred',
                'updated_at' => now()
            ]);

        // Log to audit trail
        $this->logAudit($assetId, 'transfer', 'Transfer initiated', null, json_encode($request->all()));

        return redirect()->route('supply.transfers.index')->with('success', 'Transfer request created successfully!');
    }

    public function acknowledge(int $id)
    {
        $transfer = DB::table('asset_transfers')->where('id', $id)->first();

        if (!$transfer) {
            return redirect()->route('supply.transfers.index')->with('error', 'Transfer record not found.');
        }

        DB::table('asset_transfers')
            ->where('id', $id)
            ->update([
                'status' => 'acknowledged',
                'acknowledged_at' => now()
            ]);

        // End current custody
        DB::table('asset_custodies')
            ->where('asset_id', $transfer->asset_id)
            ->where('status', 'active')
            ->update([
                'end_date' => now()->toDateString(),
                'status' => 'transferred'
            ]);

        // Start new custody
        $propNum = 'PAR-' . date('Y') . '-' . str_pad($transfer->id, 3, '0', STR_PAD_LEFT);
        DB::table('asset_custodies')->insert([
            'asset_id' => $transfer->asset_id,
            'custodian_id' => $transfer->to_custodian_id,
            'office_id' => $transfer->to_office_id,
            'effectivity_date' => now()->toDateString(),
            'property_number' => $propNum,
            'status' => 'active',
            'created_at' => now()
        ]);

        // Update asset location/office and restore status to active
        DB::table('assets')
            ->where('asset_id', $transfer->asset_id)
            ->orWhere('id', $transfer->asset_id)
            ->update([
                'office_id' => $transfer->to_office_id,
                'status' => 'active',
                'updated_at' => now()
            ]);

        $this->logAudit($transfer->asset_id, 'transfer', 'Transfer acknowledged', null, json_encode(['transfer_id' => $id]));

        return redirect()->route('supply.transfers.index')->with('success', 'Transfer acknowledged successfully! Asset custody updated.');
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
