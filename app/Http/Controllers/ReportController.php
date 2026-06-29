<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $totalAssets = DB::table('assets')->count();
        $totalValue = DB::table('assets')->sum('acquisition_cost');

        $verifiedCount = DB::table('inventory_line_items')
            ->where('verified_status', 'verified')
            ->count();
        $totalLineItems = DB::table('inventory_line_items')->count();
        $accuracyRate = $totalLineItems > 0 ? round(($verifiedCount / $totalLineItems) * 100, 1) : 0;

        // Category breakdown
        $categoryBreakdown = DB::table('assets')
            ->leftJoin('asset_categories', 'assets.category_id', '=', 'asset_categories.id')
            ->select('asset_categories.name as category', DB::raw('COUNT(*) as count'))
            ->groupBy('asset_categories.name')
            ->get();

        return view('supply.reports', compact(
            'totalAssets', 'totalValue', 'accuracyRate', 'categoryBreakdown'
        ));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'office_id' => 'nullable|exists:offices,id'
        ]);

        // Get data based on report type
        $data = [];
        $reportType = $request->report_type;

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

        // Generate PDF (simplified - we'll just return a view for now)
        return view('supply.report_preview', compact('data', 'reportType'));
    }

    private function getCompleteInventory(Request $request)
    {
        $query = DB::table('assets')
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
                'personnel.full_name as custodian_name',
                'asset_custodies.par_number'
            );

        if ($request->office_id) {
            $query->where('assets.office_id', $request->office_id);
        }

        return $query->orderBy('assets.asset_code')->get();
    }

    private function getByCategory(Request $request)
    {
        return DB::table('assets')
            ->leftJoin('asset_categories', 'assets.category_id', '=', 'asset_categories.id')
            ->select(
                'asset_categories.name as category',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(acquisition_cost) as total_value')
            )
            ->groupBy('asset_categories.name')
            ->get();
    }

    private function getByLocation(Request $request)
    {
        return DB::table('assets')
            ->leftJoin('offices', 'assets.office_id', '=', 'offices.id')
            ->select(
                'offices.name as office',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(acquisition_cost) as total_value')
            )
            ->groupBy('offices.name')
            ->get();
    }

    private function getCustodianAssignment(Request $request)
    {
        return DB::table('asset_custodies')
            ->join('assets', 'asset_custodies.asset_id', '=', 'assets.id')
            ->join('personnel', 'asset_custodies.custodian_id', '=', 'personnel.id')
            ->join('offices', 'asset_custodies.office_id', '=', 'offices.id')
            ->select(
                'personnel.full_name as custodian',
                'offices.name as office',
                DB::raw('COUNT(*) as asset_count'),
                DB::raw('SUM(assets.acquisition_cost) as total_value')
            )
            ->where('asset_custodies.status', 'active')
            ->groupBy('personnel.full_name', 'offices.name')
            ->get();
    }

    private function getTransferHistory(Request $request)
    {
        return DB::table('asset_transfers')
            ->join('assets', 'asset_transfers.asset_id', '=', 'assets.id')
            ->leftJoin('personnel as from_p', 'asset_transfers.from_custodian_id', '=', 'from_p.id')
            ->leftJoin('personnel as to_p', 'asset_transfers.to_custodian_id', '=', 'to_p.id')
            ->leftJoin('offices as from_o', 'asset_transfers.from_office_id', '=', 'from_o.id')
            ->leftJoin('offices as to_o', 'asset_transfers.to_office_id', '=', 'to_o.id')
            ->select(
                'asset_transfers.*',
                'assets.asset_code',
                'assets.description',
                'from_p.full_name as from_custodian',
                'to_p.full_name as to_custodian',
                'from_o.name as from_office',
                'to_o.name as to_office'
            )
            ->orderBy('asset_transfers.created_at', 'desc')
            ->limit(100)
            ->get();
    }

    private function getUnverifiedAssets(Request $request)
    {
        return DB::table('assets')
            ->leftJoin('asset_categories', 'assets.category_id', '=', 'asset_categories.id')
            ->leftJoin('offices', 'assets.office_id', '=', 'offices.id')
            ->leftJoin('asset_custodies', function($join) {
                $join->on('assets.id', '=', 'asset_custodies.asset_id')
                     ->where('asset_custodies.status', 'active');
            })
            ->leftJoin('personnel', 'asset_custodies.custodian_id', '=', 'personnel.id')
            ->leftJoin('inventory_line_items', function($join) {
                $join->on('assets.id', '=', 'inventory_line_items.asset_id')
                     ->where('inventory_line_items.verified_status', 'pending');
            })
            ->select(
                'assets.*',
                'asset_categories.name as category_name',
                'offices.name as office_name',
                'personnel.full_name as custodian_name'
            )
            ->whereNull('inventory_line_items.id')
            ->get();
    }

    private function getDiscrepancies(Request $request)
    {
        return DB::table('inventory_line_items')
            ->join('assets', 'inventory_line_items.asset_id', '=', 'assets.id')
            ->join('personnel', 'inventory_line_items.verified_by', '=', 'personnel.id')
            ->leftJoin('asset_categories', 'assets.category_id', '=', 'asset_categories.id')
            ->select(
                'inventory_line_items.*',
                'assets.asset_code',
                'assets.description',
                'asset_categories.name as category_name',
                'personnel.full_name as verified_by_name'
            )
            ->where('inventory_line_items.verified_status', 'discrepancy')
            ->orderBy('inventory_line_items.verified_at', 'desc')
            ->get();
    }

    private function getAuditTrailReport(Request $request)
    {
        $query = DB::table('audit_trail')
            ->join('personnel', 'audit_trail.performed_by', '=', 'personnel.id')
            ->leftJoin('assets', 'audit_trail.asset_id', '=', 'assets.id')
            ->select(
                'audit_trail.*',
                'personnel.full_name as user_name',
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
            ->leftJoin('asset_categories', 'assets.category_id', '=', 'asset_categories.id')
            ->leftJoin('offices', 'assets.office_id', '=', 'offices.id')
            ->select(
                'assets.*',
                'asset_categories.name as category_name',
                'offices.name as office_name'
            )
            ->where('assets.status', 'for_disposal')
            ->get();
    }
}
