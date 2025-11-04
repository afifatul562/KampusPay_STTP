<?php

namespace Tests\Feature;

use App\Models\KonfirmasiPembayaran;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\TarifMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KasirApproveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_kasir_can_approve_and_create_pembayaran(): void
    {
        // Arrange
        $kasir = User::factory()->create(['role' => 'kasir']);
        Sanctum::actingAs($kasir);

        $student = User::factory()->create(['role' => 'mahasiswa']);
        $detail = $student->mahasiswaDetail()->create([
            'npm' => 'NPM'.Str::random(5),
            'program_studi' => 'TI',
            'angkatan' => '2023',
            'semester_aktif' => 3,
            'status' => 'Aktif',
        ]);

        $tarif = TarifMaster::create([
            'nama_pembayaran' => 'UKT',
            'nominal' => 1000000,
            'program_studi' => 'TI',
            'angkatan' => '2023',
        ]);

        $tagihan = Tagihan::create([
            'mahasiswa_id' => $detail->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'kode_pembayaran' => 'INV-TEST-APP',
            'jumlah_tagihan' => 1000000,
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'status' => 'Belum Lunas',
        ]);

        $konfirmasi = KonfirmasiPembayaran::create([
            'tagihan_id' => $tagihan->tagihan_id,
            'file_bukti_pembayaran' => 'bukti/test.jpg',
            'status_verifikasi' => 'Menunggu Verifikasi',
        ]);

        // Act
        $response = $this->postJson(route('kasir.verifikasi.approve', $konfirmasi->konfirmasi_id));

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('pembayaran', [
            'tagihan_id' => $tagihan->tagihan_id,
            'konfirmasi_id' => $konfirmasi->konfirmasi_id,
            'metode_pembayaran' => 'Transfer',
        ]);
        $this->assertDatabaseHas('tagihan', [
            'tagihan_id' => $tagihan->tagihan_id,
            'status' => 'Lunas',
        ]);
        $this->assertDatabaseHas('konfirmasi_pembayaran', [
            'konfirmasi_id' => $konfirmasi->konfirmasi_id,
            'status_verifikasi' => 'Disetujui',
        ]);
    }
}


