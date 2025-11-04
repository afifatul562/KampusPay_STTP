<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        $this->artisan('migrate', ['--force' => true]);
    }

    public function test_login_is_throttled_after_limit(): void
    {
        // Arrange: buat user valid
        $user = User::factory()->create(['password' => 'password']);

        // Act: kirim 11 request login dengan password salah
        $last = null;
        for ($i = 0; $i < 11; $i++) {
            $last = $this->post('/login', [
                'username' => $user->username ?? $user->email,
                'password' => 'wrong-password',
            ]);
        }

        // Assert: request ke-11 terkena throttle 429
        $last->assertStatus(429);
    }
}


