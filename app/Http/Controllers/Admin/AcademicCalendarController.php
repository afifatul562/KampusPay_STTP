<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use Illuminate\Http\Request;

class AcademicCalendarController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => AcademicCalendar::query()->orderByDesc('semester_label')->get(),
        ]);
    }

    public function upsert(Request $request)
    {
        $validated = $request->validate([
            'semester_label' => ['required', 'string', 'max:100'],
            'semester_start_at' => ['nullable', 'date'],
            'uts_start_at' => ['nullable', 'date'],
            'uas_start_at' => ['nullable', 'date'],
        ]);

        $row = AcademicCalendar::updateOrCreate(
            ['semester_label' => $validated['semester_label']],
            [
                'semester_start_at' => $validated['semester_start_at'] ?? null,
                'uts_start_at' => $validated['uts_start_at'] ?? null,
                'uas_start_at' => $validated['uas_start_at'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $row,
        ]);
    }
}

