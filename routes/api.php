<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- IMPORT SEMUA CONTROLLER ANDA DI SINI ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\TarifController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\VerifikasiController as KasirVerifikasiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| File ini adalah satu-satunya sumber kebenaran untuk semua endpoint API.
*/

// =====================================================================
// RUTE PUBLIK (TIDAK PERLU LOGIN)
// =====================================================================
Route::post('/login', [AuthController::class, 'login']);

// =====================================================================
// RUTE YANG DILINDUNGI (HARUS LOGIN DENGAN TOKEN)
// =====================================================================
Route::middleware('auth:sanctum')->group(function () {

    // Rute umum setelah login
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn(Request $request) => $request->user());

    // --- RUTE KHUSUS UNTUK ADMIN ---
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('dashboard/recent-payments', [DashboardController::class, 'recentPayments'])->name('dashboard.recentPayments');
        Route::get('dashboard/recent-registrations', [DashboardController::class, 'recentRegistrations'])->name('dashboard.recentRegistrations');

        Route::apiResource('mahasiswa', MahasiswaController::class);
        Route::post('users/kasir/register', [UserController::class, 'registerKasir'])->name('users.kasir.register');
        Route::get('users', [UserController::class, 'index'])->name('users.index');

        Route::apiResource('tarif', TarifController::class);

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/generate', [ReportController::class, 'store'])->name('reports.store');

        Route::apiResource('payments', PaymentController::class)->except(['update']);
        Route::post('payments/tagihan', [PaymentController::class, 'createTagihan'])->name('payments.tagihan.create');
        Route::get('tagihan', function() {
            return \App\Models\Tagihan::with('mahasiswa.user', 'tarif', 'pembayaran')
                ->latest()
                ->get();
        })->name('tagihan.index');

        Route::get('settings/system', [SettingController::class, 'getSystemSettings'])->name('settings.system.show');
        Route::post('settings/system', [SettingController::class, 'updateSystemSettings'])->name('settings.system.update');
        Route::get('system-info', [SettingController::class, 'getSystemInfo'])->name('system-info');
    });

    // --- RUTE KHUSUS UNTUK KASIR ---
    Route::middleware('role:kasir')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/dashboard-stats', [KasirDashboardController::class, 'getDashboardStats'])->name('dashboard-stats');
        Route::post('/search-mahasiswa', [KasirDashboardController::class, 'searchMahasiswa'])->name('search-mahasiswa');
        Route::post('/process-payment', [KasirDashboardController::class, 'processPayment'])->name('process-payment');
        Route::post('/verifikasi/approve/{konfirmasi}', [KasirVerifikasiController::class, 'approve'])->name('verifikasi.approve');
        Route::post('/verifikasi/reject/{konfirmasi}', [KasirVerifikasiController::class, 'reject'])->name('verifikasi.reject');
    });

    // --- RUTE KHUSUS UNTUK MAHASISWA ---
    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        // Contoh: Route::get('/tagihan', [MahasiswaApiController::class, 'index']);
    });
});