<?php

namespace App\Policies;

use App\Models\Tagihan;
use App\Models\User;

class TagihanPolicy
{
    /**
     * Menentukan apakah pengguna dapat membuat tagihan.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isKasir();
    }

    /**
     * Menentukan apakah pengguna dapat melihat tagihan.
     */
    public function view(User $user, Tagihan $tagihan): bool
    {
        if ($user->isAdmin() || $user->isKasir()) {
            return true;
        }
        if ($user->isMahasiswa() && $user->mahasiswaDetail) {
            // Beberapa driver/konfigurasi DB dapat mengembalikan numeric sebagai string,
            // sehingga perbandingan strict (===) bisa gagal walau nilainya sama.
            return (string) $user->mahasiswaDetail->mahasiswa_id === (string) $tagihan->mahasiswa_id;
        }
        return false;
    }

    /**
     * Menentukan apakah pengguna dapat memperbarui tagihan.
     */
    public function update(User $user, Tagihan $tagihan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna dapat menghapus tagihan.
     */
    public function delete(User $user, Tagihan $tagihan): bool
    {
        return $user->isAdmin();
    }
}


