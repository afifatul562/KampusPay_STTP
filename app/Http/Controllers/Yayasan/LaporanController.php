<?php



namespace App\Http\Controllers\Yayasan;



use App\Http\Controllers\Controller;

use App\Models\MahasiswaDetail;

use App\Services\ReportDataService;

use App\Support\AcademicPeriod;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;

use Illuminate\Support\Collection;

use Illuminate\Validation\ValidationException;



class LaporanController extends Controller

{

    public function __construct(private ReportDataService $reportData) {}



    /**

     * Halaman laporan yayasan (read-only): preview server-side + tautan unduh PDF.

     */

    public function index(Request $request)

    {

        $masterAngkatan = MahasiswaDetail::query()

            ->select('angkatan')

            ->whereNotNull('angkatan', 'and')

            ->distinct()

            ->orderByDesc('angkatan')

            ->pluck('angkatan');



        $masterProdi = MahasiswaDetail::query()

            ->select('program_studi')

            ->whereNotNull('program_studi', 'and')

            ->distinct()

            ->orderBy('program_studi', 'asc')

            ->pluck('program_studi');



        $nowYear = (int) date('Y');

        $academicYears = collect(range($nowYear, $nowYear - 4))

            ->map(fn (int $y) => "{$y}/" . ($y + 1))

            ->values();

        $calendarYears = collect(range($nowYear, $nowYear - 4))->values();



        $filters = $this->filtersFromRequest($request);

        $previewData = null;

        $summaryTunggakan = null;

        $summaryPembayaran = null;

        $previewMode = null;



        if ($request->isMethod('post')) {

            $validated = $this->validateFilters($request);

            $filters = $validated;

            $previewMode = $validated['periode_mode'] ?? 'akademik';

            $previewData = $this->fetchReportData($validated);



            if ($validated['jenis_laporan'] === 'tunggakan' && $previewData instanceof Collection) {

                $summaryTunggakan = $this->reportData->summarizeTunggakan($previewData);

            }



            if ($previewMode === 'kalender'

                && $validated['jenis_laporan'] === 'pembayaran'

                && $previewData instanceof Collection) {

                $summaryPembayaran = $this->reportData->summarizePembayaranKalender($previewData);

            }

        }



        return view('yayasan.laporan', [

            'masterAngkatan' => $masterAngkatan,

            'masterProdi' => $masterProdi,

            'academicYears' => $academicYears,

            'calendarYears' => $calendarYears,

            'defaultTahunAkademik' => AcademicPeriod::currentAcademicYear(),

            'defaultTahunKalender' => $nowYear,

            'filters' => $filters,

            'previewData' => $previewData,

            'previewMode' => $previewMode,

            'summaryTunggakan' => $summaryTunggakan,

            'summaryPembayaran' => $summaryPembayaran,

        ]);

    }



    /**

     * Unduh PDF langsung tanpa menyimpan riwayat ke database.

     */

    public function downloadPdf(Request $request)

    {

        $validated = $this->validateFilters($request);

        $data = $this->fetchReportData($validated);



        if ($data === null || ($data instanceof Collection && $data->isEmpty())) {

            return redirect()

                ->route('yayasan.laporan.index')

                ->withInput($validated)

                ->with('error', 'Tidak ada data untuk periode yang dipilih.');

        }



        $jenis = $validated['jenis_laporan'];

        $mode = $validated['periode_mode'] ?? 'akademik';

        $periodeFormatted = $this->formatPeriode($validated);



        if ($jenis === 'tunggakan') {

            $fileName = sprintf(

                'laporan-tunggakan-yayasan-%s-%s.pdf',

                str_replace(['/', ' '], '-', $periodeFormatted),

                date('YmdHis')

            );



            return Pdf::loadView('yayasan.pdf.laporan_tunggakan', [

                'data' => $data,

                'periodeFormatted' => $periodeFormatted,

                'summaryTunggakan' => $this->reportData->summarizeTunggakan($data),

            ])->setPaper('a4', 'portrait')->download($fileName);

        }



        if ($mode === 'kalender') {

            $fileName = sprintf(

                'laporan-pembayaran-kalender-yayasan-%d-%s.pdf',

                (int) $validated['tahun'],

                date('YmdHis')

            );



            return Pdf::loadView('pdf.laporan_pembayaran_kalender', [

                'data' => $data,

                'periodeFormatted' => $periodeFormatted,

                'summaryPembayaran' => $this->reportData->summarizePembayaranKalender($data),

                'contextLabel' => 'Monitoring Yayasan',

            ])->setPaper('a4', 'landscape')->download($fileName);

        }



        $fileName = sprintf(

            'laporan-pembayaran-yayasan-%s-%s.pdf',

            str_replace('/', '-', $validated['tahun_akademik'] ?? (string) $validated['tahun']),

            date('YmdHis')

        );



        return Pdf::loadView('pdf.laporan_template', [

            'data' => $data,

            'jenis' => 'pembayaran',

            'periodeFormatted' => $periodeFormatted,

        ])->setPaper('a4', 'portrait')->download($fileName);

    }



    private function validateFilters(Request $request): array

    {

        if (! $request->filled('periode_mode')) {

            $request->merge(['periode_mode' => 'akademik']);

        }

        $validated = $request->validate([

            'jenis_laporan' => 'required|string|in:pembayaran,tunggakan',

            'periode_mode' => 'required|string|in:akademik,kalender',

            'tahun_akademik' => 'nullable|string|max:20',

            'tahun_kalender' => 'nullable|digits:4',

            'semester' => 'nullable|string|in:Ganjil,Genap',

            'angkatan' => 'nullable|string|max:10',

            'program_studi' => 'nullable|string|max:100',

        ]);



        $mode = $validated['periode_mode'];



        if ($mode === 'kalender') {

            $year = (int) ($validated['tahun_kalender'] ?? now()->year);

            $validated['tahun_kalender'] = (string) $year;

            $validated['tahun'] = $year;

            $validated['tahun_akademik'] = null;

            $validated['semester'] = null;

        } else {

            $ay = trim((string) ($validated['tahun_akademik'] ?? ''));

            if ($ay === '' || ! preg_match('/^(\d{4})\/\d{4}$/', $ay, $m)) {

                throw ValidationException::withMessages([

                    'tahun_akademik' => 'Format tahun akademik tidak valid (contoh: 2025/2026).',

                ]);

            }

            $validated['tahun_akademik'] = $ay;

            $validated['tahun'] = (int) $m[1];

            $validated['tahun_kalender'] = null;

        }



        return $validated;

    }



    private function filtersFromRequest(Request $request): array

    {

        return [

            'jenis_laporan' => old('jenis_laporan', $request->input('jenis_laporan', 'pembayaran')),

            'periode_mode' => old('periode_mode', $request->input('periode_mode', 'akademik')),

            'tahun_akademik' => old('tahun_akademik', $request->input('tahun_akademik', AcademicPeriod::currentAcademicYear())),

            'tahun_kalender' => old('tahun_kalender', $request->input('tahun_kalender', (string) now()->year)),

            'semester' => old('semester', $request->input('semester', '')),

            'angkatan' => old('angkatan', $request->input('angkatan', '')),

            'program_studi' => old('program_studi', $request->input('program_studi', '')),

        ];

    }



    private function fetchReportData(array $validated): ?Collection

    {

        $jenis = $validated['jenis_laporan'];

        $mode = $validated['periode_mode'] ?? 'akademik';

        $tahun = (int) $validated['tahun'];

        $semester = $validated['semester'] ?? null;

        $filters = [

            'tahun_akademik' => $validated['tahun_akademik'] ?? null,

            'angkatan' => $validated['angkatan'] ?? null,

            'program_studi' => $validated['program_studi'] ?? null,

        ];



        if ($mode === 'kalender') {

            if ($jenis === 'pembayaran') {

                return $this->reportData->fetchPembayaranKalender($tahun, $filters);

            }



            return $this->reportData->fetchTunggakanKalender($tahun, $filters);

        }



        if ($jenis === 'pembayaran') {

            $data = $this->reportData->fetch('pembayaran', $tahun, $semester, $filters);



            return $data instanceof Collection ? $data : collect($data ?? []);

        }



        return $this->reportData->fetchTunggakanAkademik($tahun, $filters, $semester);

    }



    private function formatPeriode(array $validated): string

    {

        if (($validated['periode_mode'] ?? 'akademik') === 'kalender') {

            return ReportDataService::calendarYearLabel((int) $validated['tahun']);

        }



        $semester = $validated['semester'] ?? null;

        $tahunAkademik = trim((string) ($validated['tahun_akademik'] ?? ''));



        return $tahunAkademik . ($semester ? " {$semester}" : '');

    }

}

