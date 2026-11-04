<?php

namespace Tests\Feature\Database\Seeders;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_verified_administrator_from_configured_credentials(): void
    {
        config(['app.admin_email' => 'admin@example.com', 'app.admin_password' => 'secret-password']);

        $this->seed();

        $administrator = User::query()->where('email', 'admin@example.com')->sole();
        $this->assertTrue($administrator->hasRole('admin'));
        $this->assertNotNull($administrator->email_verified_at);
        $this->assertTrue(Hash::check('secret-password', $administrator->password));
    }

    #[TestWith([null, 'secret-password'], 'missing email')]
    #[TestWith(['admin@example.com', null], 'missing password')]
    public function test_does_not_create_administrator_when_credentials_are_missing(?string $email, ?string $password): void
    {
        config(['app.admin_email' => $email, 'app.admin_password' => $password]);

        $this->seed();

        $this->assertDatabaseCount('users', 0);
    }
}
