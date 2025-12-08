<?php

namespace App\Http\Controllers; // Pastikan namespace ini benar

use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\KonfirmasiPembayaran;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\TarifMaster;
use App\Models\MahasiswaDetail; // <-- 1. TAMBAHKAN IMPORT INI
use App\Mail\TagihanBaruNotification; // <-- 2. TAMBAHKAN IMPORT INI
use App\Notifications\TagihanBaruCreated;
use App\Services\PaymentCodeGenerator; // Service class untuk generate kode pembayaran
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; // <-- 3. TAMBAHKAN IMPORT INI
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
      * BARU: Menampilkan SEMUA data TAGIHAN (untuk tabel utama admin).
      */
     public function indexTagihan()
     {
         Log::info('Mengambil data semua tagihan untuk admin.');
         try {
             $tagihan = Tagihan::with([
                     'mahasiswa.user', // Eager load user melalui mahasiswa
                     'tarif',
                     'pembayaran.userKasir' => function ($query) { // Eager load kasir melalui pembayaran
                         $query->select('id', 'nama_lengkap'); // Hanya ambil kolom yg perlu
                     },
                     'pembayaranAll.userKasir' => function ($query) { // Eager load semua pembayaran (termasuk cicilan)
                         $query->select('id', 'nama_lengkap');
                     }
                 ])
                 ->latest('created_at') // Urutkan berdasarkan tagihan terbaru
                 ->get();
             Log::info('Berhasil mengambil ' . count($tagihan) . ' tagihan.');
             // Penting: Kembalikan data dalam format yang konsisten, misal selalu dalam 'data'
             return response()->json(['data' => $tagihan]); // Kembalikan dalam 'data'
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
     * BARU: Menampilkan detail satu TAGIHAN.
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

            // Otorisasi akses melihat tagihan
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
     * !! DIMODIFIKASI UNTUK KIRIM EMAIL !!
     */
    public function createTagihan(Request $request)
    {
        Log::info("Mencoba membuat tagihan baru.", $request->all());

        // Otorisasi pembuatan tagihan (admin only via policy)
        $this->authorize('create', Tagihan::class);

        // 1. Validasi input
        $validatedData = $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa_detail,mahasiswa_id',
            'tarif_id' => 'required|exists:tarif_master,tarif_id',
            'jumlah_tagihan' => 'required|numeric|min:1',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:today',
        ]);

        // 2. Cek duplikat yang tidak dibatalkan (baik yang Lunas maupun belum)
        //    Admin TIDAK boleh membuat tagihan sejenis lagi jika pernah dibuat,
        //    kecuali tagihan sebelumnya dibatalkan secara resmi.
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

        // 3. GENERATE KODE UNIK menggunakan Service Class
        $validatedData['kode_pembayaran'] = PaymentCodeGenerator::generate($validatedData['tarif_id']);

        // 4. Tambahkan status
        $validatedData['status'] = 'Belum Lunas';

        try {
            // 5. Buat tagihan
            $tagihan = Tagihan::create($validatedData);
            Log::info("Tagihan berhasil dibuat ID: {$tagihan->tagihan_id}");

            // ====================================================
            // !! 6. PERSIAPAN & KIRIM EMAIL NOTIFIKASI (INI BAGIAN BARU) !!
            // ====================================================
            try {
                // Ambil data mahasiswa TERMASUK relasi user (untuk email)
                // Gunakan mahasiswa_id dari $tagihan yang baru dibuat
                $mahasiswa = MahasiswaDetail::with('user')->find($tagihan->mahasiswa_id);

                if ($mahasiswa && $mahasiswa->user && $mahasiswa->user->email) {
                    Log::info("Mencoba mengirim notifikasi tagihan ke: " . $mahasiswa->user->email);

                    // Kirim email menggunakan antrian (queue)
                    // Kita kirim object $tagihan yang sudah di-load relasi tarifnya nanti
                    Mail::to($mahasiswa->user->email)
                        ->queue(new TagihanBaruNotification($tagihan->load('tarif'))); // Load relasi tarif di sini

                    Log::info("Email notifikasi tagihan untuk ID {$tagihan->tagihan_id} berhasil dimasukkan ke antrian.");
                } else {
                    Log::warning("Tidak dapat mengirim email notifikasi: Data mahasiswa atau email tidak ditemukan untuk mahasiswa_id {$tagihan->mahasiswa_id}.");
                }
            } catch (\Exception $emailError) {
                // Catat error jika GAGAL mengirim email, TAPI JANGAN batalkan pembuatan tagihan
                Log::error("Gagal mengirim email notifikasi tagihan ID {$tagihan->tagihan_id}: " . $emailError->getMessage());
                // Tidak perlu melempar error atau mengembalikan respons error di sini
            }
            // ====================================================

            // Load relasi utama untuk respon JSON
            $tagihan->load('mahasiswa.user', 'tarif'); // tarif sudah di-load di atas, tapi tidak apa-apa
            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dibuat dan notifikasi sedang dikirim.', // Pesan sukses diubah
                'data' => $tagihan
            ], 201); // Gunakan status 201 (Created)

        } catch (\Exception $e) {
            Log::error("Error saat membuat tagihan: " . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat tagihan: ' . $e->getMessage()], 500);
        }

        try {
            Log::info("DEBUG: AKAN MENCOBA notify() untuk user ID: " . $mahasiswa->user->id); // <-- LOG BARU 1

            $mahasiswa->user->notify(new TagihanBaruCreated($tagihan));

            Log::info("DEBUG: SELESAI notify() tanpa error."); // <-- LOG BARU 2
            Log::info("Notifikasi database untuk tagihan ID {$tagihan->tagihan_id} berhasil dikirim ke user ID {$mahasiswa->user->id}.");
        } catch (\Exception $dbNotifyError) {
            Log::error("DEBUG: GAGAL saat notify(): " . $dbNotifyError->getMessage()); // <-- LOG BARU 3
            Log::error("Gagal mengirim notifikasi database tagihan ID {$tagihan->tagihan_id}: " . $dbNotifyError->getMessage());
        }
    }

     /**
      * BARU: Memperbarui data TAGIHAN.
      */
     public function updateTagihan(Request $request, $id)
     {
        Log::info("Mencoba update tagihan ID: {$id}", $request->all());
        $tagihan = Tagihan::find($id);

        // Otorisasi update tagihan
        $this->authorize('update', $tagihan);

         if (!$tagihan) { Log::error("Update gagal: Tagihan ID {$id} tidak ditemukan."); return response()->json(['message' => 'Tagihan tidak ditemukan.'], 404); }
         if ($tagihan->status === 'Lunas') { Log::warning("Update ditolak: Tagihan ID {$id} sudah lunas."); return response()->json(['message' => 'Tagihan yang sudah lunas tidak dapat diubah.'], 403); }

         $validatedData = $request->validate([
             'mahasiswa_id' => 'sometimes|required|exists:mahasiswa_detail,mahasiswa_id', // JIKA BOLEH GANTI MAHASISWA
             'tarif_id' => 'sometimes|required|exists:tarif_master,tarif_id',            // JIKA BOLEH GANTI TARIF
             'jumlah_tagihan' => 'sometimes|required|numeric|min:1',
             'tanggal_jatuh_tempo' => 'sometimes|required|date|after_or_equal:today',
             'kode_pembayaran' => ['sometimes', 'required', 'string', Rule::unique('tagihan')->ignore($id, 'tagihan_id')], // Jika boleh ganti kode
         ]);

         try {
             // Jika tarif_id diubah, update juga jumlah_tagihan dari tarif baru (jika tidak dikirim manual)
             if (isset($validatedData['tarif_id']) && !isset($validatedData['jumlah_tagihan'])) {
                 $tarif = \App\Models\TarifMaster::find($validatedData['tarif_id']);
                 if ($tarif) {
                     $validatedData['jumlah_tagihan'] = $tarif->nominal;
                 }
             }

             $tagihan->update($validatedData);
             Log::info("Tagihan ID {$id} berhasil diupdate.");
             $tagihan->load('mahasiswa.user', 'tarif', 'pembayaran.userKasir'); // Load relasi terbaru
             return response()->json(['success' => true, 'message' => 'Tagihan berhasil diperbarui', 'data' => $tagihan ]);
         } catch (\Exception $e) {
             Log::error("Error update tagihan ID {$id}: " . $e->getMessage());
             return response()->json(['message' => 'Gagal memperbarui tagihan: ' . $e->getMessage()], 500);
         }
     }

     /**
      * BARU: Menghapus data TAGIHAN.
      */
     public function destroyTagihan($id)
     {
        Log::info("Mencoba hapus tagihan ID: {$id}");
        $tagihan = Tagihan::find($id);

        if (!$tagihan) { Log::error("Hapus gagal: Tagihan ID {$id} tidak ditemukan."); return response()->json(['message' => 'Tagihan tidak ditemukan.'], 404); }
        
        // Otorisasi hapus tagihan
        $this->authorize('delete', $tagihan);
         if ($tagihan->status === 'Lunas') { Log::warning("Hapus ditolak: Tagihan ID {$id} sudah lunas."); return response()->json(['message' => 'Tagihan yang sudah lunas tidak dapat dihapus.'], 403); }

         // Opsional: Cek relasi Konfirmasi Pembayaran
         // if ($tagihan->konfirmasi()->exists()) { // Gunakan relasi 'konfirmasi' yang sudah ada
         //     Log::warning("Hapus ditolak: Tagihan ID {$id} memiliki konfirmasi pembayaran.");
         //     return response()->json(['message' => 'Tagihan ini memiliki konfirmasi pembayaran dan tidak dapat dihapus.'], 409);
         // }

         DB::beginTransaction();
         try {
             $tagihan->delete();
             DB::commit();
             Log::info("Tagihan ID {$id} berhasil dihapus.");
             return response()->noContent(); // Sukses 204
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
            'file_bukti_pembayaran' => 'required|string', // Nanti bisa diubah menjadi file upload
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

        // Update status tagihan menjadi 'Lunas'
        $pembayaran->tagihan()->update(['status' => 'Lunas']);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dibuat',
            'data' => $pembayaran
        ], 201);
    }
} // <-- Pastikan kurung kurawal penutup class ada di akhir
