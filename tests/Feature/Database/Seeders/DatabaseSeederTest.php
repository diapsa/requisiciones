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

    public function test_creates_one_test_user_per_role_in_local_environment(): void
    {
        $this->app['env'] = 'local';

        $this->seed();

        $requester = User::query()->where('email', 'solicitante@requisiciones.test')->sole();
        $coordinator = User::query()->where('email', 'coordinador@requisiciones.test')->sole();
        $manager = User::query()->where('email', 'gerente@requisiciones.test')->sole();

        $this->assertTrue($requester->hasRole('requester'));
        $this->assertTrue($coordinator->hasRole('coordinator'));
        $this->assertTrue($manager->hasRole('manager'));
        $this->assertSame($coordinator->id, $requester->coordinator_id);
        $this->assertTrue(Hash::check('password', $requester->password));
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
