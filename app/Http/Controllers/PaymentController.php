<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\KonfirmasiPembayaran;
use App\Models\AktivasiStatus;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\TarifMaster;
use App\Models\MahasiswaDetail;
use App\Mail\TagihanBaruNotification;
use App\Notifications\TagihanBaruCreated;
use App\Services\PaymentCodeGenerator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Menampilkan semua data pembayaran (termasuk cicilan) untuk admin.
     */
    public function index()
    {
        try {
            $payments = Pembayaran::with([
                    'tagihan.mahasiswa.user',
                    'tagihan.tarif',
                    'userKasir' => function ($query) {
                        $query->select('id', 'nama_lengkap');
                    }
                ])
                ->where(function($q) {
                    $q->whereNull('status_dibatalkan')
                      ->orWhere('status_dibatalkan', false);
                })
                ->latest('tanggal_bayar')
                ->get();
            return response()->json(['data' => $payments]);
        } catch (\Exception $e) {
            Log::error('Gagal mengambil data pembayaran: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data pembayaran.'], 500);
        }
    }

     /**
      * Menampilkan semua data tagihan untuk admin.
      */
     public function indexTagihan()
     {
         Log::info('Mengambil data semua tagihan untuk admin.');
         try {
             $tagihan = Tagihan::with([
                     'mahasiswa.user',
                     'tarif',
                     'pembayaran.userKasir' => function ($query) {
                         $query->select('id', 'nama_lengkap');
                     },
                     'pembayaranAll.userKasir' => function ($query) {
                         $query->select('id', 'nama_lengkap');
                     }
                 ])
                 ->latest('created_at')
                 ->get();
             Log::info('Berhasil mengambil ' . count($tagihan) . ' tagihan.');
             return response()->json(['data' => $tagihan]);
         } catch (\Exception $e) {
             Log::error('Gagal mengambil data tagihan: ' . $e->getMessage());
             return response()->json(['message' => 'Gagal mengambil data tagihan.'], 500);
         }
     }


    /**
     * Menampilkan detail satu pembayaran.
     */
    public function show($id)
    {
        $payment = Pembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif', 'verifier')
            ->findOrFail($id);
        return response()->json(['success' => true, 'data' => $payment]);
    }

    /**
     * Menampilkan detail satu tagihan.
     */
    public function showTagihan($id)
    {
        Log::info("Mencari detail tagihan ID: {$id}");
        try {
            $tagihan = Tagihan::with([
                'mahasiswa.user',
                'tarif',
                'pembayaran.userKasir' => fn($q)=>$q->select('id','nama_lengkap'),
                'pembayaranAll.userKasir' => function($q) {
                    $q->select('id','nama_lengkap');
                }
                ])
                ->findOrFail($id);

            $this->authorize('view', $tagihan);
            Log::info("Tagihan ID {$id} ditemukan.");
            return response()->json(['success' => true, 'data' => $tagihan]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Detail gagal: Tagihan ID {$id} tidak ditemukan.");
            return response()->json(['message' => 'Tagihan tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            Log::error("Error saat mengambil detail tagihan ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil detail tagihan.'], 500);
        }
    }


    /**
     * Membuat tagihan baru.
     */
    public function createTagihan(Request $request)
    {
        Log::info("Mencoba membuat tagihan baru.", $request->all());

        $this->authorize('create', Tagihan::class);

        $validatedData = $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa_detail,mahasiswa_id',
            'tarif_id' => 'required|exists:tarif_master,tarif_id',
            'jumlah_tagihan' => 'required|numeric|min:1',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:today',
        ]);

        $currentSemester = config('academic.current_semester');

        $aktivasi = AktivasiStatus::where('mahasiswa_id', $validatedData['mahasiswa_id'])
            ->where('semester_label', $currentSemester)
            ->latest()
            ->first();

        $tarifBssName = config('academic.bss_tarif_name', 'Administrasi BSS');
        $tarifBss = \App\Models\TarifMaster::firstOrCreate(
            ['nama_pembayaran' => $tarifBssName],
            ['nominal' => config('academic.bss_amount')]
        );

        $isBssTarif = ((int)$tarifBss->tarif_id === (int)$validatedData['tarif_id']);

        if ($aktivasi && $aktivasi->status === 'bss' && !$isBssTarif) {
            return response()->json(['message' => 'Mahasiswa status BSS. Hanya tagihan BSS yang diperbolehkan.'], 403);
        }

        if ($aktivasi && $aktivasi->status === 'bss' && $isBssTarif) {
            $validatedData['jumlah_tagihan'] = config('academic.bss_amount');
        }

        $existingTagihan = Tagihan::where('mahasiswa_id', $validatedData['mahasiswa_id'])
            ->where('tarif_id', $validatedData['tarif_id'])
            ->whereDoesntHave('pembayaran', function($q) {
                $q->where('status_dibatalkan', true);
            })
            ->first();
        if ($existingTagihan) {
            Log::warning("Gagal buat tagihan: Duplikat tagihan sejenis sudah pernah dibuat dan tidak dibatalkan.");
            return response()->json(['message' => 'Tagihan sejenis untuk mahasiswa ini sudah pernah dibuat (lunas/menunggu). Tidak dapat membuat ulang.'], 409);
        }

        $validatedData['kode_pembayaran'] = PaymentCodeGenerator::generate($validatedData['tarif_id']);
        $validatedData['status'] = 'Belum Lunas';
        $validatedData['semester_label'] = $currentSemester;
        $validatedData['is_bss'] = $aktivasi && $aktivasi->status === 'bss' && $isBssTarif;

        try {
            $tagihan = Tagihan::create($validatedData);
            Log::info("Tagihan berhasil dibuat ID: {$tagihan->tagihan_id}");

            try {
                $mahasiswa = MahasiswaDetail::with('user')->find($tagihan->mahasiswa_id);

                if ($mahasiswa && $mahasiswa->user && $mahasiswa->user->email) {
                    Log::info("Mencoba mengirim notifikasi tagihan ke: " . $mahasiswa->user->email);

                    Mail::to($mahasiswa->user->email)
                        ->queue(new TagihanBaruNotification($tagihan->load('tarif')));

                    Log::info("Email notifikasi tagihan untuk ID {$tagihan->tagihan_id} berhasil dimasukkan ke antrian.");
                } else {
                    Log::warning("Tidak dapat mengirim email notifikasi: Data mahasiswa atau email tidak ditemukan untuk mahasiswa_id {$tagihan->mahasiswa_id}.");
                }
            } catch (\Exception $emailError) {
                Log::error("Gagal mengirim email notifikasi tagihan ID {$tagihan->tagihan_id}: " . $emailError->getMessage());
            }

            $tagihan->load('mahasiswa.user', 'tarif');
            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dibuat dan notifikasi sedang dikirim.',
                'data' => $tagihan
            ], 201);

        } catch (\Exception $e) {
            Log::error("Error saat membuat tagihan: " . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat tagihan: ' . $e->getMessage()], 500);
        }
    }

     /**
      * Memperbarui data tagihan.
      */
     public function updateTagihan(Request $request, $id)
     {
        Log::info("Mencoba update tagihan ID: {$id}", $request->all());
        $tagihan = Tagihan::find($id);

        $this->authorize('update', $tagihan);

         if (!$tagihan) { Log::error("Update gagal: Tagihan ID {$id} tidak ditemukan."); return response()->json(['message' => 'Tagihan tidak ditemukan.'], 404); }
         if ($tagihan->status === 'Lunas') { Log::warning("Update ditolak: Tagihan ID {$id} sudah lunas."); return response()->json(['message' => 'Tagihan yang sudah lunas tidak dapat diubah.'], 403); }

         $validatedData = $request->validate([
             'mahasiswa_id' => 'sometimes|required|exists:mahasiswa_detail,mahasiswa_id',
             'tarif_id' => 'sometimes|required|exists:tarif_master,tarif_id',
             'jumlah_tagihan' => 'sometimes|required|numeric|min:1',
             'tanggal_jatuh_tempo' => 'sometimes|required|date|after_or_equal:today',
             'kode_pembayaran' => ['sometimes', 'required', 'string', Rule::unique('tagihan')->ignore($id, 'tagihan_id')],
         ]);

         try {
             if (isset($validatedData['tarif_id']) && !isset($validatedData['jumlah_tagihan'])) {
                 $tarif = \App\Models\TarifMaster::find($validatedData['tarif_id']);
                 if ($tarif) {
                     $validatedData['jumlah_tagihan'] = $tarif->nominal;
                 }
             }

             $tagihan->update($validatedData);
             Log::info("Tagihan ID {$id} berhasil diupdate.");
             $tagihan->load('mahasiswa.user', 'tarif', 'pembayaran.userKasir');
             return response()->json(['success' => true, 'message' => 'Tagihan berhasil diperbarui', 'data' => $tagihan ]);
         } catch (\Exception $e) {
             Log::error("Error update tagihan ID {$id}: " . $e->getMessage());
             return response()->json(['message' => 'Gagal memperbarui tagihan: ' . $e->getMessage()], 500);
         }
     }

     /**
      * Menghapus data tagihan.
      */
     public function destroyTagihan($id)
     {
        Log::info("Mencoba hapus tagihan ID: {$id}");
        $tagihan = Tagihan::find($id);

        if (!$tagihan) { Log::error("Hapus gagal: Tagihan ID {$id} tidak ditemukan."); return response()->json(['message' => 'Tagihan tidak ditemukan.'], 404); }

        $this->authorize('delete', $tagihan);
         if ($tagihan->status === 'Lunas') { Log::warning("Hapus ditolak: Tagihan ID {$id} sudah lunas."); return response()->json(['message' => 'Tagihan yang sudah lunas tidak dapat dihapus.'], 403); }

         DB::beginTransaction();
         try {
             $tagihan->delete();
             DB::commit();
             Log::info("Tagihan ID {$id} berhasil dihapus.");
             return response()->noContent();
         } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Error hapus tagihan ID {$id}: " . $e->getMessage());
             return response()->json(['message' => 'Gagal menghapus tagihan: ' . $e->getMessage()], 500);
         }
     }

    /**
     * Membuat konfirmasi pembayaran baru.
     */
    public function createKonfirmasiPembayaran(Request $request)
    {
        $validatedData = $request->validate([
            'tagihan_id' => 'required|exists:tagihan,tagihan_id',
            'file_bukti_pembayaran' => 'required|string',
        ]);

        $konfirmasi = KonfirmasiPembayaran::create($validatedData + ['status_verifikasi' => 'Menunggu Verifikasi']);

        return response()->json([
            'success' => true,
            'message' => 'Konfirmasi pembayaran berhasil dibuat',
            'data' => $konfirmasi
        ], 201);
    }

    /**
     * Membuat data pembayaran final setelah verifikasi.
     */
    public function createPembayaran(Request $request)
    {
        $validatedData = $request->validate([
            'tagihan_id' => 'required|exists:tagihan,tagihan_id|unique:pembayaran,tagihan_id',
            'konfirmasi_id' => 'required|exists:konfirmasi_pembayaran,konfirmasi_id',
            'diverifikasi_oleh' => 'required|exists:users,id',
            'tanggal_bayar' => 'required|date',
            'metode_pembayaran' => 'required|string'
        ]);

        $pembayaran = Pembayaran::create($validatedData);

        $pembayaran->tagihan()->update(['status' => 'Lunas']);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dibuat',
            'data' => $pembayaran
        ], 201);
    }
}
