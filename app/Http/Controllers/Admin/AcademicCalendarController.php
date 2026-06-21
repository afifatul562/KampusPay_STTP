<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use Illuminate\Http\Request;

class AcademicCalendarController extends Controller
{
    public function index()
    {
        $rows = AcademicCalendar::query()
            ->orderByDesc('semester_label')
            ->get()
            ->map(fn (AcademicCalendar $row) => $this->formatCalendarRow($row))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
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
            'data' => $this->formatCalendarRow($row),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatCalendarRow(AcademicCalendar $row): array
    {
        return [
            'id' => $row->id,
            'semester_label' => $row->semester_label,
            'semester_start_at' => $row->semester_start_at?->format('Y-m-d'),
            'uts_start_at' => $row->uts_start_at?->format('Y-m-d'),
            'uas_start_at' => $row->uas_start_at?->format('Y-m-d'),
            'created_at' => $row->created_at?->toIso8601String(),
            'updated_at' => $row->updated_at?->toIso8601String(),
        ];
    }
}

