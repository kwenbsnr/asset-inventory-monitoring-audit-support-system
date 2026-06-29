<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ========== AUTHENTICATION ROUTES ==========
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string'
    ]);

    $user = DB::table('users')
        ->join('personnel', 'users.personnel_id', '=', 'personnel.id')
        ->where('users.username', $request->input('username'))
        ->where('users.is_active', 1)
        ->select('users.*', 'personnel.full_name')
        ->first();

    if ($user && Hash::check($request->input('password'), $user->password_hash)) {
        session([
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->full_name,
            'personnel_id' => $user->personnel_id
        ]);
        return redirect()->route('supply.dashboard');
    }

    return back()->with('error', 'Invalid username or password.');
})->name('login.post');

Route::get('/logout', function () {
    session()->flush();
    return redirect()->route('login');
})->name('logout');

// ========== PROTECTED ROUTES (Require Authentication) ==========
Route::middleware(['auth'])->group(function () {

    // Home / Dashboard
    Route::get('/', function () {
        return redirect()->route('supply.dashboard');
    })->name('home');

    // ========== SUPPLY OFFICER ROUTES ==========
    Route::prefix('supply')->name('supply.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [AssetController::class, 'dashboard'])->name('dashboard');

        // Assets
        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
        Route::get('/assets/{id}', [AssetController::class, 'show'])->name('assets.show');
        Route::get('/assets/{id}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('/assets/{id}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('/assets/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');

        // Transfers
        Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
        Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
        Route::post('/transfers/{id}/acknowledge', [TransferController::class, 'acknowledge'])->name('transfers.acknowledge');

        // QR Scanner
        Route::get('/qr-scanner', [QRController::class, 'index'])->name('qr.index');
        Route::post('/qr/scan', [QRController::class, 'scan'])->name('qr.scan');
        Route::get('/qr/search', [QRController::class, 'search'])->name('qr.search');
        Route::get('/qr/generate/{assetId}', [QRController::class, 'generateQR'])->name('qr.generate');

        // Audit Trail
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/export', [AuditController::class, 'export'])->name('audit.export');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

        // Personnel (Custodians)
        Route::get('/custodians', [PersonnelController::class, 'index'])->name('custodians.index');
        Route::post('/custodians', [PersonnelController::class, 'store'])->name('custodians.store');
        Route::put('/custodians/{id}', [PersonnelController::class, 'update'])->name('custodians.update');
        Route::delete('/custodians/{id}', [PersonnelController::class, 'destroy'])->name('custodians.destroy');
    });

    // ========== ADMIN ROUTES ==========
    Route::prefix('admin')->middleware(['admin'])->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Alias for dashboard (redirect to supply dashboard)
    Route::get('/dashboard', function () {
        return redirect()->route('supply.dashboard');
    })->name('dashboard');
});

// Fallback route for undefined routes
Route::fallback(function () {
    return redirect()->route('login');
});
