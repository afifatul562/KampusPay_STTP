<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        $this->artisan('migrate', ['--force' => true]);
        Storage::fake('public');
    }

    public function test_admin_can_download_report(): void
    {
        // Arrange
        $admin = User::factory()->create(['role' => 'admin']);
        $fileName = 'laporan_pembayaran_2025_11_'.time().'.pdf';
        // Simulasi file di storage/app/public/reports
        Storage::disk('public')->put('reports/'.$fileName, 'PDFCONTENT');
        $report = Report::create(['jenis_laporan' => 'pembayaran', 'periode' => now()->format('Y-m'), 'file_name' => $fileName]);

        // Act
        $response = $this->actingAs($admin)->get(route('admin.reports.download', ['report' => $report->id]));

        // Assert: file response (200)
        $response->assertOk();
    }

    public function test_non_admin_is_forbidden_to_download_report(): void
    {
        // Arrange
        $kasir = User::factory()->create(['role' => 'kasir']);
        $fileName = 'laporan_pembayaran_2025_11_'.time().'.pdf';
        Storage::disk('public')->put('reports/'.$fileName, 'PDFCONTENT');
        $report = Report::create(['jenis_laporan' => 'pembayaran', 'periode' => now()->format('Y-m'), 'file_name' => $fileName]);

        // Act
        $response = $this->actingAs($kasir)->get(route('admin.reports.download', ['report' => $report->id]));

        // Assert: CheckRole middleware mengembalikan 403
        $response->assertStatus(403);
    }
}


