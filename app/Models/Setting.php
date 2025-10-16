<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    // Izinkan kolom 'key' dan 'value' untuk diisi secara massal
    protected $fillable = ['key', 'value'];
}