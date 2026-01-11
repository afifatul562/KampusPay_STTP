<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Mengambil view/konten yang mewakili komponen.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
