<?php

namespace Tests\Feature\Providers;

use App\Models\ExpenseReport;
use App\Models\Payment;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @return array<string, array{class-string<Model>, string}>
     */
    public static function morphAliases(): array
    {
        return [
            'user' => [User::class, 'user'],
            'requisition' => [Requisition::class, 'requisition'],
            'expense report' => [ExpenseReport::class, 'expense_report'],
            'payment' => [Payment::class, 'payment'],
        ];
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    #[DataProvider('morphAliases')]
    public function test_polymorphic_columns_store_short_alias_instead_of_class_name(string $modelClass, string $expectedAlias): void
    {
        $model = new $modelClass;

        $morphClass = $model->getMorphClass();

        $this->assertSame($expectedAlias, $morphClass);
    }
}
