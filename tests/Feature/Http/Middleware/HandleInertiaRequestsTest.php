<?php

namespace Tests\Feature\Http\Middleware;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_shares_roles_and_permissions_of_authenticated_user(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $coordinator = User::factory()->create()->assignRole('coordinator');

        $response = $this->actingAs($coordinator)->get(route('dashboard'));

        $response->assertOk();
        $this->assertSame(['coordinator'], $response->inertiaProps('auth.roles'));
        $this->assertEqualsCanonicalizing([
            'create requisitions',
            'register deposits',
            'create expense reports',
            'approve team requisitions',
            'approve team expense reports',
        ], $response->inertiaProps('auth.permissions'));
    }

    public function test_shares_empty_roles_and_permissions_with_guests(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $this->assertSame([], $response->inertiaProps('auth.roles'));
        $this->assertSame([], $response->inertiaProps('auth.permissions'));
    }
}
