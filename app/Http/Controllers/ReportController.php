<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $totalAssets = DB::table('assets')->count();
        $totalValue = DB::table('assets')->sum('acquisition_cost') ?? 0;

        $verifiedCount = DB::table('inventory_line_items')
            ->where('verified_status', 'verified')
            ->count();
        $totalLineItems = DB::table('inventory_line_items')->count();
        $accuracyRate = $totalLineItems > 0 ? round(($verifiedCount / $totalLineItems) * 100, 1) : 0;

        // Category breakdown
        $categoryBreakdown = DB::table('assets')
            ->leftJoin('asset_accounts', 'assets.asset_accounts_id', '=', 'asset_accounts.asset_accounts_id')
            ->select(
                DB::raw('COALESCE(asset_accounts.account_name, "Uncategorized") as category'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(assets.acquisition_cost) as total_value')
            )
            ->groupBy('asset_accounts.account_name')
            ->orderBy('count', 'desc')
            ->get();

        $offices = DB::table('offices')->orderBy('name')->get();

        return view('supply.reports', compact(
            'totalAssets', 'totalValue', 'accuracyRate', 'categoryBreakdown', 'offices'
        ));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'office_id' => 'nullable'
        ]);

        $reportType = $request->report_type;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $generatedAt = now()->format('F d, Y h:i A');

        $reportTitles = [
            'complete_inventory' => 'Report on the Physical Count of Property, Plant and Equipment (RPCPPE)',
            'by_category' => 'Inventory Summary by Asset Account / Classification',
            'by_location' => 'Asset Distribution by Office / Station Location',
            'custodian_assignment' => 'Property Acknowledgment Receipt (PAR) Custodian Summary',
            'transfer_history' => 'Property Transfer Report (PTR) Movements Log',
            'unverified' => 'Unverified and Pending Physical Inventory Audit Report',
            'discrepancies' => 'Physical Inventory Discrepancy & Finding Investigation Report',
            'audit_trail_report' => 'System Security & Transaction Audit Trail Log',
            'for_disposal' => 'Report of Unserviceable Property (IIRUP) / For Disposal',
        ];

        $reportTitle = $reportTitles[$reportType] ?? 'Asset Inventory Report';

        switch ($reportType) {
            case 'complete_inventory':
                $data = $this->getCompleteInventory($request);
                break;
            case 'by_category':
                $data = $this->getByCategory($request);
                break;
            case 'by_location':
                $data = $this->getByLocation($request);
                break;
            case 'custodian_assignment':
                $data = $this->getCustodianAssignment($request);
                break;
            case 'transfer_history':
                $data = $this->getTransferHistory($request);
                break;
            case 'unverified':
                $data = $this->getUnverifiedAssets($request);
                break;
            case 'discrepancies':
                $data = $this->getDiscrepancies($request);
                break;
            case 'audit_trail_report':
                $data = $this->getAuditTrailReport($request);
                break;
            case 'for_disposal':
                $data = $this->getForDisposal($request);
                break;
            default:
                $data = $this->getCompleteInventory($request);
        }

        return view('supply.report_preview', compact(
            'data', 'reportType', 'reportTitle', 'generatedAt', 'dateFrom', 'dateTo'
        ));
    }

    private function getCompleteInventory(Request $request)
    {
        $query = DB::table('assets')
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
                'personnel.full_name as custodian_name',
                'asset_custodies.property_number as par_number'
            );

        if ($request->office_id) {
            $query->where(function($q) use ($request) {
                $q->where('assets.office_id', $request->office_id)
                  ->orWhere('asset_custodies.office_id', $request->office_id);
            });
        }

        return $query->orderBy('assets.asset_code')->get();
    }

    private function getByCategory(Request $request)
    {
        return DB::table('assets')
            ->leftJoin('asset_accounts', 'assets.asset_accounts_id', '=', 'asset_accounts.asset_accounts_id')
            ->select(
                DB::raw('COALESCE(asset_accounts.account_name, "Unclassified") as category'),
                DB::raw('COALESCE(asset_accounts.account_code, "N/A") as account_code'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(assets.acquisition_cost) as total_value')
            )
            ->groupBy('asset_accounts.account_name', 'asset_accounts.account_code')
            ->get();
    }

    private function getByLocation(Request $request)
    {
        return DB::table('assets')
            ->leftJoin('asset_custodies', function($join) {
                $join->on('assets.asset_id', '=', 'asset_custodies.asset_id')
                     ->where('asset_custodies.status', 'active');
            })
            ->leftJoin('offices', function($join) {
                $join->on('asset_custodies.office_id', '=', 'offices.office_id')
                     ->orOn('assets.office_id', '=', 'offices.office_id');
            })
            ->select(
                DB::raw('COALESCE(offices.name, "Unassigned Station") as office'),
                DB::raw('COUNT(assets.asset_id) as count'),
                DB::raw('SUM(assets.acquisition_cost) as total_value')
            )
            ->groupBy('offices.name')
            ->get();
    }

    private function getCustodianAssignment(Request $request)
    {
        return DB::table('asset_custodies')
            ->join('assets', 'asset_custodies.asset_id', '=', 'assets.asset_id')
            ->leftJoin('personnel', 'asset_custodies.custodian_id', '=', 'personnel.personnel_id')
            ->leftJoin('offices', 'asset_custodies.office_id', '=', 'offices.office_id')
            ->select(
                'personnel.full_name as custodian',
                'offices.name as office',
                'asset_custodies.property_number as par_number',
                DB::raw('COUNT(assets.asset_id) as asset_count'),
                DB::raw('SUM(assets.acquisition_cost) as total_value')
            )
            ->where('asset_custodies.status', 'active')
            ->groupBy('personnel.full_name', 'offices.name', 'asset_custodies.property_number')
            ->get();
    }

    private function getTransferHistory(Request $request)
    {
        $query = DB::table('asset_transfers')
            ->leftJoin('assets', 'asset_transfers.asset_id', '=', 'assets.asset_id')
            ->leftJoin('personnel as from_p', 'asset_transfers.from_custodian_id', '=', 'from_p.personnel_id')
            ->leftJoin('personnel as to_p', 'asset_transfers.to_custodian_id', '=', 'to_p.personnel_id')
            ->leftJoin('offices as from_o', 'asset_transfers.from_office_id', '=', 'from_o.office_id')
            ->leftJoin('offices as to_o', 'asset_transfers.to_office_id', '=', 'to_o.office_id')
            ->select(
                'asset_transfers.*',
                'assets.asset_code',
                'assets.description',
                'from_p.full_name as from_custodian',
                'to_p.full_name as to_custodian',
                'from_o.name as from_office',
                'to_o.name as to_office'
            );

        if ($request->date_from) {
            $query->whereDate('asset_transfers.transfer_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('asset_transfers.transfer_date', '<=', $request->date_to);
        }

        return $query->orderBy('asset_transfers.transfer_date', 'desc')->limit(100)->get();
    }

    private function getUnverifiedAssets(Request $request)
    {
        return DB::table('assets')
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
            ->where(function($q) {
                $q->whereNull('assets.verification_status')
                  ->orWhere('assets.verification_status', 'pending');
            })
            ->get();
    }

    private function getDiscrepancies(Request $request)
    {
        return DB::table('inventory_line_items')
            ->leftJoin('assets', 'inventory_line_items.asset_id', '=', 'assets.asset_id')
            ->leftJoin('personnel', 'inventory_line_items.verified_by', '=', 'personnel.personnel_id')
            ->leftJoin('asset_accounts', 'assets.asset_accounts_id', '=', 'asset_accounts.asset_accounts_id')
            ->select(
                'inventory_line_items.*',
                'assets.asset_code',
                'assets.description',
                'asset_accounts.account_name as category_name',
                'personnel.full_name as verified_by_name'
            )
            ->where('inventory_line_items.verified_status', 'discrepancy')
            ->orderBy('inventory_line_items.verified_at', 'desc')
            ->get();
    }

    private function getAuditTrailReport(Request $request)
    {
        $query = DB::table('audit_trail')
            ->leftJoin('users', 'audit_trail.performed_by', '=', 'users.users_id')
            ->leftJoin('personnel', function($join) {
                $join->on('users.personnel_id', '=', 'personnel.personnel_id')
                     ->orOn('audit_trail.performed_by', '=', 'personnel.personnel_id');
            })
            ->leftJoin('assets', 'audit_trail.asset_id', '=', 'assets.asset_id')
            ->select(
                'audit_trail.*',
                DB::raw('COALESCE(personnel.full_name, users.username, "System") as user_name'),
                'assets.asset_code',
                'assets.description'
            );

        if ($request->date_from) {
            $query->whereDate('audit_trail.performed_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('audit_trail.performed_at', '<=', $request->date_to);
        }

        return $query->orderBy('audit_trail.performed_at', 'desc')
            ->limit(200)
            ->get();
    }

    private function getForDisposal(Request $request)
    {
        return DB::table('assets')
            ->leftJoin('asset_accounts', 'assets.asset_accounts_id', '=', 'asset_accounts.asset_accounts_id')
            ->leftJoin('offices', 'assets.office_id', '=', 'offices.office_id')
            ->select(
                'assets.*',
                'asset_accounts.account_name as category_name',
                'offices.name as office_name'
            )
            ->whereIn('assets.status', ['for_disposal', 'pending_disposal', 'disposed'])
            ->get();
    }
}
