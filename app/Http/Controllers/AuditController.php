<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index(Request $request)
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

        // Filter by action type
        if ($request->has('action_type') && $request->action_type) {
            $query->where('audit_trail.action_type', $request->action_type);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('audit_trail.performed_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('audit_trail.performed_at', '<=', $request->to_date);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('assets.asset_code', 'like', "%{$search}%")
                  ->orWhere('audit_trail.action_type', 'like', "%{$search}%")
                  ->orWhere('personnel.full_name', 'like', "%{$search}%");
            });
        }

        $auditLogs = $query->orderBy('audit_trail.performed_at', 'desc')
            ->limit(100)
            ->get();

        // Get distinct action types for filter
        $actionTypes = DB::table('audit_trail')
            ->select('action_type')
            ->distinct()
            ->pluck('action_type');

        return view('supply.audit', compact('auditLogs', 'actionTypes'));
    }

    public function export()
    {
        $logs = DB::table('audit_trail')
            ->join('personnel', 'audit_trail.performed_by', '=', 'personnel.id')
            ->leftJoin('assets', 'audit_trail.asset_id', '=', 'assets.id')
            ->select(
                'audit_trail.performed_at as date',
                'personnel.full_name as user',
                'audit_trail.action_type as action',
                'audit_trail.module as module',
                'assets.asset_code as asset_code',
                'assets.description as asset_description'
            )
            ->orderBy('audit_trail.performed_at', 'desc')
            ->limit(1000)
            ->get();

        // Create CSV
        $filename = 'audit_trail_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'w+');

        // Add headers
        fputcsv($handle, ['Date', 'User', 'Action', 'Module', 'Asset Code', 'Asset Description']);

        // Add data
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->date,
                $log->user,
                $log->action,
                $log->module,
                $log->asset_code,
                $log->asset_description
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}
