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
        // Get all users with their personnel info
        $users = DB::table('users')
            ->join('personnel', 'users.personnel_id', '=', 'personnel.id')
            ->select('users.*', 'personnel.full_name', 'personnel.position', 'personnel.office_id')
            ->get();

        $personnel = DB::table('personnel')
            ->whereNotIn('id', function($query) {
                $query->select('personnel_id')->from('users')->whereNotNull('personnel_id');
            })
            ->get();

        return view('admin.users', compact('users', 'personnel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'personnel_id' => 'required|exists:personnel,id',
            'username' => 'required|string|max:80|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,supply_officer'
        ]);

        DB::table('users')->insert([
            'personnel_id' => $request->personnel_id,
            'username' => $request->username,
            'password_hash' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => 1,
            'created_at' => now()
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'role' => 'required|in:admin,supply_officer',
            'is_active' => 'required|boolean'
        ]);

        DB::table('users')
            ->where('id', $id)
            ->update([
                'role' => $request->role,
                'is_active' => $request->is_active,
                'updated_at' => now()
            ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(int $id)
    {
        DB::table('users')->where('id', $id)->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}
