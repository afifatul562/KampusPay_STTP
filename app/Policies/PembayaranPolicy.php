<?php

namespace App\Policies;

use App\Models\Pembayaran;
use App\Models\User;

class PembayaranPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat pembayaran.
     */
    public function view(User $user, Pembayaran $pembayaran): bool
    {
        if ($user->isAdmin() || $user->isKasir()) {
            return true;
        }
        if ($user->isMahasiswa() && $user->mahasiswaDetail && $pembayaran->tagihan) {
            // Beberapa driver/konfigurasi DB dapat mengembalikan numeric sebagai string,
            // sehingga perbandingan strict (===) bisa gagal walau nilainya sama.
            return (string) $user->mahasiswaDetail->mahasiswa_id === (string) $pembayaran->tagihan->mahasiswa_id;
        }
        return false;
    }
}


