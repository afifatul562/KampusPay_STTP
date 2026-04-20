<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MahasiswaProgram extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa_program';
    protected $primaryKey = 'mahasiswa_id';
    public $incrementing = false;

    protected $fillable = [
        'mahasiswa_id',
        'program_type',
        'kip_scheme',
        'is_transfer',
        'start_semester',
    ];

    protected $casts = [
        'is_transfer' => 'bool',
        'start_semester' => 'int',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaDetail::class, 'mahasiswa_id', 'mahasiswa_id');
    }
}

