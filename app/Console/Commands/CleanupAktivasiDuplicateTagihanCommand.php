<?php

namespace App\Console\Commands;

use App\Models\KonfirmasiPembayaran;
use App\Models\MahasiswaDetail;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TagihanSlipAlias;
use App\Support\TagihanDedup;
use App\Support\TarifBillingRules;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupAktivasiDuplicateTagihanCommand extends Command
{
    protected $signature = 'tagihan:cleanup-aktivasi-duplikat
                            {--dry-run : Tampilkan kandidat tanpa menghapus}
                            {--npm= : Batasi ke satu mahasiswa (NPM)}';

    protected $description = 'Hapus tagihan duplikat hasil aktivasi jika tagihan setara sudah ada (mis. dari import)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $npm = trim((string) $this->option('npm'));

        if ($dryRun) {
            $this->warn('Mode dry-run: tidak ada data yang dihapus.');
        }

        $query = Tagihan::query()
            ->with(['tarif', 'mahasiswa'])
            ->where('kode_pembayaran', 'like', 'INV-KP-%')
            ->whereIn('status', ['Belum Lunas', 'Ditolak'], 'and', false);

        if ($npm !== '') {
            $mhs = MahasiswaDetail::query()->where('npm', $npm)->first();
            if (! $mhs) {
                $this->error("Mahasiswa NPM {$npm} tidak ditemukan.");

                return self::FAILURE;
            }
            $query->where('mahasiswa_id', $mhs->mahasiswa_id);
        }

        $candidates = $query->orderBy('tagihan_id')->get();
        $removed = 0;
        $rows = [];

        foreach ($candidates as $candidate) {
            $mahasiswa = $candidate->mahasiswa;
            if (! $mahasiswa) {
                continue;
            }

            $namaTarif = (string) optional($candidate->tarif)->nama_pembayaran;
            $conflict = TagihanDedup::findConflictingTagihan($candidate, $mahasiswa);
            $wrongSem1 = $namaTarif !== ''
                && TarifBillingRules::isSem1OnlyTarifName(mb_strtolower($namaTarif))
                && (int) $mahasiswa->semester_aktif !== 1;

            if (! $conflict && ! $wrongSem1) {
                continue;
            }

            $rows[] = [
                $candidate->tagihan_id,
                $mahasiswa->npm,
                $namaTarif,
                $candidate->kode_pembayaran,
                $candidate->status,
                $conflict?->tagihan_id ?? '-',
                $conflict?->kode_pembayaran ?? ($wrongSem1 ? '(bukan semester 1)' : '-'),
                $conflict?->status ?? '-',
            ];

            if (! $dryRun) {
                $this->deleteTagihan($candidate);
            }

            $removed++;
        }

        if ($rows === []) {
            $this->info('Tidak ada duplikat aktivasi yang perlu dibersihkan.');

            return self::SUCCESS;
        }

        $this->table(
            ['Hapus ID', 'NPM', 'Jenis', 'Kode hapus', 'Status hapus', 'Tetap ID', 'Kode tetap', 'Status tetap'],
            $rows
        );

        $verb = $dryRun ? 'Akan dihapus' : 'Dihapus';

        $this->info("{$verb}: {$removed} tagihan duplikat aktivasi.");

        return self::SUCCESS;
    }

    private function deleteTagihan(Tagihan $tagihan): void
    {
        $tagihanId = $tagihan->tagihan_id;

        DB::transaction(function () use ($tagihanId) {
            KonfirmasiPembayaran::query()->where('tagihan_id', $tagihanId)->delete();
            Pembayaran::query()->where('tagihan_id', $tagihanId)->delete();
            TagihanSlipAlias::query()->where('tagihan_id', $tagihanId)->delete();
            Tagihan::destroy($tagihanId);
        });
    }
}
