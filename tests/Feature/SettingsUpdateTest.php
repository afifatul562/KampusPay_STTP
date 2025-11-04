<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SettingsUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $payload = [
            'app_name' => 'KampusPay STTP',
            'academic_year' => '2025/2026',
            'semester' => 'Ganjil',
            'bank_name' => 'Bank Nagari',
            'account_holder' => 'Yayasan STTP',
            'account_number' => '1234567890',
        ];

        $res = $this->postJson(route('admin.settings.system.update'), $payload);
        $res->assertOk();
        $this->assertDatabaseHas('settings', ['key' => 'app_name', 'value' => 'KampusPay STTP']);
        $this->assertDatabaseHas('settings', ['key' => 'academic_year', 'value' => '2025/2026']);
        $this->assertDatabaseHas('settings', ['key' => 'semester', 'value' => 'Ganjil']);
        $this->assertDatabaseHas('settings', ['key' => 'bank_name', 'value' => 'Bank Nagari']);
        $this->assertDatabaseHas('settings', ['key' => 'account_holder', 'value' => 'Yayasan STTP']);
        $this->assertDatabaseHas('settings', ['key' => 'account_number', 'value' => '1234567890']);
    }

    public function test_update_settings_validation_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $payload = [
            'app_name' => '', // required
            'academic_year' => '',
            'semester' => '',
        ];

        $res = $this->postJson(route('admin.settings.system.update'), $payload);
        $res->assertStatus(422);
    }
}


