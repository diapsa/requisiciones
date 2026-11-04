<?php

namespace Tests\Feature\Providers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_granted_abilities_that_are_not_permissions(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create()->assignRole('admin');

        $isGranted = $admin->can('delete any requisition');

        $this->assertTrue($isGranted);
    }
}
