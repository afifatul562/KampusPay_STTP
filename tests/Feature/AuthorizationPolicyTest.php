<?php

namespace Tests\Feature;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TarifMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Gunakan sqlite memory jika tersedia
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        // Jalankan migrasi default
        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_mahasiswa_cannot_view_other_students_tagihan_page(): void
    {
        // Arrange: buat dua mahasiswa dan masing-masing detail + tagihan
        $studentA = User::factory()->create(['role' => 'mahasiswa']);
        $studentA->mahasiswaDetail()->create(['npm' => 'A001', 'program_studi' => 'TI', 'angkatan' => '2023', 'semester_aktif' => 3, 'status' => 'Aktif']);

        $studentB = User::factory()->create(['role' => 'mahasiswa']);
        $studentB->mahasiswaDetail()->create(['npm' => 'B001', 'program_studi' => 'TI', 'angkatan' => '2023', 'semester_aktif' => 3, 'status' => 'Aktif']);

        $tarif = TarifMaster::create(['nama_pembayaran' => 'UKT', 'nominal' => 1000000, 'program_studi' => 'TI', 'angkatan' => '2023']);
        $tagihanA = Tagihan::create([
            'mahasiswa_id' => $studentA->mahasiswaDetail->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'kode_pembayaran' => 'INV-TEST-A',
            'jumlah_tagihan' => 1000000,
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'status' => 'Belum Lunas',
        ]);

        // Act: login sebagai student B, coba akses halaman tagihan student A
        $response = $this->actingAs($studentB)->get(route('mahasiswa.pembayaran.show', $tagihanA->tagihan_id));

        // Assert
        $response->assertStatus(403);
    }

    public function test_mahasiswa_cannot_download_other_students_kwitansi(): void
    {
        // Arrange
        $studentA = User::factory()->create(['role' => 'mahasiswa']);
        $studentA->mahasiswaDetail()->create(['npm' => 'A001', 'program_studi' => 'TI', 'angkatan' => '2023', 'semester_aktif' => 3, 'status' => 'Aktif']);

        $studentB = User::factory()->create(['role' => 'mahasiswa']);
        $studentB->mahasiswaDetail()->create(['npm' => 'B001', 'program_studi' => 'TI', 'angkatan' => '2023', 'semester_aktif' => 3, 'status' => 'Aktif']);

        $tarif = TarifMaster::create(['nama_pembayaran' => 'UKT', 'nominal' => 1000000, 'program_studi' => 'TI', 'angkatan' => '2023']);
        $tagihanA = Tagihan::create([
            'mahasiswa_id' => $studentA->mahasiswaDetail->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'kode_pembayaran' => 'INV-TEST-B',
            'jumlah_tagihan' => 1000000,
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'status' => 'Lunas',
        ]);

        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihanA->tagihan_id,
            'diverifikasi_oleh' => $studentA->id, // tidak penting untuk test ini
            'tanggal_bayar' => now(),
            'metode_pembayaran' => 'Tunai',
        ]);

        // Act: student B mencoba download kwitansi milik student A
        $response = $this->actingAs($studentB)->get(route('mahasiswa.kwitansi.download', $pembayaran->pembayaran_id));

        // Assert
        $response->assertStatus(403);
    }
}


