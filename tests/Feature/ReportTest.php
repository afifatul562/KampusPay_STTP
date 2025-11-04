<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Tagihan;
use App\Models\TarifMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_admin_can_preview_report_with_data(): void
    {
        // Arrange: admin user
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'admin_'.Str::random(5)]);
        Sanctum::actingAs($admin);

        // Buat mahasiswa + tagihan pada bulan berjalan
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

        Tagihan::create([
            'mahasiswa_id' => $detail->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'kode_pembayaran' => 'INV-REP-'.Str::random(4),
            'jumlah_tagihan' => 1000000,
            'tanggal_jatuh_tempo' => now()->addDays(10),
            'status' => 'Belum Lunas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act
        $payload = ['jenis_laporan' => 'pembayaran', 'periode' => now()->format('Y-m')];
        $response = $this->postJson(route('admin.reports.preview'), $payload);

        // Assert
        $response->assertOk();
        $response->assertJsonStructure(['data']);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_admin_can_generate_report_and_history_saved(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $student = User::factory()->create(['role' => 'mahasiswa']);
        $detail = $student->mahasiswaDetail()->create([
            'npm' => 'NPM'.Str::random(5),
            'program_studi' => 'TI',
            'angkatan' => '2023',
            'semester_aktif' => 3,
            'status' => 'Aktif',
        ]);
        $tarif = TarifMaster::create(['nama_pembayaran' => 'UKT', 'nominal' => 1000000, 'program_studi' => 'TI', 'angkatan' => '2023']);
        Tagihan::create([
            'mahasiswa_id' => $detail->mahasiswa_id,
            'tarif_id' => $tarif->tarif_id,
            'kode_pembayaran' => 'INV-REP2-'.Str::random(4),
            'jumlah_tagihan' => 1000000,
            'tanggal_jatuh_tempo' => now()->addDays(5),
            'status' => 'Belum Lunas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act
        $payload = ['jenis_laporan' => 'pembayaran', 'periode' => now()->format('Y-m')];
        $response = $this->postJson(route('admin.reports.store'), $payload);

        // Assert
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('reports', [
            'jenis_laporan' => 'pembayaran',
            'periode' => now()->format('Y-m'),
        ]);
    }

    public function test_preview_is_throttled_after_limit(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $payload = ['jenis_laporan' => 'pembayaran', 'periode' => now()->format('Y-m')];

        // Act: panggil lebih dari 20x dalam 1 menit
        $last = null;
        for ($i = 0; $i < 21; $i++) {
            $last = $this->postJson(route('admin.reports.preview'), $payload);
        }

        // Assert: request ke-21 seharusnya 429
        $last->assertStatus(429);
    }
}


