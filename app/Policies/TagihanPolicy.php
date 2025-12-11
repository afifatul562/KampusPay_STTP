<?php

namespace App\Policies;

use App\Models\Tagihan;
use App\Models\User;

class TagihanPolicy
{
    public function create(User $user): bool
    {
        // Admin dan kasir boleh membuat tagihan
        return $user->isAdmin() || $user->isKasir();
    }

    public function view(User $user, Tagihan $tagihan): bool
    {
        if ($user->isAdmin() || $user->isKasir()) {
            return true;
        }
        if ($user->isMahasiswa() && $user->mahasiswaDetail) {
            return $user->mahasiswaDetail->mahasiswa_id === $tagihan->mahasiswa_id;
        }
        return false;
    }

    public function update(User $user, Tagihan $tagihan): bool
    {
        // Hanya admin yang boleh update tagihan (non-breaking rule)
        return $user->isAdmin();
    }

    public function delete(User $user, Tagihan $tagihan): bool
    {
        // Hanya admin yang boleh hapus tagihan (non-breaking rule)
        return $user->isAdmin();
    }
}


