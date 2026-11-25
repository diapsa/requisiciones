<?php

namespace Tests\Feature\Database\Seeders;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RolePermissionSeederTest extends TestCase
{
    use RefreshDatabase;

    private const array ALL_PERMISSIONS = [
        'create requisitions',
        'register deposits',
        'create expense reports',
        'approve team requisitions',
        'approve team expense reports',
        'approve requisitions',
        'review expense reports',
        'close settlements',
        'view all requisitions',
        'manage users',
        'manage catalogs',
    ];

    /**
     * @return array<string, array{string, list<string>}>
     */
    public static function rolePermissions(): array
    {
        return [
            'requester' => ['requester', [
                'create requisitions',
                'register deposits',
                'create expense reports',
            ]],
            'coordinator' => ['coordinator', [
                'create requisitions',
                'register deposits',
                'create expense reports',
                'approve team requisitions',
                'approve team expense reports',
            ]],
            'manager' => ['manager', [
                'create requisitions',
                'register deposits',
                'create expense reports',
                'approve requisitions',
                'review expense reports',
                'close settlements',
                'view all requisitions',
            ]],
            'admin' => ['admin', self::ALL_PERMISSIONS],
        ];
    }

    /**
     * @param  list<string>  $expectedPermissions
     */
    #[DataProvider('rolePermissions')]
    public function test_role_grants_exactly_its_permissions(string $role, array $expectedPermissions): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create()->assignRole($role);

        $grantedPermissions = array_values(array_filter(
            self::ALL_PERMISSIONS,
            fn (string $permission): bool => $user->can($permission),
        ));

        $this->assertSame($expectedPermissions, $grantedPermissions);
    }
}
