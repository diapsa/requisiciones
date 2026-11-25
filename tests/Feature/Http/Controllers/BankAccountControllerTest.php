<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Requisition;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class BankAccountControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A CLABE with a correct check digit.
     */
    private const VALID_CLABE = '002010077777777771';

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('bank-accounts.index'))->assertRedirect(route('login'));
    }

    public function test_forbids_users_without_the_manage_catalogs_permission(): void
    {
        $user = $this->userWithRole('requester');

        $this->actingAs($user)->get(route('bank-accounts.index'))->assertForbidden();
        $this->actingAs($user)->post(route('bank-accounts.store'), $this->validPayload($user))->assertForbidden();

        $this->assertDatabaseCount('bank_accounts', 0);
    }

    public function test_lists_accounts_by_employee_with_active_ones_first(): void
    {
        $ana = User::factory()->create(['name' => 'Ana']);
        $beatriz = User::factory()->create(['name' => 'Beatriz']);
        BankAccount::factory()->for($beatriz)->create();
        BankAccount::factory()->for($ana)->create(['bank_name' => 'Banorte', 'is_active' => false]);
        $activeAccount = BankAccount::factory()->for($ana)->create(['bank_name' => 'BBVA', 'account_number' => '123456789012345678']);
        Requisition::factory()->for($ana, 'requester')->for($activeAccount)->create();

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('bank-accounts.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('bank-accounts/index')
                ->has('bankAccounts', 3)
                ->where('bankAccounts.0.user.name', 'Ana')
                ->where('bankAccounts.0.bank_name', 'BBVA')
                ->where('bankAccounts.0.account_number_last_digits', '5678')
                ->where('bankAccounts.0.requisitions_count', 1)
                ->missing('bankAccounts.0.account_number')
                ->where('bankAccounts.1.bank_name', 'Banorte')
                ->where('bankAccounts.1.is_active', false)
                ->where('bankAccounts.2.user.name', 'Beatriz')
                ->has('users', 3)
            );
    }

    public function test_never_sends_the_full_account_number_to_the_browser(): void
    {
        BankAccount::factory()->create(['account_number' => self::VALID_CLABE]);

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('bank-accounts.index'))
            ->assertOk()
            ->assertDontSee(self::VALID_CLABE);
    }

    public function test_registers_an_active_account_without_the_spaces_typed_in_the_number(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('bank-accounts.store'), $this->validPayload($employee, ['account_number' => '0020 1007-7777 7777 71']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('bank-accounts.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $bankAccount = BankAccount::query()->sole();
        $this->assertTrue($bankAccount->user->is($employee));
        $this->assertSame(self::VALID_CLABE, $bankAccount->account_number);
        $this->assertSame('clabe', $bankAccount->account_type);
        $this->assertTrue($bankAccount->is_active);
    }

    public function test_registers_a_debit_card(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('bank-accounts.store'), $this->validPayload($employee, [
                'account_type' => 'debit_card',
                'account_number' => '4152313012345678',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('4152313012345678', BankAccount::query()->sole()->account_number);
    }

    /**
     * @param  array<string, string|int>  $overrides
     */
    #[DataProvider('invalidBankAccounts')]
    public function test_rejects_an_invalid_bank_account(array $overrides, string $field, string $message): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('bank-accounts.store'), $this->validPayload(User::factory()->create(), $overrides))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('bank_accounts', 0);
    }

    /**
     * @return array<string, array{array<string, string|int>, string, string}>
     */
    public static function invalidBankAccounts(): array
    {
        return [
            'missing employee' => [['user_id' => ''], 'user_id', 'Elige al empleado dueño de la cuenta.'],
            'unknown employee' => [['user_id' => 999], 'user_id', 'El empleado elegido no existe.'],
            'missing bank' => [['bank_name' => ''], 'bank_name', 'Escribe el banco.'],
            'missing holder' => [['account_holder' => ''], 'account_holder', 'Escribe el titular de la cuenta.'],
            'unknown account type' => [['account_type' => 'savings'], 'account_type', 'Elige un tipo de cuenta de la lista.'],
            'missing number' => [['account_number' => ''], 'account_number', 'Escribe el número de cuenta.'],
            'CLABE with 17 digits' => [['account_number' => '00201007777777777'], 'account_number', 'La CLABE debe tener 18 dígitos.'],
            'CLABE with a wrong check digit' => [['account_number' => '002010077777777772'], 'account_number', 'La CLABE no es válida; revisa que esté bien escrita.'],
            'card with 15 digits' => [['account_type' => 'debit_card', 'account_number' => '415231301234567'], 'account_number', 'El número de tarjeta debe tener 16 dígitos.'],
            'account number with letters' => [['account_type' => 'account', 'account_number' => '01234ABC'], 'account_number', 'El número de cuenta debe tener entre 6 y 20 dígitos.'],
        ];
    }

    public function test_deactivates_an_account_without_changing_its_details(): void
    {
        $bankAccount = BankAccount::factory()->create(['bank_name' => 'BBVA']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('bank-accounts.update', $bankAccount), ['is_active' => '0', 'bank_name' => 'Banorte'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('bank-accounts.index'))
            ->assertInertiaFlash('toast.message', 'Cuenta desactivada.');

        $bankAccount->refresh();
        $this->assertFalse($bankAccount->is_active);
        $this->assertSame('BBVA', $bankAccount->bank_name);
    }

    public function test_reactivates_an_account(): void
    {
        $bankAccount = BankAccount::factory()->create(['is_active' => false]);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('bank-accounts.update', $bankAccount), ['is_active' => '1'])
            ->assertInertiaFlash('toast.message', 'Cuenta activada.');

        $this->assertTrue($bankAccount->refresh()->is_active);
    }

    public function test_deletes_an_account_no_requisition_was_paid_to(): void
    {
        $bankAccount = BankAccount::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('bank-accounts.destroy', $bankAccount))
            ->assertRedirect(route('bank-accounts.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertModelMissing($bankAccount);
    }

    #[TestWith([false], 'active requisition')]
    #[TestWith([true], 'soft-deleted requisition')]
    public function test_does_not_delete_an_account_used_by_a_requisition(bool $requisitionIsTrashed): void
    {
        $bankAccount = BankAccount::factory()->create();
        $requisition = Requisition::factory()->for($bankAccount->user, 'requester')->for($bankAccount)->create();

        if ($requisitionIsTrashed) {
            $requisition->delete();
        }

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('bank-accounts.destroy', $bankAccount))
            ->assertRedirect(route('bank-accounts.index'))
            ->assertInertiaFlash('toast.type', 'error');

        $this->assertModelExists($bankAccount);
    }

    /**
     * @param  array<string, string|int>  $overrides
     * @return array<string, string|int>
     */
    private function validPayload(User $employee, array $overrides = []): array
    {
        return [
            'user_id' => $employee->id,
            'bank_name' => 'BBVA',
            'account_holder' => $employee->name,
            'account_type' => 'clabe',
            'account_number' => self::VALID_CLABE,
            'bank_city' => 'Saltillo',
            'branch_number' => '1234',
            ...$overrides,
        ];
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        return User::factory()->create()->assignRole($role);
    }
}
