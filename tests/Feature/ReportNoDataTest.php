<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportNoDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_generate_report_without_data_returns_404(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Pastikan tidak ada tagihan di periode jauh
        $payload = [
            'jenis_laporan' => 'pembayaran',
            'periode' => '1999-01',
        ];

        // Act
        $response = $this->postJson(route('admin.reports.store'), $payload);

        // Assert
        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }
}


