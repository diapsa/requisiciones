<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ExpenseRate;
use App\Models\ExpenseType;
use App\Models\RequisitionItem;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExpenseTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('expense-types.index'))->assertRedirect(route('login'));
    }

    public function test_forbids_users_without_the_manage_catalogs_permission(): void
    {
        $user = $this->userWithRole('requester');

        $this->actingAs($user)->get(route('expense-types.index'))->assertForbidden();
        $this->actingAs($user)->post(route('expense-types.store'), $this->validPayload())->assertForbidden();

        $this->assertDatabaseCount('expense_types', 0);
    }

    public function test_lists_expense_types_in_sort_order_with_their_rates_and_uses(): void
    {
        $meals = ExpenseType::factory()->create(['name' => 'Comida', 'sort_order' => 3]);
        ExpenseType::factory()->create(['name' => 'Combustibles y lubricantes', 'sort_order' => 1]);
        ExpenseRate::factory()->for($meals)->count(2)->create();
        RequisitionItem::factory()->for($meals)->create();

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('expense-types.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('expense-types/index')
                ->has('expenseTypes', 2)
                ->where('expenseTypes.0.name', 'Combustibles y lubricantes')
                ->where('expenseTypes.0.expense_rates_count', 0)
                ->where('expenseTypes.1.name', 'Comida')
                ->where('expenseTypes.1.expense_rates_count', 2)
                ->where('expenseTypes.1.requisition_items_count', 1)
                ->where('expenseTypes.1.expenses_count', 0)
            );
    }

    public function test_creates_an_expense_type_with_a_normalized_code_and_unit(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('expense-types.store'), $this->validPayload(['code' => ' Car Wash ', 'default_unit' => 'ticket']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('expense-types.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertDatabaseHas('expense_types', [
            'code' => 'car_wash',
            'name' => 'Lavado de auto',
            'calculation_method' => 'fixed',
            'default_unit' => 'TICKET',
            'is_requestable' => true,
            'sort_order' => 14,
        ]);
    }

    public function test_creates_an_expense_type_that_cannot_be_requested_when_the_checkbox_is_unchecked(): void
    {
        $payload = $this->validPayload();
        unset($payload['is_requestable']);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('expense-types.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->assertFalse(ExpenseType::query()->sole()->is_requestable);
    }

    /**
     * @param  array<string, string>  $overrides
     */
    #[DataProvider('invalidExpenseTypes')]
    public function test_rejects_an_invalid_expense_type(array $overrides, string $field, string $message): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('expense-types.store'), $this->validPayload($overrides))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('expense_types', 0);
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidExpenseTypes(): array
    {
        return [
            'missing code' => [['code' => ''], 'code', 'Escribe la clave del tipo de gasto.'],
            'code starting with a number' => [['code' => '1car_wash'], 'code', 'La clave debe empezar con una letra y solo llevar minúsculas sin acento, números y guion bajo.'],
            'code with an accent' => [['code' => 'lavandería'], 'code', 'La clave debe empezar con una letra y solo llevar minúsculas sin acento, números y guion bajo.'],
            'missing name' => [['name' => ''], 'name', 'Escribe el nombre del tipo de gasto.'],
            'unknown calculation method' => [['calculation_method' => 'per_kilometer'], 'calculation_method', 'Elige un método de cálculo de la lista.'],
            'missing unit' => [['default_unit' => ''], 'default_unit', 'Escribe la unidad.'],
            'unit longer than 20 characters' => [['default_unit' => str_repeat('A', 21)], 'default_unit', 'La unidad no puede tener más de 20 caracteres.'],
            'negative sort order' => [['sort_order' => '-1'], 'sort_order', 'El orden no puede ser negativo.'],
            'sort order over the column limit' => [['sort_order' => '65536'], 'sort_order', 'El orden no puede pasar de 65,535.'],
            'fractional sort order' => [['sort_order' => '1.5'], 'sort_order', 'El orden debe ser un número entero.'],
        ];
    }

    public function test_rejects_a_duplicate_code(): void
    {
        ExpenseType::factory()->create(['code' => 'car_wash']);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('expense-types.store'), $this->validPayload(['code' => 'Car Wash']))
            ->assertSessionHasErrors(['code' => 'Ya existe un tipo de gasto con esa clave.']);

        $this->assertDatabaseCount('expense_types', 1);
    }

    public function test_updates_an_expense_type_keeping_its_code(): void
    {
        $expenseType = ExpenseType::factory()->create(['code' => 'car_wash', 'calculation_method' => 'fixed']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('expense-types.update', $expenseType), $this->validPayload([
                'code' => 'car_wash',
                'calculation_method' => 'per_night',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('expense-types.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertSame('per_night', $expenseType->refresh()->calculation_method);
    }

    public function test_rejects_changing_the_code_to_another_expense_types_code(): void
    {
        ExpenseType::factory()->create(['code' => 'tolls']);
        $expenseType = ExpenseType::factory()->create(['code' => 'car_wash']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('expense-types.update', $expenseType), $this->validPayload(['code' => 'tolls']))
            ->assertSessionHasErrors(['code' => 'Ya existe un tipo de gasto con esa clave.']);

        $this->assertSame('car_wash', $expenseType->refresh()->code);
    }

    public function test_deletes_an_unused_expense_type(): void
    {
        $expenseType = ExpenseType::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('expense-types.destroy', $expenseType))
            ->assertRedirect(route('expense-types.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertModelMissing($expenseType);
    }

    public function test_does_not_delete_an_expense_type_with_rates(): void
    {
        $expenseType = ExpenseType::factory()->create();
        ExpenseRate::factory()->for($expenseType)->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('expense-types.destroy', $expenseType))
            ->assertRedirect(route('expense-types.index'))
            ->assertInertiaFlash('toast.message', 'No se puede eliminar: tiene tarifas registradas.');

        $this->assertModelExists($expenseType);
    }

    public function test_does_not_delete_an_expense_type_used_by_a_requisition(): void
    {
        $expenseType = ExpenseType::factory()->create();
        RequisitionItem::factory()->for($expenseType)->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('expense-types.destroy', $expenseType))
            ->assertRedirect(route('expense-types.index'))
            ->assertInertiaFlash('toast.message', 'No se puede eliminar: hay requisiciones o comprobaciones con este tipo de gasto.');

        $this->assertModelExists($expenseType);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'code' => 'car_wash',
            'name' => 'Lavado de auto',
            'calculation_method' => 'fixed',
            'default_unit' => 'TICKET',
            'is_requestable' => '1',
            'sort_order' => '14',
            ...$overrides,
        ];
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        return User::factory()->create()->assignRole($role);
    }
}
