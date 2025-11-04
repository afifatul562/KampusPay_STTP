<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Import Controller
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\TarifController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PaymentController; // <-- Pastikan ini diimport
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\VerifikasiController as KasirVerifikasiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rute Publik (jika ada)
// Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    // Route::post('/logout', [AuthController::class, 'logout']);

    // --- RUTE KHUSUS UNTUK ADMIN ---
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('dashboard/recent-payments', [DashboardController::class, 'recentPayments'])->name('dashboard.recentPayments');
        Route::get('dashboard/recent-registrations', [DashboardController::class, 'recentRegistrations'])->name('dashboard.recentRegistrations');

        // Mahasiswa (CRUD via API)
        Route::apiResource('mahasiswa', MahasiswaController::class)->only(['index', 'show', 'destroy']); // Hanya index, show, destroy

        // User & Kasir
        Route::post('users/kasir/register', [UserController::class, 'registerKasir'])->name('users.kasir.register');
        // Ganti nama route agar tidak bentrok dengan route web 'admin.users.index'
        Route::get('users', [UserController::class, 'index'])->name('users.api.index');

        // Tarif (CRUD via API)
        Route::apiResource('tarif', TarifController::class); // index, show, store, update, destroy

        // Report (rate limited)
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/generate', [ReportController::class, 'store'])
            ->middleware('throttle:10,1') // max 10/min per user
            ->name('reports.store');
        Route::post('reports/preview', [ReportController::class, 'preview']) // Preview Data
            ->middleware('throttle:20,1') // lebih longgar dari generate
            ->name('reports.preview');
        Route::delete('reports/{report}', [ReportController::class, 'destroy'])
            ->middleware('throttle:10,1')
            ->name('reports.destroy');

        // Pembayaran (Hanya lihat data pembayaran yg sudah ada)
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{id}', [PaymentController::class, 'show'])->name('payments.show');

        // TAGIHAN (CRUD via API, dihandle oleh PaymentController)
        Route::get('tagihan', [PaymentController::class, 'indexTagihan'])->name('tagihan.index');        // GET /api/admin/tagihan
        Route::get('tagihan/{id}', [PaymentController::class, 'showTagihan'])->name('tagihan.show');          // GET /api/admin/tagihan/{id}
        Route::post('payments/tagihan', [PaymentController::class, 'createTagihan'])->name('payments.tagihan.create'); // POST /api/admin/payments/tagihan (URL create tetap sama)
        Route::put('tagihan/{id}', [PaymentController::class, 'updateTagihan'])->name('tagihan.update');        // PUT /api/admin/tagihan/{id}
        Route::delete('tagihan/{id}', [PaymentController::class, 'destroyTagihan'])->name('tagihan.destroy');    // DELETE /api/admin/tagihan/{id}

        // Settings
        Route::get('settings/system', [SettingController::class, 'getSystemSettings'])->name('settings.system.show');
        Route::post('settings/system', [SettingController::class, 'updateSystemSettings'])->name('settings.system.update');
        Route::get('system-info', [SettingController::class, 'getSystemInfo'])->name('system-info');
    });

    // --- RUTE KHUSUS UNTUK KASIR ---
    Route::middleware('role:kasir')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/dashboard-stats', [KasirDashboardController::class, 'getDashboardStats'])->name('dashboard-stats');
        Route::post('/search-mahasiswa', [KasirDashboardController::class, 'searchMahasiswa'])
            ->middleware('throttle:30,1')
            ->name('search-mahasiswa');
        Route::post('/process-payment', [KasirDashboardController::class, 'processPayment'])
            ->middleware('throttle:10,1')
            ->name('process-payment');
        Route::post('/verifikasi/approve/{konfirmasi}', [KasirVerifikasiController::class, 'approve'])
            ->middleware('throttle:15,1')
            ->name('verifikasi.approve');
        Route::post('/verifikasi/reject/{konfirmasi}', [KasirVerifikasiController::class, 'reject'])
            ->middleware('throttle:15,1')
            ->name('verifikasi.reject');
    });

    // --- RUTE KHUSUS UNTUK MAHASISWA ---
    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        // Contoh: Route::get('/tagihan-saya', [MahasiswaApiController::class, 'tagihanSaya']);
    });
});
