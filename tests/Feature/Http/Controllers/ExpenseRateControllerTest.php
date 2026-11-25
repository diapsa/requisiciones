<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ExpenseRate;
use App\Models\ExpenseType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExpenseRateControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('expense-rates.index'))->assertRedirect(route('login'));
    }

    public function test_forbids_users_without_the_manage_catalogs_permission(): void
    {
        $user = $this->userWithRole('manager');
        $expenseType = ExpenseType::factory()->create();

        $this->actingAs($user)->get(route('expense-rates.index'))->assertForbidden();
        $this->actingAs($user)->post(route('expense-rates.store'), $this->validPayload($expenseType))->assertForbidden();

        $this->assertDatabaseCount('expense_rates', 0);
    }

    public function test_lists_rates_by_expense_type_and_name_with_the_newest_validity_first(): void
    {
        $fuel = ExpenseType::factory()->create(['name' => 'Combustibles', 'sort_order' => 1]);
        $meals = ExpenseType::factory()->create(['name' => 'Comida', 'sort_order' => 3]);
        ExpenseRate::factory()->for($meals)->create(['name' => 'Comida con factura', 'amount' => 150, 'valid_from' => '2025-01-01', 'valid_until' => '2025-12-31']);
        ExpenseRate::factory()->for($meals)->create(['name' => 'Comida con factura', 'amount' => 170, 'valid_from' => '2026-01-01']);
        ExpenseRate::factory()->for($fuel)->create(['name' => 'Precio del litro', 'valid_from' => '2026-01-01']);

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('expense-rates.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('expense-rates/index')
                ->has('expenseRates', 3)
                ->where('expenseRates.0.name', 'Precio del litro')
                ->where('expenseRates.0.expense_type.name', 'Combustibles')
                ->where('expenseRates.1.amount', '170.00')
                ->where('expenseRates.1.valid_from', '2026-01-01')
                ->where('expenseRates.1.valid_until', null)
                ->where('expenseRates.2.amount', '150.00')
                ->where('expenseRates.2.valid_until', '2025-12-31')
                ->has('expenseTypes', 2)
                ->where('expenseTypes.0.name', 'Combustibles')
            );
    }

    public function test_creates_an_expense_rate(): void
    {
        $expenseType = ExpenseType::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('expense-rates.store'), $this->validPayload($expenseType))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('expense-rates.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $rate = ExpenseRate::query()->sole();
        $this->assertTrue($rate->expenseType->is($expenseType));
        $this->assertSame('Comida con factura', $rate->name);
        $this->assertSame('150.50', $rate->amount);
        $this->assertSame('2026-01-01', $rate->valid_from->toDateString());
        $this->assertNull($rate->valid_until);
    }

    /**
     * @param  array<string, string|int|null>  $overrides
     */
    #[DataProvider('invalidExpenseRates')]
    public function test_rejects_an_invalid_expense_rate(array $overrides, string $field, string $message): void
    {
        $expenseType = ExpenseType::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('expense-rates.store'), $this->validPayload($expenseType, $overrides))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('expense_rates', 0);
    }

    /**
     * @return array<string, array{array<string, string|int|null>, string, string}>
     */
    public static function invalidExpenseRates(): array
    {
        return [
            'missing expense type' => [['expense_type_id' => ''], 'expense_type_id', 'Elige el tipo de gasto.'],
            'unknown expense type' => [['expense_type_id' => 999], 'expense_type_id', 'El tipo de gasto elegido no existe.'],
            'missing name' => [['name' => ''], 'name', 'Escribe el nombre de la tarifa.'],
            'missing amount' => [['amount' => ''], 'amount', 'Escribe el monto.'],
            'negative amount' => [['amount' => '-1'], 'amount', 'El monto no puede ser negativo.'],
            'amount with 3 decimals' => [['amount' => '150.125'], 'amount', 'El monto admite hasta 2 decimales.'],
            'missing start date' => [['valid_from' => ''], 'valid_from', 'Escribe desde cuándo aplica la tarifa.'],
            'start date in another format' => [['valid_from' => '01/01/2026'], 'valid_from', 'La fecha de inicio no es válida.'],
            'end date before the start date' => [['valid_until' => '2025-12-31'], 'valid_until', 'La fecha de fin no puede ser anterior a la de inicio.'],
        ];
    }

    /**
     * @return array<string, array{string, string|null, string, string|null}>
     */
    public static function overlappingPeriods(): array
    {
        return [
            'new rate starts while an open-ended one applies' => ['2026-01-01', null, '2026-06-01', null],
            'new rate ends the day an existing one starts' => ['2026-06-01', null, '2026-01-01', '2026-06-01'],
            'new rate starts the day an existing one ends' => ['2026-01-01', '2026-05-31', '2026-05-31', null],
        ];
    }

    #[DataProvider('overlappingPeriods')]
    public function test_rejects_a_rate_that_overlaps_another_with_the_same_name_and_type(string $existingFrom, ?string $existingUntil, string $newFrom, ?string $newUntil): void
    {
        $expenseType = ExpenseType::factory()->create();
        ExpenseRate::factory()->for($expenseType)->create(['name' => 'Comida con factura', 'valid_from' => $existingFrom, 'valid_until' => $existingUntil]);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('expense-rates.store'), $this->validPayload($expenseType, ['valid_from' => $newFrom, 'valid_until' => $newUntil]))
            ->assertSessionHasErrors(['valid_from' => 'Ya hay una tarifa con ese nombre y tipo de gasto vigente en esas fechas.']);

        $this->assertDatabaseCount('expense_rates', 1);
    }

    /**
     * @return array<string, array{string, string|null, string, string|null}>
     */
    public static function consecutivePeriods(): array
    {
        return [
            'new rate starts the day after the existing one ends' => ['2026-01-01', '2026-05-31', '2026-06-01', null],
            'new rate ends the day before the existing one starts' => ['2026-06-01', null, '2026-01-01', '2026-05-31'],
        ];
    }

    #[DataProvider('consecutivePeriods')]
    public function test_accepts_a_rate_that_follows_another_with_the_same_name_and_type(string $existingFrom, ?string $existingUntil, string $newFrom, ?string $newUntil): void
    {
        $expenseType = ExpenseType::factory()->create();
        ExpenseRate::factory()->for($expenseType)->create(['name' => 'Comida con factura', 'valid_from' => $existingFrom, 'valid_until' => $existingUntil]);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('expense-rates.store'), $this->validPayload($expenseType, ['valid_from' => $newFrom, 'valid_until' => $newUntil]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('expense_rates', 2);
    }

    public function test_accepts_rates_with_the_same_period_but_another_name(): void
    {
        $expenseType = ExpenseType::factory()->create();
        ExpenseRate::factory()->for($expenseType)->create(['name' => 'Comida sin factura', 'valid_from' => '2026-01-01']);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('expense-rates.store'), $this->validPayload($expenseType))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('expense_rates', 2);
    }

    public function test_closes_a_rate_without_conflicting_with_itself(): void
    {
        $expenseType = ExpenseType::factory()->create();
        $rate = ExpenseRate::factory()->for($expenseType)->create(['name' => 'Comida con factura', 'valid_from' => '2026-01-01']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('expense-rates.update', $rate), $this->validPayload($expenseType, ['valid_until' => '2026-06-30']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('expense-rates.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertSame('2026-06-30', $rate->refresh()->valid_until?->toDateString());
    }

    public function test_rejects_moving_a_rate_onto_another_rates_period(): void
    {
        $expenseType = ExpenseType::factory()->create();
        ExpenseRate::factory()->for($expenseType)->create(['name' => 'Comida con factura', 'valid_from' => '2026-01-01', 'valid_until' => '2026-06-30']);
        $rate = ExpenseRate::factory()->for($expenseType)->create(['name' => 'Comida con factura', 'valid_from' => '2026-07-01']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('expense-rates.update', $rate), $this->validPayload($expenseType, ['valid_from' => '2026-06-01']))
            ->assertSessionHasErrors(['valid_from' => 'Ya hay una tarifa con ese nombre y tipo de gasto vigente en esas fechas.']);

        $this->assertSame('2026-07-01', $rate->refresh()->valid_from->toDateString());
    }

    public function test_deletes_an_expense_rate(): void
    {
        $rate = ExpenseRate::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('expense-rates.destroy', $rate))
            ->assertRedirect(route('expense-rates.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertModelMissing($rate);
    }

    /**
     * @param  array<string, string|int|null>  $overrides
     * @return array<string, string|int|null>
     */
    private function validPayload(ExpenseType $expenseType, array $overrides = []): array
    {
        return [
            'expense_type_id' => $expenseType->id,
            'name' => 'Comida con factura',
            'amount' => '150.50',
            'valid_from' => '2026-01-01',
            'valid_until' => null,
            ...$overrides,
        ];
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        return User::factory()->create()->assignRole($role);
    }
}
