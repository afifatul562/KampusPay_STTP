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
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrasi kebijakan (policies)
        Gate::policy(Tagihan::class, TagihanPolicy::class);
        Gate::policy(Pembayaran::class, PembayaranPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);

        // Set locale tanggal Indonesia secara global (Carbon & aplikasi)
        try {
            Carbon::setLocale('id');
            if (function_exists('setlocale')) {
                @setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'id');
            }
            if (App::getLocale() !== 'id') {
                App::setLocale('id');
            }
        } catch (\Throwable $e) {
            // ignore locale errors
        }
    }
}
