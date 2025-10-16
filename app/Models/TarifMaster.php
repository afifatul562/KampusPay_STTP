<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarifMaster extends Model
{
    use HasFactory;

    protected $table = 'tarif_master';  // specify the correct table name
    protected $primaryKey = 'tarif_id';
    protected $fillable = [
        'nama_pembayaran',
        'nominal',
        'program_studi',
        'angkatan'
    ];

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'tarif_id', 'tarif_id');
    }
}
