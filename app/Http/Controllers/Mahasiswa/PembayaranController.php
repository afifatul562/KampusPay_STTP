<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\KonfirmasiPembayaran;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    /**
     * Menampilkan daftar tagihan mahasiswa.
     */
    public function index(Request $request)
    {
        $user = User::with('mahasiswaDetail')->find(Auth::id());

        if (!$user->mahasiswaDetail) {
            abort(404, 'Detail mahasiswa tidak ditemukan.');
        }

        $tagihanQuery = $user->mahasiswaDetail->tagihan()
            ->with('tarif', 'konfirmasi', 'pembayaran', 'mahasiswa')
            ->orderBy('tanggal_jatuh_tempo', 'asc');

        if ($request->filled('status')) {
            $tagihanQuery->where('status', $request->status);
        }

        $tagihan = $tagihanQuery->get();

        return view('mahasiswa.pembayaran', compact('tagihan'));
    }

    /**
     * Menampilkan detail tagihan dan form upload bukti pembayaran.
     */
    public function show(Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);

        $settings = Setting::getCachedMap();

        return view('mahasiswa.pembayaran_show', compact('tagihan', 'settings'));
    }

    /**
     * Tampilkan halaman untuk memilih metode pembayaran (Tunai / Transfer).
     */
    public function pilihMetode(Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);

        if ( !in_array($tagihan->status, ['Belum Lunas', 'Ditolak']) ) {
            return redirect()
                ->route('mahasiswa.pembayaran.index')
                ->with('error', 'Tagihan ini sedang diproses atau sudah lunas.');
        }

        return view('mahasiswa.pilih-metode', [
            'tagihan' => $tagihan
        ]);
    }

    /**
     * Proses pilihan metode pembayaran dari mahasiswa.
     */
    public function prosesMetode(Request $request, Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);
        $metode = $request->input('metode');

        if ($tagihan->isWajibLunas() && $metode == 'cicil') {
            return redirect()->back()->with('error', 'Tagihan ini tidak dapat dicicil. Harus dibayar lunas.');
        }

        if ($metode == 'cicil') {
            return redirect()->route('mahasiswa.pembayaran.cicil', $tagihan->tagihan_id);

        } elseif ($metode == 'transfer') {
            return redirect()->route('mahasiswa.pembayaran.show', $tagihan->tagihan_id);

        } elseif ($metode == 'tunai') {
            $tagihan->update(['status' => 'Menunggu Pembayaran Tunai']);

            return redirect()
                ->route('mahasiswa.pembayaran.index')
                ->with('success', 'Pilihan bayar tunai berhasil. Silakan lakukan pembayaran ke kasir.');
        }

        return redirect()->back()->with('error', 'Metode pembayaran tidak valid.');
    }

    /**
     * Tampilkan form cicilan
     */
    public function cicil(Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);

        if ($tagihan->isWajibLunas()) {
            return redirect()->route('mahasiswa.pembayaran.index')
                ->with('error', 'Tagihan ini tidak dapat dicicil. Harus dibayar lunas.');
        }

        if (!in_array($tagihan->status, ['Belum Lunas', 'Ditolak'])) {
            return redirect()->route('mahasiswa.pembayaran.index')
                ->with('error', 'Tagihan ini sedang diproses atau sudah lunas.');
        }

        $sisaPokok = $tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan;

        return view('mahasiswa.cicil', compact('tagihan', 'sisaPokok'));
    }

    /**
     * Menyimpan konfirmasi pembayaran dengan upload bukti.
     */
    public function storeKonfirmasi(Request $request, Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);

        $isCicilan = $request->has('is_cicilan') && $request->input('is_cicilan') == '1';
        $sisaPokok = $tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan;

        $minBayar = min(50000, $sisaPokok);
        $request->validate([
            'bukti_pembayaran' => 'required|file|max:2048',
            'jumlah_bayar' => $isCicilan ? 'required|numeric|min:'.$minBayar.'|max:' . $sisaPokok : 'nullable',
        ]);

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
            $jumlahBayar = $request->input('jumlah_bayar');
            if ($jumlahBayar <= 0) {
                return back()->withErrors(['jumlah_bayar' => 'Jumlah pembayaran harus lebih dari 0.'])->withInput();
            }
            if ($jumlahBayar > $sisaPokok) {
                return back()->withErrors(['jumlah_bayar' => 'Jumlah pembayaran tidak boleh melebihi sisa pokok.'])->withInput();
            }
            if ($jumlahBayar < $minBayar && $sisaPokok >= 50000) {
                return back()->withErrors(['jumlah_bayar' => 'Minimal pembayaran cicilan adalah Rp 50.000.'])->withInput();
            }
        }

        $folder = 'bukti_pembayaran';
        $path = $file->store($folder, 'public');

        $konfirmasi = KonfirmasiPembayaran::create([
            'tagihan_id' => $tagihan->tagihan_id,
            'file_bukti_pembayaran' => $path,
            'status_verifikasi' => 'Menunggu Verifikasi',
            'is_cicilan' => $isCicilan,
            'jumlah_bayar' => $isCicilan ? $request->input('jumlah_bayar') : null,
        ]);

        $tagihan->update(['status' => 'Menunggu Verifikasi Transfer']);

        return redirect()->route('mahasiswa.pembayaran.index')
                         ->with('success', 'Bukti pembayaran berhasil di-upload dan sedang menunggu verifikasi.');
    }

    /**
     * Proses cicilan via transfer (upload bukti)
     */
    public function storeCicilTransfer(Request $request, Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);

        if ($tagihan->isWajibLunas()) {
            return redirect()->route('mahasiswa.pembayaran.index')
                ->with('error', 'Tagihan ini tidak dapat dicicil. Harus dibayar lunas.');
        }

        $sisaPokok = $tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan;

        $minBayar = min(50000, $sisaPokok);
        $request->validate([
            'bukti_pembayaran' => 'required|file|max:2048',
            'jumlah_bayar' => 'required|numeric|min:'.$minBayar.'|max:' . $sisaPokok,
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

        $jumlahBayar = $request->input('jumlah_bayar');
        if ($jumlahBayar <= 0 || $jumlahBayar > $sisaPokok) {
            return back()->withErrors(['jumlah_bayar' => 'Jumlah pembayaran tidak valid.'])->withInput();
        }
        if ($jumlahBayar < $minBayar && $sisaPokok >= 50000) {
            return back()->withErrors(['jumlah_bayar' => 'Minimal pembayaran cicilan adalah Rp 50.000.'])->withInput();
        }

        $folder = 'bukti_pembayaran';
        $path = $file->store($folder, 'public');

        KonfirmasiPembayaran::create([
            'tagihan_id' => $tagihan->tagihan_id,
            'file_bukti_pembayaran' => $path,
            'status_verifikasi' => 'Menunggu Verifikasi',
            'is_cicilan' => true,
            'jumlah_bayar' => $jumlahBayar,
        ]);

        $tagihan->update(['status' => 'Menunggu Verifikasi Transfer']);

        return redirect()->route('mahasiswa.pembayaran.index')
                         ->with('success', 'Bukti pembayaran cicilan berhasil di-upload dan sedang menunggu verifikasi.');
    }
}

