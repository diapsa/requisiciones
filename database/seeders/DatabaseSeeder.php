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
