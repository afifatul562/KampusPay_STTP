<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat laporan.
     */
    public function view(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna dapat menghapus laporan.
     */
    public function delete(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }
}


