<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonnelController extends Controller
{
    public function index()
    {
        $personnel = DB::table('personnel')
            ->leftJoin('offices', 'personnel.office_id', '=', 'offices.office_id')
            ->select('personnel.*', 'offices.name as office_name')
            ->orderBy('personnel.full_name')
            ->get();

        $offices = DB::table('offices')->get();

        // Get asset count per custodian
        $custodianAssets = DB::table('asset_custodies')
            ->select('custodian_id', DB::raw('COUNT(*) as asset_count'))
            ->where('status', 'active')
            ->groupBy('custodian_id')
            ->get()
            ->pluck('asset_count', 'custodian_id')
            ->toArray();

        return view('supply.custodians', compact('personnel', 'offices', 'custodianAssets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:150',
            'employee_id' => 'required|string|max:30|unique:personnel,employee_id',
            'position' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'office_id' => 'nullable'
        ]);

        DB::table('personnel')->insert([
            'full_name' => $request->input('full_name'),
            'employee_id' => $request->input('employee_id'),
            'position' => $request->input('position'),
            'designation' => $request->input('designation'),
            'office_id' => $request->input('office_id') ?: 1,
            'is_active' => 1,
            'created_at' => now()
        ]);

        return redirect()->route('supply.custodians.index')->with('success', 'Personnel added successfully!');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:150',
            'position' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'office_id' => 'nullable',
            'is_active' => 'required|boolean'
        ]);

        DB::table('personnel')
            ->where('personnel_id', $id)
            ->orWhere('id', $id)
            ->update([
                'full_name' => $request->input('full_name'),
                'position' => $request->input('position'),
                'designation' => $request->input('designation'),
                'office_id' => $request->input('office_id') ?: 1,
                'is_active' => $request->input('is_active')
            ]);

        return redirect()->route('supply.custodians.index')->with('success', 'Personnel updated successfully!');
    }

    public function destroy(int $id)
    {
        DB::table('personnel')
            ->where('personnel_id', $id)
            ->orWhere('id', $id)
            ->delete();

        return redirect()->route('supply.custodians.index')->with('success', 'Personnel deleted successfully!');
    }
}
