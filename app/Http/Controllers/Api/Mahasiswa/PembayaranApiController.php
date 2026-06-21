<?php

namespace App\Http\Controllers\Api\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KonfirmasiPembayaran;
use App\Models\Tagihan;
use App\Services\KasirTransferNotificationService;
use App\Support\TagihanPayability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranApiController extends Controller
{
    public function pilihMetode(Request $request, Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);

        if ($response = $this->guardPayable($tagihan)) {
            return $response;
        }

        $metode = $request->input('metode');

        if (!in_array($metode, ['tunai', 'transfer', 'cicil'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Metode pembayaran tidak valid.',
            ], 422);
        }

        if ($tagihan->isWajibLunas() && $metode === 'cicil') {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan ini tidak dapat dicicil. Harus dibayar lunas.',
            ], 422);
        }

        if (!in_array($tagihan->status, ['Belum Lunas', 'Ditolak']) && $metode !== 'tunai') {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan ini sedang diproses atau sudah lunas.',
            ], 422);
        }

        if ($metode === 'tunai') {
            $sisa = max(0.0, (float) ($tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan) - (float) ($tagihan->total_angsuran ?? 0));
            if ($sisa <= 0) {
                $sisa = (float) $tagihan->jumlah_tagihan;
            }
            $tagihan->fill([
                'status' => 'Menunggu Pembayaran Tunai',
                'sisa_pokok' => $sisa,
            ]);
            $tagihan->save();

            return response()->json([
                'success' => true,
                'message' => 'Pilihan bayar tunai berhasil. Silakan lakukan pembayaran ke kasir.',
                'next_action' => 'tunai',
                'status_tagihan' => $tagihan->status,
            ]);
        }

        // Untuk transfer / cicil, mobile app akan lanjut ke upload bukti
        return response()->json([
            'success' => true,
            'message' => 'Metode pembayaran tersimpan. Silakan upload bukti pembayaran.',
            'next_action' => $metode,
        ]);
    }

    public function konfirmasiTransfer(Request $request, Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);

        if ($response = $this->guardPayable($tagihan)) {
            return $response;
        }

        $request->validate([
            'bukti_pembayaran' => 'required|file|max:2048',
        ]);

        $file = $request->file('bukti_pembayaran');
        $mime = $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!$mime || !in_array(strtolower($mime), $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Format file tidak didukung. Hanya JPG/PNG.',
            ], 422);
        }
        if (!@getimagesize($file->getPathname())) {
            return response()->json([
                'success' => false,
                'message' => 'File bukan gambar yang valid.',
            ], 422);
        }

        $folder = 'bukti_pembayaran';
        $path = $file->store($folder, 'public');

        $konfirmasi = KonfirmasiPembayaran::create([
            'tagihan_id' => $tagihan->tagihan_id,
            'file_bukti_pembayaran' => $path,
            'status_verifikasi' => 'Menunggu Verifikasi',
            'is_cicilan' => false,
            'jumlah_bayar' => null,
        ]);

        $tagihan->fill(['status' => 'Menunggu Verifikasi Transfer']);
        $tagihan->save();

        app(KasirTransferNotificationService::class)->notifyForKonfirmasi($konfirmasi);

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran berhasil di-upload dan sedang menunggu verifikasi.',
            'status_tagihan' => $tagihan->status,
        ]);
    }

    public function konfirmasiCicilan(Request $request, Tagihan $tagihan)
    {
        $this->authorize('view', $tagihan);

        if ($response = $this->guardPayable($tagihan)) {
            return $response;
        }

        if ($tagihan->isWajibLunas()) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan ini tidak dapat dicicil. Harus dibayar lunas.',
            ], 422);
        }

        $sisaPokok = $tagihan->sisa_pokok ?? $tagihan->jumlah_tagihan;

        $request->validate([
            'bukti_pembayaran' => 'required|file|max:2048',
            'jumlah_bayar' => 'required|numeric|min:1|max:' . $sisaPokok,
        ]);

        $file = $request->file('bukti_pembayaran');
        $mime = $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!$mime || !in_array(strtolower($mime), $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Format file tidak didukung. Hanya JPG/PNG.',
            ], 422);
        }
        if (!@getimagesize($file->getPathname())) {
            return response()->json([
                'success' => false,
                'message' => 'File bukan gambar yang valid.',
            ], 422);
        }

        $jumlahBayar = (float) $request->input('jumlah_bayar');
        if (!$tagihan->cicilanAmountValid($jumlahBayar)) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pembayaran tidak valid.',
            ], 422);
        }

        $folder = 'bukti_pembayaran';
        $path = $file->store($folder, 'public');

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

        return response()->json([
            'success' => true,
            'message' => 'Bukti pembayaran cicilan berhasil di-upload dan sedang menunggu verifikasi.',
            'status_tagihan' => $tagihan->status,
        ]);
    }

    protected function guardPayable(Tagihan $tagihan): ?JsonResponse
    {
        $message = TagihanPayability::violationMessage($tagihan, (int) $tagihan->mahasiswa_id);
        if ($message) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return null;
    }
}

