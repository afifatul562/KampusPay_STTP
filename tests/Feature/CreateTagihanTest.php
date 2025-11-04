<?php

namespace Tests\Feature;

use App\Models\MahasiswaDetail;
use App\Models\TarifMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateTagihanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_admin_can_create_tagihan_via_api(): void
    {
        // Arrange: admin user with Sanctum
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin_'.Str::random(6),
        ]);
        Sanctum::actingAs($admin);

        // Mahasiswa + tarif data
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $detail = $student->mahasiswaDetail()->create([
            'npm' => 'NPM'.Str::random(6),
            'program_studi' => 'TI',
            'angkatan' => '2023',
            'semester_aktif' => 3,
            'status' => 'Aktif',
        ]);

        $tarif = TarifMaster::create([
            'nama_pembayaran' => 'UKT',
            'nominal' => 1500000,
            'program_studi' => 'TI',
            'angkatan' => '2023',
        ]);

        // Act: call API
        $payload = [
            'mahasiswa_id' => $detail->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'jumlah_tagihan' => 1500000,
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
        ];

        $response = $this->postJson(route('admin.payments.tagihan.create'), $payload);

        // Assert
        $response->assertCreated();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('tagihan', [
            'mahasiswa_id' => $detail->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'jumlah_tagihan' => 1500000,
            'status' => 'Belum Lunas',
        ]);
    }
}


