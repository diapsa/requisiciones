<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\Requisition;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class BusinessUnitControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('business-units.index'))->assertRedirect(route('login'));
    }

    public function test_forbids_users_without_the_manage_catalogs_permission(): void
    {
        $user = $this->userWithRole('manager');

        $this->actingAs($user)->get(route('business-units.index'))->assertForbidden();
        $this->actingAs($user)->post(route('business-units.store'), ['code' => 'SALT', 'name' => 'Saltillo'])->assertForbidden();

        $this->assertDatabaseCount('business_units', 0);
    }

    public function test_lists_business_units_by_name_with_their_requisition_count(): void
    {
        $saltillo = BusinessUnit::factory()->create(['code' => 'SALT', 'name' => 'Saltillo']);
        BusinessUnit::factory()->create(['code' => 'MTY', 'name' => 'Monterrey']);
        Requisition::factory()->for($saltillo)->create();
        Requisition::factory()->for($saltillo)->create()->delete();

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('business-units.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('business-units/index')
                ->has('businessUnits', 2)
                ->where('businessUnits.0.code', 'MTY')
                ->where('businessUnits.0.requisitions_count', 0)
                ->where('businessUnits.1.code', 'SALT')
                ->where('businessUnits.1.requisitions_count', 2)
            );
    }

    public function test_creates_a_business_unit_with_an_uppercase_code(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('business-units.store'), ['code' => 'salt', 'name' => 'Saltillo'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('business-units.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertDatabaseHas('business_units', ['code' => 'SALT', 'name' => 'Saltillo']);
    }

    /**
     * @param  array<string, string>  $payload
     */
    #[DataProvider('invalidBusinessUnits')]
    public function test_rejects_an_invalid_business_unit(array $payload, string $field, string $message): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('business-units.store'), $payload)
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('business_units', 0);
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidBusinessUnits(): array
    {
        return [
            'missing code' => [['code' => '', 'name' => 'Saltillo'], 'code', 'Escribe la clave de la unidad de negocio.'],
            'code longer than 10 characters' => [['code' => 'SALTILLOMTY', 'name' => 'Saltillo'], 'code', 'La clave no puede tener más de 10 caracteres.'],
            'code with an underscore' => [['code' => 'SAL_T', 'name' => 'Saltillo'], 'code', 'La clave solo puede llevar letras sin acento y números, sin espacios.'],
            'missing name' => [['code' => 'SALT', 'name' => ''], 'name', 'Escribe el nombre de la unidad de negocio.'],
            'name longer than 100 characters' => [['code' => 'SALT', 'name' => str_repeat('a', 101)], 'name', 'El nombre no puede tener más de 100 caracteres.'],
        ];
    }

    public function test_rejects_a_duplicate_code(): void
    {
        BusinessUnit::factory()->create(['code' => 'SALT']);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('business-units.store'), ['code' => 'salt', 'name' => 'Saltillo'])
            ->assertSessionHasErrors(['code' => 'Ya existe una unidad de negocio con esa clave.']);

        $this->assertDatabaseCount('business_units', 1);
    }

    public function test_updates_a_business_unit_keeping_its_code(): void
    {
        $businessUnit = BusinessUnit::factory()->create(['code' => 'SALT', 'name' => 'Saltilo']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('business-units.update', $businessUnit), ['code' => 'SALT', 'name' => 'Saltillo'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('business-units.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertSame('Saltillo', $businessUnit->refresh()->name);
    }

    public function test_rejects_changing_the_code_to_another_business_units_code(): void
    {
        BusinessUnit::factory()->create(['code' => 'SALT']);
        $businessUnit = BusinessUnit::factory()->create(['code' => 'MTY']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('business-units.update', $businessUnit), ['code' => 'SALT', 'name' => 'Monterrey'])
            ->assertSessionHasErrors(['code' => 'Ya existe una unidad de negocio con esa clave.']);

        $this->assertSame('MTY', $businessUnit->refresh()->code);
    }

    public function test_deletes_a_business_unit_without_requisitions(): void
    {
        $businessUnit = BusinessUnit::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('business-units.destroy', $businessUnit))
            ->assertRedirect(route('business-units.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertModelMissing($businessUnit);
    }

    #[TestWith([false], 'active requisition')]
    #[TestWith([true], 'soft-deleted requisition')]
    public function test_does_not_delete_a_business_unit_used_by_a_requisition(bool $requisitionIsTrashed): void
    {
        $businessUnit = BusinessUnit::factory()->create();
        $requisition = Requisition::factory()->for($businessUnit)->create();

        if ($requisitionIsTrashed) {
            $requisition->delete();
        }

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('business-units.destroy', $businessUnit))
            ->assertRedirect(route('business-units.index'))
            ->assertInertiaFlash('toast.type', 'error');

        $this->assertModelExists($businessUnit);
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        return User::factory()->create()->assignRole($role);
    }
}
