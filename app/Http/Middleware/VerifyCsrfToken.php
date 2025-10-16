<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
        'api/register-mahasiswa',
        'api/register-kasir',
        'api/recent-registrations',
        'api/dashboard-stats',
        'api/recent-payments',
        'api/mahasiswa',
        'api/tarif',
        'api/payments',
        'api/mahasiswa-stats',
        'api/payment-distribution',
        'api/all-mahasiswa',
        'api/all-tarif',
        'api/all-payments',
        'api/mahasiswa-detail/*',
        'api/tarif/*',
        'api/payment-detail/*',
        'api/generate-report',
        'api/download-report/*',
        'api/report-history',
        'api/system-settings',
        'api/payment-settings',
        'api/admin-users',
        'api/system-info',
        'api/tagihan',
        'api/konfirmasi-pembayaran',
        'api/pembayaran',
    ];
}
