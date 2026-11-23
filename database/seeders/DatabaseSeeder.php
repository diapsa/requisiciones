<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $this->createAdministrator();

        if (app()->isLocal()) {
            $this->createTestUsers();
        }
    }

    /**
     * Create one verified user per role for local testing; all of them use the password "password".
     */
    private function createTestUsers(): void
    {
        $manager = $this->createTestUser('gerente@requisiciones.test', 'Gerente de Prueba', 'manager');
        $coordinator = $this->createTestUser('coordinador@requisiciones.test', 'Coordinador de Prueba', 'coordinator');
        $requester = $this->createTestUser('solicitante@requisiciones.test', 'Solicitante de Prueba', 'requester');

        $requester->forceFill(['coordinator_id' => $coordinator->id])->save();
    }

    private function createTestUser(string $email, string $name, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'password', 'email_verified_at' => now()],
        );

        $user->assignRole($role);

        return $user;
    }

    /**
     * Create the first administrator from ADMIN_EMAIL and ADMIN_PASSWORD, since there is no public registration.
     */
    private function createAdministrator(): void
    {
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        if (blank($email) || blank($password)) {
            $this->command->warn('ADMIN_EMAIL or ADMIN_PASSWORD is empty; the administrator was not created.');

            return;
        }

        $administrator = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Administrador', 'password' => $password, 'email_verified_at' => now()],
        );

        $administrator->assignRole('admin');
    }
}
