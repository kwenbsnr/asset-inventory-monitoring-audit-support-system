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
    if (session()->has('user_id')) {
        return redirect()->route('supply.dashboard');
    }
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string'
    ]);

    $user = DB::table('users')
        ->join('personnel', 'users.personnel_id', '=', 'personnel.personnel_id')
        ->where('users.username', $request->input('username'))
        ->where('users.is_active', 1)
        ->select('users.*', 'personnel.full_name')
        ->first();

    if ($user && Hash::check($request->input('password'), $user->password_hash)) {
        DB::table('users')->where('users_id', $user->users_id)->update(['last_login' => now()]);

        session([
            'user_id' => $user->users_id,
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

    // ========== BACKWARD-COMPATIBLE ROUTE ALIASES ==========
    Route::get('/dashboard', [AssetController::class, 'dashboard'])->name('dashboard');

    Route::name('assets.')->prefix('assets')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{id}', [AssetController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->name('destroy');
    });

    Route::name('transfers.')->prefix('transfers')->group(function () {
        Route::get('/', [TransferController::class, 'index'])->name('index');
        Route::post('/', [TransferController::class, 'store'])->name('store');
        Route::post('/{id}/acknowledge', [TransferController::class, 'acknowledge'])->name('acknowledge');
    });

    Route::name('qr.')->prefix('qr')->group(function () {
        Route::get('/', [QRController::class, 'index'])->name('index');
        Route::post('/scan', [QRController::class, 'scan'])->name('scan');
        Route::get('/search', [QRController::class, 'search'])->name('search');
        Route::get('/generate/{assetId}', [QRController::class, 'generateQR'])->name('generate');
    });

    Route::name('audit.')->prefix('audit')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/export', [AuditController::class, 'export'])->name('export');
    });

    Route::name('reports.')->prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
    });

    Route::name('custodians.')->prefix('custodians')->group(function () {
        Route::get('/', [PersonnelController::class, 'index'])->name('index');
        Route::post('/', [PersonnelController::class, 'store'])->name('store');
        Route::put('/{id}', [PersonnelController::class, 'update'])->name('update');
        Route::delete('/{id}', [PersonnelController::class, 'destroy'])->name('destroy');
    });

    // ========== ADMIN ROUTES ==========
    Route::prefix('admin')->middleware(['admin'])->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

// Fallback route for undefined routes
Route::fallback(function () {
    if (session()->has('user_id')) {
        return redirect()->route('supply.dashboard');
    }
    return redirect()->route('login');
});
