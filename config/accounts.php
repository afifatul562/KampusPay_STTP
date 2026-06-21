<?php

return [
    // Kosong/null = fallback ke NPM mahasiswa saat create/import.
    'mahasiswa_default_password' => env('MAHASISWA_DEFAULT_PASSWORD'),

    // Hanya dipakai jika admin tidak mengisi password saat daftar kasir.
    'kasir_default_password' => env('KASIR_DEFAULT_PASSWORD', 'kasirkampuspay'),

    'yayasan_default_password' => env('YAYASAN_DEFAULT_PASSWORD', 'yayasan123'),

    'import_kasir_username' => env('IMPORT_KASIR_USERNAME', 'kasir'),
];
