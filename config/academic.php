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

    // Jarak jatuh tempo tagihan normal dalam hari (fallback jika kalender UAS belum diisi)
    'default_due_in_days' => (int) env('DEFAULT_DUE_IN_DAYS', 30),

    // Jatuh tempo tagihan rutin = UAS dikurangi N hari (H-N sebelum UAS)
    'uas_due_offset_days' => (int) env('UAS_DUE_OFFSET_DAYS', 7),

    // Pengingat FCM sebelum jatuh tempo (Fase 3)
    'reminder_days_before_due' => [7, 3, 1, 0],

    // Jatuh tempo Uang Wisuda setelah submit form daftar wisuda (H+N hari)
    'wisuda_due_in_days' => (int) env('WISUDA_DUE_IN_DAYS', 30),
];

