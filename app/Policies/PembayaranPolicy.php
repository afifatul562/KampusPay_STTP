<?php

namespace App\Policies;

use App\Models\Pembayaran;
use App\Models\User;

class PembayaranPolicy
{
    public function view(User $user, Pembayaran $pembayaran): bool
    {
        if ($user->isAdmin() || $user->isKasir()) {
            return true;
        }
        if ($user->isMahasiswa() && $user->mahasiswaDetail && $pembayaran->tagihan) {
            return $user->mahasiswaDetail->mahasiswa_id === $pembayaran->tagihan->mahasiswa_id;
        }
        return false;
    }
}


