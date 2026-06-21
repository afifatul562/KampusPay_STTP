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
        'semester_start_at' => 'date:Y-m-d',
        'uts_start_at' => 'date:Y-m-d',
        'uas_start_at' => 'date:Y-m-d',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        foreach (['semester_start_at', 'uts_start_at', 'uas_start_at'] as $key) {
            $value = $this->{$key};
            $data[$key] = $value ? $value->format('Y-m-d') : null;
        }

        return $data;
    }
}

