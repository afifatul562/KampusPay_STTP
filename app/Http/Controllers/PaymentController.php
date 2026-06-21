<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\KonfirmasiPembayaran;
use App\Models\AktivasiStatus;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\TarifMaster;
use App\Models\MahasiswaDetail;
use App\Models\Setting;
use App\Mail\TagihanBaruNotification;
use App\Notifications\TagihanBaruCreated;
use App\Services\PaymentCodeGenerator;
use App\Support\TagihanDedup;
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
      * Daftar tagihan (admin/kasir) dengan pagination & filter server-side.
      */
     public function indexTagihan(Request $request)
     {
         try {
             $perPage = max(5, min(50, (int) $request->query('per_page', 15)));

             $query = Tagihan::with([
                     'mahasiswa.user',
                     'tarif',
                     'pembayaran.userKasir' => function ($query) {
                         $query->select('id', 'nama_lengkap');
                     },
                     'pembayaranAll.userKasir' => function ($query) {
                         $query->select('id', 'nama_lengkap');
                     },
                 ]);

             $this->applyTagihanListFilters($query, $request);

             $tagihan = $query->latest('created_at')->paginate($perPage);

             return response()->json($tagihan);
         } catch (\Exception $e) {
             Log::error('Gagal mengambil data tagihan: ' . $e->getMessage());

             return response()->json(['message' => 'Gagal mengambil data tagihan.'], 500);
         }
     }

     private function applyTagihanListFilters(Builder $query, Request $request): void
     {
         $tahunAkademik = trim((string) $request->query('tahun_akademik', ''));
         $angkatan = trim((string) $request->query('angkatan', ''));
         $programStudi = trim((string) $request->query('program_studi', ''));
         $jenis = trim((string) $request->query('jenis', ''));
         $search = trim((string) $request->query('search', ''));
         $status = trim((string) $request->query('status', ''));

         if ($tahunAkademik !== '') {
             $query->where('semester_label', 'LIKE', $tahunAkademik . '%');
         }

         if ($angkatan !== '' || $programStudi !== '' || $search !== '') {
             $query->whereHas('mahasiswa', function ($mq) use ($angkatan, $programStudi, $search) {
                 if ($angkatan !== '') {
                     $mq->where('angkatan', $angkatan);
                 }
                 if ($programStudi !== '') {
                     $mq->where('program_studi', $programStudi);
                 }
                 if ($search !== '') {
                     $mq->where(function ($sq) use ($search) {
                         $sq->where('npm', 'like', '%' . $search . '%')
                             ->orWhereHas('user', function ($uq) use ($search) {
                                 $uq->where('nama_lengkap', 'like', '%' . $search . '%');
                             });
                     });
                 }
             });
         }

         if ($jenis !== '') {
             $query->whereHas('tarif', function ($tq) use ($jenis) {
                 $tq->where('nama_pembayaran', $jenis);
             });
         }

         if ($status === '') {
             return;
         }

         if ($status === 'BUKAN_LUNAS') {
             $query->where('status', '!=', 'Lunas');

             return;
         }

         if ($status === 'Dibatalkan') {
             $query->whereHas('pembayaran', function ($pq) {
                 $pq->where('status_dibatalkan', true);
             });

             return;
         }

         if ($status === 'Belum Dibayarkan') {
             $query->where('status', 'Belum Lunas')
                 ->whereDoesntHave('pembayaranAll', function ($pq) {
                     $pq->where(function ($q) {
                         $q->whereNull('status_dibatalkan')->orWhere('status_dibatalkan', false);
                     });
                 });

             return;
         }

         if ($status === 'Belum Lunas') {
             $query->where('status', 'Belum Lunas')
                 ->whereHas('pembayaranAll', function ($pq) {
                     $pq->where(function ($q) {
                         $q->whereNull('status_dibatalkan')->orWhere('status_dibatalkan', false);
                     });
                 });

             return;
         }

         $query->where('status', $status)
             ->whereDoesntHave('pembayaran', function ($pq) {
                 $pq->where('status_dibatalkan', true);
             });
     }


    /**
     * Menampilkan detail satu pembayaran.
     */
    public function show(int|string $id)
    {
        $payment = Pembayaran::with('tagihan.mahasiswa.user', 'tagihan.tarif', 'verifier')
            ->findOrFail($id);
        return response()->json(['success' => true, 'data' => $payment]);
    }

    /**
     * BARU: Menampilkan detail satu TAGIHAN.
     */
    public function showTagihan(int|string $id)
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
     * Membuat tagihan baru dan mengirim notifikasi ke mahasiswa.
     */
    public function createTagihan(Request $request)
    {
        Log::info("Mencoba membuat tagihan baru.", $request->all());

        // Otorisasi pembuatan tagihan (admin only via policy)
        $this->authorize('create', Tagihan::class);

        $validatedData = $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa_detail,mahasiswa_id',
            'tarif_id' => 'required|exists:tarif_master,tarif_id',
            'jumlah_tagihan' => 'required|numeric|min:1',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:today',
        ]);

        $currentSemester = Setting::currentSemesterLabel();

        $aktivasi = AktivasiStatus::where('mahasiswa_id', '=', $validatedData['mahasiswa_id'], 'and')
            ->where('semester_label', '=', $currentSemester, 'and')
            ->latest('created_at')
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
            // Paksa nominal BSS dari konfigurasi
            $validatedData['jumlah_tagihan'] = config('academic.bss_amount');
        }

        $mahasiswaDetail = MahasiswaDetail::query()->find($validatedData['mahasiswa_id']);
        $tarif = TarifMaster::query()->find($validatedData['tarif_id']);

        if ($isBssTarif) {
            $existingBss = Tagihan::query()
                ->where('mahasiswa_id', '=', $validatedData['mahasiswa_id'], 'and')
                ->where('tarif_id', '=', $validatedData['tarif_id'], 'and')
                ->where('is_bss', '=', true, 'and')
                ->exists();
            if ($existingBss) {
                return response()->json(['message' => 'Tagihan BSS untuk mahasiswa ini sudah ada.'], 409);
            }
        } elseif ($mahasiswaDetail && $tarif && TagihanDedup::alreadyExistsForAktivasi(
            (int) $validatedData['mahasiswa_id'],
            $tarif,
            $mahasiswaDetail,
            $currentSemester
        )) {
            Log::warning('Gagal buat tagihan: duplikat tagihan setara sudah ada.');
            return response()->json(['message' => 'Tagihan setara untuk mahasiswa ini sudah ada di semester ini.'], 409);
        }

        $validatedData['kode_pembayaran'] = PaymentCodeGenerator::generate($validatedData['tarif_id']);
        $validatedData['status'] = 'Belum Lunas';
        $validatedData['semester_label'] = $currentSemester;
        $validatedData['is_bss'] = $aktivasi && $aktivasi->status === 'bss' && $isBssTarif;
        $validatedData['semester_perkuliahan'] = \App\Support\SemesterPerkuliahan::fromMahasiswa($mahasiswaDetail);

        try {
            $tagihan = Tagihan::create($validatedData);
            $tagihan->updateAngsuran();
            Log::info("Tagihan berhasil dibuat ID: {$tagihan->tagihan_id}");

            $mahasiswa = MahasiswaDetail::with('user')->find($tagihan->mahasiswa_id);

            if ($mahasiswa && $mahasiswa->user) {
                try {
                    $mahasiswa->user->notify(new TagihanBaruCreated($tagihan));
                    Log::info("Notifikasi database tagihan ID {$tagihan->tagihan_id} untuk user ID {$mahasiswa->user->id}.");
                } catch (\Exception $dbNotifyError) {
                    Log::error("Gagal notifikasi database tagihan ID {$tagihan->tagihan_id}: " . $dbNotifyError->getMessage());
                }

                // Email: kirim langsung (send) agar tidak bergantung pada queue worker.
                if ($mahasiswa->user->email) {
                    try {
                        if (config('mail.default') === 'log') {
                            Log::warning('[KampusPay] MAIL_MAILER=log: email tagihan tidak dikirim ke inbox, hanya ke storage/logs/laravel.log. Ubah ke smtp di .env dan isi kredensial SMTP.');
                        }
                        Log::info("Mengirim email tagihan ke: {$mahasiswa->user->email} (mailer: ".config('mail.default').')');
                        Mail::to($mahasiswa->user->email)
                            ->send(new TagihanBaruNotification($tagihan->load('tarif')));
                        Log::info("Email tagihan ID {$tagihan->tagihan_id} terkirim.");
                    } catch (\Exception $emailError) {
                        Log::error("Gagal email tagihan ID {$tagihan->tagihan_id}: " . $emailError->getMessage());
                    }
                } else {
                    Log::warning("Email tagihan dilewati: user ID {$mahasiswa->user->id} belum punya alamat email (isi di Profil mahasiswa).");
                }
            } else {
                Log::warning("Tidak dapat mengirim notifikasi tagihan: mahasiswa/user tidak ditemukan untuk mahasiswa_id {$tagihan->mahasiswa_id}.");
            }

            // Load relasi utama untuk respon JSON
            $tagihan->load('mahasiswa.user', 'tarif');
            return response()->json([
                'success' => true,
                'message' => 'Tagihan berhasil dibuat.',
                'data' => $tagihan,
            ], 201);

        } catch (\Exception $e) {
            Log::error("Error saat membuat tagihan: " . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat tagihan: ' . $e->getMessage()], 500);
        }
    }

     /**
      * Memperbarui data tagihan.
      */
     public function updateTagihan(Request $request, int|string $id)
     {
        Log::info("Mencoba update tagihan ID: {$id}", $request->all());
        $tagihan = Tagihan::find($id, ['*']);

        if (!$tagihan instanceof Tagihan) {
            Log::error("Update gagal: Tagihan ID {$id} tidak ditemukan.");
            return response()->json(['message' => 'Tagihan tidak ditemukan.'], 404);
        }

        // Otorisasi update tagihan
        $this->authorize('update', $tagihan);
         if ($tagihan->status === 'Lunas') { Log::warning("Update ditolak: Tagihan ID {$id} sudah lunas."); return response()->json(['message' => 'Tagihan yang sudah lunas tidak dapat diubah.'], 403); }

         $validatedData = $request->validate([
             'mahasiswa_id' => 'sometimes|required|exists:mahasiswa_detail,mahasiswa_id',
             'tarif_id' => 'sometimes|required|exists:tarif_master,tarif_id',
             'jumlah_tagihan' => 'sometimes|required|numeric|min:1',
             'tanggal_jatuh_tempo' => 'sometimes|required|date',
             'kode_pembayaran' => ['sometimes', 'nullable', 'string', Rule::unique('tagihan')->ignore($id, 'tagihan_id')],
         ]);

         if (array_key_exists('kode_pembayaran', $validatedData) && blank($validatedData['kode_pembayaran'])) {
             unset($validatedData['kode_pembayaran']);
         }

         $existingDueDate = $tagihan->tanggal_jatuh_tempo?->format('Y-m-d');
         if (isset($validatedData['tanggal_jatuh_tempo'])
             && $validatedData['tanggal_jatuh_tempo'] !== $existingDueDate
             && strtotime($validatedData['tanggal_jatuh_tempo']) < strtotime('today')) {
             return response()->json(['message' => 'Tanggal jatuh tempo baru harus hari ini atau setelahnya.'], 422);
         }

         try {
             // Jika tarif_id diubah, update juga jumlah_tagihan dari tarif baru (jika tidak dikirim manual)
             if (isset($validatedData['tarif_id']) && !isset($validatedData['jumlah_tagihan'])) {
                 $tarif = \App\Models\TarifMaster::find($validatedData['tarif_id'], ['*']);
                 if ($tarif) {
                     $validatedData['jumlah_tagihan'] = $tarif->nominal;
                 }
             }

             $tagihan->fill($validatedData);
             $tagihan->save();
             $tagihan->updateAngsuran();
             Log::info("Tagihan ID {$id} berhasil diupdate.");
             $tagihan->load('mahasiswa.user', 'tarif', 'pembayaran.userKasir'); // Load relasi terbaru
             return response()->json(['success' => true, 'message' => 'Tagihan berhasil diperbarui', 'data' => $tagihan ]);
         } catch (\Exception $e) {
             Log::error("Error update tagihan ID {$id}: " . $e->getMessage());
             return response()->json(['message' => 'Gagal memperbarui tagihan: ' . $e->getMessage()], 500);
         }
     }

     /**
      * Menghapus data tagihan.
      */
     public function destroyTagihan(int|string $id)
     {
        Log::info("Mencoba hapus tagihan ID: {$id}");
        $tagihan = Tagihan::find($id, ['*']);

        if (!$tagihan instanceof Tagihan) {
            Log::error("Hapus gagal: Tagihan ID {$id} tidak ditemukan.");
            return response()->json(['message' => 'Tagihan tidak ditemukan.'], 404);
        }

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
             Tagihan::destroy($tagihan->getKey());
             DB::commit();
             Log::info("Tagihan ID {$id} berhasil dihapus.");
             return response()->noContent(204); // Sukses 204
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
}
