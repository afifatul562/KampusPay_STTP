<?php

return [
    // Label semester aktif, contoh: "2025/2026 Ganjil"
    'current_semester' => env('CURRENT_SEMESTER', '2025/2026 Ganjil'),

    // Tarif BSS default (dapat diubah via env)
    'bss_amount' => (int) env('BSS_AMOUNT', 200000),

    // Jarak jatuh tempo BSS dalam hari
    'bss_due_in_days' => (int) env('BSS_DUE_IN_DAYS', 14),

    // Nama tarif BSS (akan dibuat jika belum ada)
    'bss_tarif_name' => env('BSS_TARIF_NAME', 'Administrasi BSS'),
];

