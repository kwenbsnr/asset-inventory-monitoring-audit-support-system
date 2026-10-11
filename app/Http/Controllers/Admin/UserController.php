<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = DB::table('users')
            ->leftJoin('personnel', 'users.personnel_id', '=', 'personnel.personnel_id')
            ->select('users.*', 'personnel.full_name', 'personnel.position', 'personnel.office_id')
            ->get();

        $personnel = DB::table('personnel')
            ->where('is_active', 1)
            ->orderBy('full_name')
            ->get();

        return view('admin.users', compact('users', 'personnel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'personnel_id' => 'required',
            'username' => 'required|string|max:80|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required'
        ]);

        $role = $request->input('role');
        if ($role === 'supply_officer') {
            $role = 'asset_manager';
        }

        DB::table('users')->insert([
            'personnel_id' => $request->input('personnel_id'),
            'username' => $request->input('username'),
            'password_hash' => Hash::make($request->input('password')),
            'role' => $role,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'role' => 'required',
            'is_active' => 'required|boolean'
        ]);

        $role = $request->input('role');
        if ($role === 'supply_officer') {
            $role = 'asset_manager';
        }

        DB::table('users')
            ->where('users_id', $id)
            ->orWhere('id', $id)
            ->update([
                'role' => $role,
                'is_active' => $request->input('is_active'),
                'updated_at' => now()
            ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(int $id)
    {
        DB::table('users')
            ->where('users_id', $id)
            ->orWhere('id', $id)
            ->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}
