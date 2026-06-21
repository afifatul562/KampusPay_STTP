<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// Import Controller
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\TarifController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AcademicCalendarController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\VerifikasiController as KasirVerifikasiController;
use App\Http\Controllers\Kasir\AktivasiKasirController;
use App\Http\Controllers\Mahasiswa\AktivasiController as MahasiswaAktivasiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Mahasiswa\DashboardApiController;
use App\Http\Controllers\Api\Mahasiswa\DeviceTokenApiController;
use App\Http\Controllers\Api\Mahasiswa\KwitansiApiController;
use App\Http\Controllers\Api\Mahasiswa\LaporanApiController;
use App\Http\Controllers\Api\Mahasiswa\PembayaranApiController;
use App\Http\Controllers\Api\Mahasiswa\ProfilApiController;
use App\Http\Controllers\Api\Mahasiswa\RiwayatApiController;
use App\Http\Controllers\Api\Mahasiswa\TagihanApiController;
use App\Http\Controllers\Mahasiswa\NotificationController as MahasiswaNotificationController;
use App\Http\Controllers\Mahasiswa\WisudaController as MahasiswaWisudaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- RUTE KHUSUS UNTUK ADMIN ---
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Mahasiswa (CRUD via API)
        Route::apiResource('mahasiswa', MahasiswaController::class)->only(['index', 'show', 'destroy']); // Hanya index, show, destroy

        // User & Kasir
        Route::post('users/kasir/register', [UserController::class, 'registerKasir'])->name('users.kasir.register');
        Route::post('users/yayasan/register', [UserController::class, 'registerYayasan'])->name('users.yayasan.register');
        // Ganti nama route agar tidak bentrok dengan route web 'admin.users.index'
        Route::get('users', [UserController::class, 'index'])->name('users.api.index');

        // Tarif (CRUD via API)
        Route::apiResource('tarif', TarifController::class); // index, show, store, update, destroy

        // Kalender akademik (untuk rule UTS/UAS/awal semester)
        Route::get('academic-calendar', [AcademicCalendarController::class, 'index'])->name('academic-calendar.index');
        Route::post('academic-calendar', [AcademicCalendarController::class, 'upsert'])->name('academic-calendar.upsert');

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
        Route::post('/verifikasi/pending/{konfirmasi}', [KasirVerifikasiController::class, 'pending'])
            ->middleware('throttle:15,1')
            ->name('verifikasi.pending');

        Route::get('/notifications', [\App\Http\Controllers\Kasir\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Kasir\NotificationController::class, 'markAsRead'])->name('notifications.read');

        // Aktivasi mahasiswa
        Route::get('/aktivasi', [AktivasiKasirController::class, 'index'])->name('aktivasi.index');
        Route::post('/aktivasi/{aktivasi}/override', [AktivasiKasirController::class, 'override'])->name('aktivasi.override');
        Route::post('/aktivasi/mahasiswa/{mahasiswa}', [AktivasiKasirController::class, 'createFromMahasiswa'])->name('aktivasi.createFromMahasiswa');

        // Laporan Pembayaran
        Route::get('/reports/preview', [\App\Http\Controllers\Kasir\LaporanController::class, 'previewPembayaran'])
            ->middleware('throttle:20,1')
            ->name('reports.preview');
        Route::post('/reports/generate', [\App\Http\Controllers\Kasir\LaporanController::class, 'generatePembayaranPdf'])
            ->middleware('throttle:10,1')
            ->name('reports.generate');
        // Laporan Tunggakan
        Route::get('/reports/tunggakan/preview', [\App\Http\Controllers\Kasir\LaporanController::class, 'previewTunggakan'])
            ->middleware('throttle:20,1')
            ->name('reports.tunggakan.preview');
        Route::post('/reports/tunggakan/generate', [\App\Http\Controllers\Kasir\LaporanController::class, 'generateTunggakanPdf'])
            ->middleware('throttle:10,1')
            ->name('reports.tunggakan.generate');
        Route::get('/reports/riwayat', [\App\Http\Controllers\Kasir\LaporanController::class, 'indexRiwayat'])
            ->name('reports.riwayat');
        Route::get('/reports/{reportId}/download', [\App\Http\Controllers\Kasir\LaporanController::class, 'downloadRiwayat'])
            ->name('reports.download.api');
        Route::delete('/reports/{reportId}', [\App\Http\Controllers\Kasir\LaporanController::class, 'destroyRiwayat'])
            ->name('reports.destroy');

        // Tagihan & master data pendukung (kasir)
        Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
        Route::get('/tarif', [TarifController::class, 'index'])->name('tarif.index');
        Route::get('/tagihan', [PaymentController::class, 'indexTagihan'])->name('tagihan.index');
        Route::get('/tagihan/{id}', [PaymentController::class, 'showTagihan'])->name('tagihan.show');
        Route::post('/payments/tagihan', [PaymentController::class, 'createTagihan'])->name('payments.tagihan.create');
    });

    // --- RUTE KHUSUS UNTUK MAHASISWA ---
    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('/dashboard', DashboardApiController::class)->name('mobile.dashboard');
        Route::post('/device-token', [DeviceTokenApiController::class, 'store'])->name('mobile.device-token');
        Route::get('/tagihan', [TagihanApiController::class, 'index'])->name('mobile.tagihan.index');
        Route::get('/tagihan/{tagihan}', [TagihanApiController::class, 'show'])->name('mobile.tagihan.show');
        Route::post('/tagihan/{tagihan}/pilih-metode', [PembayaranApiController::class, 'pilihMetode'])->name('mobile.tagihan.pilih-metode');
        Route::post('/tagihan/{tagihan}/konfirmasi-transfer', [PembayaranApiController::class, 'konfirmasiTransfer'])->name('mobile.tagihan.konfirmasi-transfer');
        Route::post('/tagihan/{tagihan}/konfirmasi-cicilan', [PembayaranApiController::class, 'konfirmasiCicilan'])->name('mobile.tagihan.konfirmasi-cicilan');
        Route::get('/riwayat', [RiwayatApiController::class, 'index'])->name('mobile.riwayat');
        Route::get('/profil', [ProfilApiController::class, 'show'])->name('mobile.profil.show');
        Route::patch('/profil/email', [ProfilApiController::class, 'updateEmail'])->name('mobile.profil.email');
        Route::post('/profil/password', [ProfilApiController::class, 'updatePassword'])->name('mobile.profil.password');
        Route::get('/laporan', [LaporanApiController::class, 'ringkasan'])->name('mobile.laporan.ringkasan');
        Route::get('/laporan/histori/pdf', [LaporanApiController::class, 'historiPdf'])->name('mobile.laporan.histori.pdf');
        Route::get('/laporan/tunggakan/pdf', [LaporanApiController::class, 'tunggakanPdf'])->name('mobile.laporan.tunggakan.pdf');
        Route::get('/laporan/histori', [LaporanApiController::class, 'histori'])->name('mobile.laporan.histori');
        Route::get('/laporan/tunggakan', [LaporanApiController::class, 'tunggakan'])->name('mobile.laporan.tunggakan');
        Route::get('/kwitansi/{pembayaran}', [KwitansiApiController::class, 'download'])->name('mobile.kwitansi');

        Route::get('/aktivasi', [MahasiswaAktivasiController::class, 'current'])->name('aktivasi.current');
        Route::post('/aktivasi', [MahasiswaAktivasiController::class, 'store'])->name('aktivasi.store');

        Route::get('/wisuda', [MahasiswaWisudaController::class, 'current'])->name('mobile.wisuda.current');
        Route::post('/wisuda', [MahasiswaWisudaController::class, 'store'])->name('mobile.wisuda.store');

        Route::get('/notifications', [MahasiswaNotificationController::class, 'index'])->name('mobile.notifications.index');
        Route::post('/notifications/{id}/read', [MahasiswaNotificationController::class, 'markAsRead'])->name('mobile.notifications.read');
    });
});
