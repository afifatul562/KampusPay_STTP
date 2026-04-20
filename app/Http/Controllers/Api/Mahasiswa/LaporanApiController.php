<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Setting;
use App\Models\TarifMaster;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanApiController extends Controller
{
    public function ringkasan()
    {
        $mahasiswaId = Auth::user()->mahasiswaDetail->mahasiswa_id ?? null;

        if (!$mahasiswaId) {
            return response()->json([
                'success' => false,
                'message' => 'Detail mahasiswa tidak ditemukan.',
            ], 404);
        }

        $tunggakan = Tagihan::with('tarif', 'mahasiswa')
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereIn('status', ['Belum Lunas', 'Ditolak'])
            ->get();

        $jenisTarif = TarifMaster::select('nama_pembayaran')
            ->distinct()
            ->orderBy('nama_pembayaran')
            ->pluck('nama_pembayaran');

        return response()->json([
            'success' => true,
            'tunggakan' => $tunggakan->map(function (Tagihan $t) {
                return [
                    'id' => $t->tagihan_id,
                    'kode_pembayaran' => $t->kode_pembayaran,
                    'nama_pembayaran' => $t->tarif?->nama_pembayaran,
                    'jenis_tampilan' => $t->jenis_tampilan,
                    'jumlah_tagihan' => $t->jumlah_tagihan,
                    'sisa_pokok' => $t->sisa_pokok,
                    'tanggal_jatuh_tempo' => $t->tanggal_jatuh_tempo,
                ];
            }),
            'jenis_tarif' => $jenisTarif,
        ]);
    }

    public function histori(Request $request)
    {
        $user = Auth::user()->load('mahasiswaDetail.user');
        $mahasiswa = $user->mahasiswaDetail;
        $mahasiswaId = $mahasiswa?->mahasiswa_id;

        if (!$mahasiswaId) {
            return response()->json([
                'success' => false,
                'message' => 'Detail mahasiswa tidak ditemukan.',
            ], 404);
        }

        $query = Pembayaran::with('tagihan.tarif', 'tagihan.mahasiswa', 'userKasir')
            ->whereHas('tagihan', function ($q) use ($mahasiswaId) {
                $q->where('mahasiswa_id', $mahasiswaId);
            });

        if ($request->filled('start_date')) {
            try {
                $startDate = \Carbon\Carbon::parse($request->start_date)->format('Y-m-d');
                $query->whereDate('tanggal_bayar', '>=', $startDate);
            } catch (\Exception $e) {
            }
        }
        if ($request->filled('end_date')) {
            try {
                $endDate = \Carbon\Carbon::parse($request->end_date)->format('Y-m-d');
                $query->whereDate('tanggal_bayar', '<=', $endDate);
            } catch (\Exception $e) {
            }
        }

        if ($request->filled('jenis_pembayaran')) {
            $query->whereHas('tagihan.tarif', function ($q) use ($request) {
                $q->where('nama_pembayaran', $request->jenis_pembayaran);
            });
        }

        $histori = $query->latest('tanggal_bayar')->get()->map(function (Pembayaran $p) {
            return [
                'id' => $p->pembayaran_id,
                'tanggal_bayar' => optional($p->tanggal_bayar)->toDateTimeString(),
                'metode_pembayaran' => $p->metode_pembayaran,
                'jumlah_bayar' => $p->jumlah_bayar,
                'is_cicilan' => (bool) $p->is_cicilan,
                'tagihan' => [
                    'id' => $p->tagihan?->tagihan_id,
                    'kode_pembayaran' => $p->tagihan?->kode_pembayaran,
                    'nama_pembayaran' => $p->tagihan?->tarif?->nama_pembayaran,
                    'jenis_tampilan' => $p->tagihan?->jenis_tampilan,
                ],
            ];
        });

        $settings = Setting::getCachedMap();
        $tahunAkademik = $settings['academic_year'] ?? ($settings['tahun_akademik'] ?? ($settings['tahun_ajaran'] ?? '-'));

        return response()->json([
            'success' => true,
            'tahun_akademik' => $tahunAkademik,
            'histori' => $histori,
        ]);
    }

    public function tunggakan()
    {
        $user = Auth::user()->load('mahasiswaDetail.user');
        $mahasiswa = $user->mahasiswaDetail;
        $mahasiswaId = $mahasiswa?->mahasiswa_id;

        if (!$mahasiswaId) {
            return response()->json([
                'success' => false,
                'message' => 'Detail mahasiswa tidak ditemukan.',
            ], 404);
        }

        $tunggakan = Tagihan::with('tarif', 'mahasiswa')
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereIn('status', ['Belum Lunas', 'Ditolak'])
            ->get()
            ->map(function (Tagihan $t) {
                return [
                    'id' => $t->tagihan_id,
                    'kode_pembayaran' => $t->kode_pembayaran,
                    'nama_pembayaran' => $t->tarif?->nama_pembayaran,
                    'jenis_tampilan' => $t->jenis_tampilan,
                    'jumlah_tagihan' => $t->jumlah_tagihan,
                    'sisa_pokok' => $t->sisa_pokok,
                    'tanggal_jatuh_tempo' => $t->tanggal_jatuh_tempo,
                    'status' => $t->status, // Tambahkan status untuk ditampilkan
                ];
            });

        $settings = Setting::getCachedMap();
        $tahunAkademik = $settings['academic_year'] ?? ($settings['tahun_akademik'] ?? ($settings['tahun_ajaran'] ?? null));

        return response()->json([
            'success' => true,
            'tahun_akademik' => $tahunAkademik,
            'tunggakan' => $tunggakan,
        ]);
    }
}

