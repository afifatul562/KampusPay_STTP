<?php

namespace Tests\Feature;

use App\Models\Tagihan;
use App\Models\TarifMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KasirProcessPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_kasir_process_payment_sets_tagihan_to_lunas(): void
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
            'nominal' => 2000000,
            'program_studi' => 'TI',
            'angkatan' => '2023',
        ]);

        $tagihan = Tagihan::create([
            'mahasiswa_id' => $detail->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'kode_pembayaran' => 'INV-KSR-'.Str::random(4),
            'jumlah_tagihan' => 2000000,
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'status' => 'Belum Lunas',
        ]);

        // Act
        $payload = [
            'tagihan_ids' => [$tagihan->tagihan_id],
            'metode_pembayaran' => 'Tunai',
        ];
        $response = $this->postJson(route('kasir.process-payment'), $payload);

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('tagihan', [
            'tagihan_id' => $tagihan->tagihan_id,
            'status' => 'Lunas',
        ]);
        // Pembayaran tercatat
        $this->assertDatabaseHas('pembayaran', [
            'tagihan_id' => $tagihan->tagihan_id,
            'metode_pembayaran' => 'Tunai',
        ]);
    }
}


