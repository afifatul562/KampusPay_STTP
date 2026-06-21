<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\KonfirmasiPembayaran;
use App\Models\Setting;
use App\Models\AktivasiStatus;
use App\Support\TagihanPayability;
use App\Services\KasirTransferNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    protected function abortIfTagihanTidakSesuaiStatus(Tagihan $tagihan): void
    {
        $user = Auth::user();
        $detail = $user?->mahasiswaDetail;
        if (!$user || !$detail) {
            abort(403);
        }

        $message = TagihanPayability::violationMessage($tagihan, (int) $detail->mahasiswa_id);
        if ($message) {
            abort(403, $message);
        }
    }

    public function index(Request $request)
    {
        $user = User::with('mahasiswaDetail')->find(Auth::id());

        if (!$user->mahasiswaDetail) {
            abort(404, 'Detail mahasiswa tidak ditemukan.');
        }

        $semester = Setting::currentSemesterLabel();
        $aktivasi = AktivasiStatus::query()
            ->where('mahasiswa_id', $user->mahasiswaDetail->mahasiswa_id)
            ->where('semester_label', $semester)
            ->latest()
            ->first();

        $tagihanQuery = $user->mahasiswaDetail->tagihan()
            ->with('tarif', 'konfirmasi', 'pembayaran', 'mahasiswa')
            ->orderBy('tanggal_jatuh_tempo', 'asc');

        $today = now()->toDateString();
        $tagihanQuery->where(function ($q) use ($today) {
            $q->whereNull('available_from')->orWhere('available_from', '<=', $today);
        });

        TagihanPayability::applyAktivasiScope($tagihanQuery, $aktivasi);

        if ($request->filled('status')) {
            $statusFilter = $request->status;
            if ($statusFilter === 'Belum Dibayarkan') {
                $tagihanQuery->where('status', 'Belum Lunas')
                    ->where(function ($q) {
                        $q->whereNull('total_angsuran')->orWhere('total_angsuran', '<=', 0);
                    });
            } else {
                $tagihanQuery->where('status', $statusFilter);
            }
        }

        $tagihan = $tagihanQuery->get();

        return view('mahasiswa.pembayaran', compact('tagihan'));
    }

    public function show(Tagihan $tagihan)
    {
        // Gunakan policy untuk memastikan hanya pemilik yang dapat melihat
        $this->authorize('view', $tagihan);
        $this->abortIfTagihanTidakSesuaiStatus($tagihan);

        // Ambil semua data dari tabel 'settings'
        $settings = Setting::getCachedMap();

        // Kirim data tagihan DAN data settings ke view
        return view('mahasiswa.pembayaran_show', compact('tagihan', 'settings'));
    }

    /**
     * Tampilkan halaman untuk memilih metode pembayaran (Tunai / Transfer).
     */
    public function pilihMetode(Tagihan $tagihan)
    {
        // Otorisasi melihat tagihan
        $this->authorize('view', $tagihan);
        $this->abortIfTagihanTidakSesuaiStatus($tagihan);
        // Keamanan: Cek apakah tagihan ini boleh dibayar.
        // Hanya status 'Belum Lunas' atau 'Ditolak' yang boleh lanjut.
        if ( !in_array($tagihan->status, ['Belum Lunas', 'Ditolak']) ) {
            return redirect()
                ->route('mahasiswa.pembayaran.index')
                ->with('error', 'Tagihan ini sedang diproses atau sudah lunas.');
        }

        // Tampilkan view 'pilih-metode.blade.php' dan kirim data tagihan
        return view('mahasiswa.pilih-metode', [
            'tagihan' => $tagihan
        ]);
    }

    /**
     * Proses pilihan metode pembayaran dari mahasiswa.
     */
    public function prosesMetode(Request $request, Tagihan $tagihan)
    {
        // Otorisasi melihat tagihan
        $this->authorize('view', $tagihan);
        $this->abortIfTagihanTidakSesuaiStatus($tagihan);
        $metode = $request->input('metode');

        // Validasi: cek apakah tagihan wajib lunas
        if ($tagihan->isWajibLunas() && $metode == 'cicil') {
            return redirect()->back()->with('error', 'Tagihan ini tidak dapat dicicil. Harus dibayar lunas.');
        }

        if ($metode == 'cicil') {
            // Jika pilih 'cicil', arahkan ke halaman form cicilan
            return redirect()->route('mahasiswa.pembayaran.cicil', $tagihan->tagihan_id);

        } elseif ($metode == 'transfer') {
            // Jika pilih 'transfer', arahkan ke halaman upload bukti
            // (Halaman 'show' kamu yang sudah ada)
            return redirect()->route('mahasiswa.pembayaran.show', $tagihan->tagihan_id);

        } elseif ($metode == 'tunai') {
            // Status menunggu tunai; sisa pokok tetap dihitung tunggakan sampai lunas di kasir.
            $sisa = max(0.0, (float) ($tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan) - (float) ($tagihan->total_angsuran ?? 0));
            if ($sisa <= 0) {
                $sisa = (float) $tagihan->jumlah_tagihan;
            }
            $tagihan->fill([
                'status' => 'Menunggu Pembayaran Tunai',
                'sisa_pokok' => $sisa,
            ]);
            $tagihan->save();

            // Kembalikan ke daftar tagihan dengan pesan sukses
            return redirect()
                ->route('mahasiswa.pembayaran.index')
                ->with('success', 'Pilihan bayar tunai berhasil. Silakan lakukan pembayaran ke kasir.');
        }

        // Jika metode tidak valid
        return redirect()->back()->with('error', 'Metode pembayaran tidak valid.');
    }

    /**
     * Tampilkan form cicilan
     */
    public function cicil(Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);
        $this->abortIfTagihanTidakSesuaiStatus($tagihan);

        // Validasi: cek apakah tagihan wajib lunas
        if ($tagihan->isWajibLunas()) {
            return redirect()->route('mahasiswa.pembayaran.index')
                ->with('error', 'Tagihan ini tidak dapat dicicil. Harus dibayar lunas.');
        }

        // Cek status tagihan
        if (!in_array($tagihan->status, ['Belum Lunas', 'Ditolak'])) {
            return redirect()->route('mahasiswa.pembayaran.index')
                ->with('error', 'Tagihan ini sedang diproses atau sudah lunas.');
        }

        $sisaPokok = $tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan;

        return view('mahasiswa.cicil', compact('tagihan', 'sisaPokok'));
    }

    public function storeKonfirmasi(Request $request, Tagihan $tagihan)
    {
        // Otorisasi melihat tagihan
        $this->authorize('view', $tagihan);
        $this->abortIfTagihanTidakSesuaiStatus($tagihan);

        // Validasi: cek apakah ini cicilan atau lunas
        $isCicilan = $request->has('is_cicilan') && $request->input('is_cicilan') == '1';
        $sisaPokok = $tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan;

        $request->validate([
            'bukti_pembayaran' => 'required|file|max:2048',
            'jumlah_bayar' => $isCicilan ? 'required|numeric|min:1|max:' . $sisaPokok : 'nullable',
        ]);

        // Validasi tambahan (server-side mime sniffing)
        $file = $request->file('bukti_pembayaran');
        $mime = $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!$mime || !in_array(strtolower($mime), $allowed, true)) {
            return back()->withErrors(['bukti_pembayaran' => 'Format file tidak didukung. Hanya JPG/PNG.'])->withInput();
        }
        if (!@getimagesize($file->getPathname())) {
            return back()->withErrors(['bukti_pembayaran' => 'File bukan gambar yang valid.'])->withInput();
        }

        // Validasi jumlah bayar untuk cicilan
        if ($isCicilan) {
            $jumlahBayar = (float) $request->input('jumlah_bayar');
            if (!$tagihan->cicilanAmountValid($jumlahBayar)) {
                return back()->withErrors(['jumlah_bayar' => 'Jumlah pembayaran tidak valid.'])->withInput();
            }
        }

        $path = $file->store('bukti_pembayaran', 'public');

        $konfirmasi = KonfirmasiPembayaran::create([
            'tagihan_id' => $tagihan->tagihan_id,
            'file_bukti_pembayaran' => $path,
            'status_verifikasi' => 'Menunggu Verifikasi',
            'is_cicilan' => $isCicilan,
            'jumlah_bayar' => $isCicilan ? $request->input('jumlah_bayar') : null,
        ]);

        $tagihan->fill(['status' => 'Menunggu Verifikasi Transfer']);
        $tagihan->save();

        app(KasirTransferNotificationService::class)->notifyForKonfirmasi($konfirmasi);

        return redirect()->route('mahasiswa.pembayaran.index')
                         ->with('success', 'Bukti pembayaran berhasil di-upload dan sedang menunggu verifikasi.');
    }

    /**
     * Proses cicilan via transfer (upload bukti)
     */
    public function storeCicilTransfer(Request $request, Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);
        $this->abortIfTagihanTidakSesuaiStatus($tagihan);

        // Validasi: cek apakah tagihan wajib lunas
        if ($tagihan->isWajibLunas()) {
            return redirect()->route('mahasiswa.pembayaran.index')
                ->with('error', 'Tagihan ini tidak dapat dicicil. Harus dibayar lunas.');
        }

        $sisaPokok = $tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan;

        $request->validate([
            'bukti_pembayaran' => 'required|file|max:2048',
            'jumlah_bayar' => 'required|numeric|min:1|max:' . $sisaPokok,
        ]);

        // Validasi tambahan (server-side mime sniffing)
        $file = $request->file('bukti_pembayaran');
        $mime = $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!$mime || !in_array(strtolower($mime), $allowed, true)) {
            return back()->withErrors(['bukti_pembayaran' => 'Format file tidak didukung. Hanya JPG/PNG.'])->withInput();
        }
        if (!@getimagesize($file->getPathname())) {
            return back()->withErrors(['bukti_pembayaran' => 'File bukan gambar yang valid.'])->withInput();
        }

        $jumlahBayar = (float) $request->input('jumlah_bayar');
        if (!$tagihan->cicilanAmountValid($jumlahBayar)) {
            return back()->withErrors(['jumlah_bayar' => 'Jumlah pembayaran tidak valid.'])->withInput();
        }

        // Simpan file
        $folder = 'bukti_pembayaran';
        $path = $file->store($folder, 'public');

        // Simpan konfirmasi dengan catatan jumlah_bayar (bisa simpan di session atau kolom tambahan)
        // Untuk sekarang kita simpan jumlah_bayar di request, nanti saat verifikasi akan digunakan
        $konfirmasi = KonfirmasiPembayaran::create([
            'tagihan_id' => $tagihan->tagihan_id,
            'file_bukti_pembayaran' => $path,
            'status_verifikasi' => 'Menunggu Verifikasi',
            'is_cicilan' => true,
            'jumlah_bayar' => $jumlahBayar,
        ]);

        $tagihan->fill(['status' => 'Menunggu Verifikasi Transfer']);
        $tagihan->save();

        app(KasirTransferNotificationService::class)->notifyForKonfirmasi($konfirmasi);

        return redirect()->route('mahasiswa.pembayaran.index')
                         ->with('success', 'Bukti pembayaran cicilan berhasil di-upload dan sedang menunggu verifikasi.');
    }
}

