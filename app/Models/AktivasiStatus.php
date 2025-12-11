<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AktivasiStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'mahasiswa_id',
        'semester_label',
        'status',
        'chosen_by_user_id',
        'chosen_by_role',
        'note',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaDetail::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function chosenBy()
    {
        return $this->belongsTo(User::class, 'chosen_by_user_id');
    }
}

