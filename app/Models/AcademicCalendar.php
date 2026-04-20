<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicCalendar extends Model
{
    use HasFactory;

    protected $table = 'academic_calendar';

    protected $fillable = [
        'semester_label',
        'semester_start_at',
        'uts_start_at',
        'uas_start_at',
    ];

    protected $casts = [
        'semester_start_at' => 'date',
        'uts_start_at' => 'date',
        'uas_start_at' => 'date',
    ];
}

