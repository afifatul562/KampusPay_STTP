<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Tagihan;
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
    }
}
