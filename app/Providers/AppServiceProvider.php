<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Tagihan;
use App\Observers\TagihanObserver;
use App\Models\Pembayaran;
use App\Models\Report;
use App\Policies\TagihanPolicy;
use App\Policies\PembayaranPolicy;
use App\Policies\ReportPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Mendaftarkan layanan aplikasi.
     */
    public function register(): void
    {
        //
    }

    /**
     * Mem-bootstrap layanan aplikasi.
     */
    public function boot(): void
    {
        Tagihan::observe(TagihanObserver::class);

        Gate::policy(Tagihan::class, TagihanPolicy::class);
        Gate::policy(Pembayaran::class, PembayaranPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);

        try {
            Carbon::setLocale('id');
            if (function_exists('setlocale')) {
                @setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'id');
            }
            if (App::getLocale() !== 'id') {
                App::setLocale('id');
            }
        } catch (\Throwable $e) {
        }

        // Generate API token untuk semua view yang butuh (admin/kasir/mahasiswa)
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                if ($user && ($user->isAdmin() || $user->isKasir() || $user->isMahasiswa()) && !session('api_token')) {
                    // Hapus token lama dengan nama yang sama
                    $user->tokens()->where('name', 'api-token')->delete();
                    // Generate token baru
                    $token = $user->createToken('api-token')->plainTextToken;
                    session(['api_token' => $token]);
                }
            }
        });
    }
}
