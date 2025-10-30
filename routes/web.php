<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MahasiswaController; // Dulu 'as AdminMahasiswaController', kita samakan saja
use App\Http\Controllers\Admin\TarifController as AdminTarifController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\TransaksiController as KasirTransaksiController;
use App\Http\Controllers\Kasir\LaporanController as KasirLaporanController;
use App\Http\Controllers\Kasir\VerifikasiController as KasirVerifikasiController;
use App\Http\Controllers\Kasir\PengaturanController as KasirPengaturanController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\Mahasiswa\PembayaranController as MahasiswaPembayaranController;
use App\Http\Controllers\Mahasiswa\RiwayatController as MahasiswaRiwayatController;
use App\Http\Controllers\Mahasiswa\KwitansiController;
use App\Http\Controllers\Mahasiswa\ProfilController as MahasiswaProfilController;
use App\Http\Controllers\Mahasiswa\LaporanController as MahasiswaLaporanController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        $user = Auth::user();

        // Ini akan error jika fungsi di User.php belum ada
        if ($user->isAdmin()) return redirect()->route('admin.dashboard');
        if ($user->isKasir()) return redirect()->route('kasir.dashboard');
        if ($user->isMahasiswa()) return redirect()->route('mahasiswa.dashboard');

        Auth::logout();
        return redirect('/login')->withErrors('Role tidak dikenali.');
    })->name('dashboard');

    // Rute Halaman Profil bawaan Laravel
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- RUTE ADMIN ---
    Route::middleware(\App\Http\Middleware\CheckRole::class . ':admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('/dashboard/recent-payments', [AdminDashboardController::class, 'recentPayments'])->name('dashboard.recentPayments');
        Route::get('/dashboard/recent-registrations', [AdminDashboardController::class, 'recentRegistrations'])->name('dashboard.recentRegistrations');
        Route::get('/mahasiswa', fn() => view('admin.mahasiswa'))->name('mahasiswa');

        // DIPERBAIKI: Gunakan 'MahasiswaController' yang sudah di-import
        Route::get('/mahasiswa/create', [MahasiswaController::class, 'create'])->name('create-mahasiswa');
        Route::post('/mahasiswa', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
        Route::post('/mahasiswa/import', [MahasiswaController::class, 'import'])->name('mahasiswa.import');

        // DIPERBAIKI: Hapus prefix '/admin/' dan 'admin.' karena sudah ada di grup
        Route::get('mahasiswa/{id}/edit', [MahasiswaController::class, 'edit'])->name('mahasiswa.edit');
        Route::put('mahasiswa/{id}', [MahasiswaController::class, 'update'])->name('mahasiswa.update');

        Route::get('/pembayaran', fn() => view('admin.pembayaran'))->name('pembayaran');
        Route::get('/pembayaran/print/{pembayaran}', [\App\Http\Controllers\Admin\PrintController::class, 'cetakBuktiPembayaran'])->name('pembayaran.print');
        Route::get('/tarif', fn() => view('admin.tarif'))->name('tarif');
        Route::get('/laporan', fn() => view('admin.laporan'))->name('laporan');
        Route::get('/pengaturan', fn() => view('admin.pengaturan'))->name('pengaturan');
        Route::get('/registrasi', fn() => view('admin.registrasi'))->name('registrasi');
        Route::get('/reports/download/{report}', [AdminReportController::class, 'download'])->name('reports.download');
        Route::resource('users', App\Http\Controllers\Admin\UserManagementController::class);
    });

    // --- RUTE KASIR ---
    Route::middleware(\App\Http\Middleware\CheckRole::class . ':kasir')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/dashboard', fn() => view('kasir.dashboard'))->name('dashboard');
        Route::get('/transaksi', [KasirTransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/export', [KasirTransaksiController::class, 'export'])->name('transaksi.export');
        Route::get('/laporan', [KasirLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/verifikasi', [KasirVerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/pengaturan', [KasirPengaturanController::class, 'showPasswordForm'])->name('pengaturan.password');
        Route::put('/pengaturan', [KasirPengaturanController::class, 'updatePassword'])->name('pengaturan.updatePassword');
    });

    // --- RUTE MAHASISWA ---
    Route::middleware(\App\Http\Middleware\CheckRole::class . ':mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pembayaran', [MahasiswaPembayaranController::class, 'index'])->name('pembayaran.index');
        Route::get('/pembayaran/{tagihan}/pilih-metode', [MahasiswaPembayaranController::class, 'pilihMetode'])->name('pembayaran.pilih-metode');
        Route::post('/pembayaran/{tagihan}/proses-metode', [MahasiswaPembayaranController::class, 'prosesMetode'])->name('pembayaran.proses-metode');
        Route::get('/pembayaran/{tagihan}', [MahasiswaPembayaranController::class, 'show'])->name('pembayaran.show');
        Route::post('/pembayaran/{tagihan}/konfirmasi', [MahasiswaPembayaranController::class, 'storeKonfirmasi'])->name('pembayaran.konfirmasi.store');
        Route::get('/riwayat', [MahasiswaRiwayatController::class, 'index'])->name('riwayat.index');
        Route::get('/kwitansi/{pembayaran}/download', [KwitansiController::class, 'download'])->name('kwitansi.download');

        Route::get('/laporan', [MahasiswaLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/histori/download', [MahasiswaLaporanController::class, 'downloadHistori'])->name('laporan.histori.download');
        Route::get('/laporan/tunggakan/download', [MahasiswaLaporanController::class, 'downloadTunggakan'])->name('laporan.tunggakan.download');

        // Rute Profil & Ubah Password yang baru (ini dipertahankan)
        Route::get('/profil', [MahasiswaProfilController::class, 'index'])->name('profil');
        Route::get('/profil/password', [MahasiswaProfilController::class, 'editPassword'])->name('profil.password.edit');
        Route::post('/profil/password', [MahasiswaProfilController::class, 'updatePassword'])->name('profil.password.update');
    });
});

require __DIR__.'/auth.php';
