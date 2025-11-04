<?php

namespace Tests\Feature;

use App\Models\TarifMaster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TarifCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_admin_can_create_update_and_delete_tarif(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Create
        $createPayload = [
            'nama_pembayaran' => 'Uang Skripsi',
            'nominal' => 2500000,
            'program_studi' => 'S1 Informatika',
            'angkatan' => '2024',
        ];
        $createRes = $this->postJson(route('admin.tarif.store'), $createPayload);
        $createRes->assertCreated();
        $tarifId = $createRes->json('data.tarif_id');
        $this->assertNotNull($tarifId);
        $this->assertDatabaseHas('tarif_master', [
            'tarif_id' => $tarifId,
            'nama_pembayaran' => 'Uang Skripsi',
        ]);

        // Update
        $updatePayload = [
            'nominal' => 3000000,
            'program_studi' => null, // Semua Jurusan
            'angkatan' => null, // Semua Angkatan
        ];
        $updateRes = $this->putJson(route('admin.tarif.update', $tarifId), $updatePayload);
        $updateRes->assertOk();
        $this->assertDatabaseHas('tarif_master', [
            'tarif_id' => $tarifId,
            'nominal' => 3000000,
            'program_studi' => null,
            'angkatan' => null,
        ]);

        // Destroy
        $deleteRes = $this->deleteJson(route('admin.tarif.destroy', $tarifId));
        $deleteRes->assertNoContent();
        $this->assertDatabaseMissing('tarif_master', [ 'tarif_id' => $tarifId ]);
    }

    public function test_tarif_validation_errors(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Missing nama_pembayaran
        $badRes = $this->postJson(route('admin.tarif.store'), [
            'nominal' => -1,
        ]);
        $badRes->assertStatus(422);
    }
}


