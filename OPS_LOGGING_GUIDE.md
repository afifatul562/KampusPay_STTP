# Logging & Rotation Guide

## 1) Konfigurasi Laravel
- File `config/logging.php`
- Gunakan channel `stack` (default) dengan `single`/`daily` sesuai kebutuhan.

Contoh `daily` (rotasi otomatis harian):
```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'info'),
    'days' => 14, // simpan 14 hari
],
```

## 2) Log Rotation OS (alternatif/backup)
`/etc/logrotate.d/kampuspay`
```conf
/var/www/kampuspay/storage/logs/*.log {
  daily
  rotate 14
  compress
  missingok
  notifempty
  copytruncate
}
```

## 3) Redaksi Data Sensitif
- Jangan log password/token.
- Saat menangkap exception, sertakan konteks non-sensitif saja (ID user, ID record, request id).

Contoh:
```php
Log::error('Gagal approve pembayaran', [
    'pembayaran_id' => $pembayaran->pembayaran_id ?? null,
    'kasir_id' => Auth::id(),
    'error' => $e->getMessage(),
]);
```

## 4) Rekomendasi
- Set `LOG_LEVEL=info` (prod), `debug` hanya untuk dev.
- Gunakan Sentry/Rollbar untuk error monitoring.
- Audit log akses sensitif: download laporan, approve/reject, hapus data.


