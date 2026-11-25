<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class VehicleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('vehicles.index'))->assertRedirect(route('login'));
    }

    public function test_forbids_users_without_the_manage_catalogs_permission(): void
    {
        $user = $this->userWithRole('requester');

        $this->actingAs($user)->get(route('vehicles.index'))->assertForbidden();
        $this->actingAs($user)->post(route('vehicles.store'), $this->validPayload())->assertForbidden();

        $this->assertDatabaseCount('vehicles', 0);
    }

    public function test_lists_active_vehicles_first_with_how_many_requisitions_use_each(): void
    {
        Vehicle::factory()->inactive()->create(['model' => 'Chevrolet Aveo']);
        $hilux = Vehicle::factory()->create(['model' => 'Toyota Hilux']);
        Vehicle::factory()->create(['model' => 'Nissan NP300', 'tank_capacity_liters' => 80, 'km_per_liter' => 12.5]);

        $requisition = Requisition::factory()->create();
        RequisitionItem::factory()->for($requisition)->for($hilux)->count(2)->create();
        RequisitionItem::factory()->for($hilux)->create();

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('vehicles.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('vehicles/index')
                ->has('vehicles', 3)
                ->where('vehicles.0.model', 'Nissan NP300')
                ->where('vehicles.0.tank_capacity_liters', '80.00')
                ->where('vehicles.0.km_per_liter', '12.50')
                ->where('vehicles.0.requisitions_count', 0)
                ->where('vehicles.1.model', 'Toyota Hilux')
                ->where('vehicles.1.requisitions_count', 2)
                ->where('vehicles.2.model', 'Chevrolet Aveo')
                ->where('vehicles.2.is_active', false)
            );
    }

    public function test_creates_a_vehicle_with_uppercase_plates(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('vehicles.store'), $this->validPayload(['plate_number' => 'abc-123-d']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertDatabaseHas('vehicles', [
            'model' => 'Nissan NP300',
            'plate_number' => 'ABC-123-D',
            'tank_capacity_liters' => 80,
            'km_per_liter' => 12.5,
            'is_active' => true,
        ]);
    }

    public function test_creates_an_inactive_vehicle_when_the_active_checkbox_is_unchecked(): void
    {
        $payload = $this->validPayload();
        unset($payload['is_active']);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('vehicles.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->assertFalse(Vehicle::query()->sole()->is_active);
    }

    /**
     * @param  array<string, string>  $overrides
     */
    #[DataProvider('invalidVehicles')]
    public function test_rejects_an_invalid_vehicle(array $overrides, string $field, string $message): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('vehicles.store'), $this->validPayload($overrides))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('vehicles', 0);
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidVehicles(): array
    {
        return [
            'missing model' => [['model' => ''], 'model', 'Escribe el modelo del vehículo.'],
            'model longer than 100 characters' => [['model' => str_repeat('a', 101)], 'model', 'El modelo no puede tener más de 100 caracteres.'],
            'plates longer than 20 characters' => [['plate_number' => str_repeat('A', 21)], 'plate_number', 'Las placas no pueden tener más de 20 caracteres.'],
            'missing tank capacity' => [['tank_capacity_liters' => ''], 'tank_capacity_liters', 'Escribe la capacidad del tanque.'],
            'tank capacity of zero' => [['tank_capacity_liters' => '0'], 'tank_capacity_liters', 'La capacidad del tanque debe ser mayor a 0.'],
            'tank capacity with 3 decimals' => [['tank_capacity_liters' => '45.125'], 'tank_capacity_liters', 'La capacidad del tanque admite hasta 2 decimales.'],
            'tank capacity over the column limit' => [['tank_capacity_liters' => '10000'], 'tank_capacity_liters', 'La capacidad del tanque no puede pasar de 9,999.99 litros.'],
            'missing efficiency' => [['km_per_liter' => ''], 'km_per_liter', 'Escribe el rendimiento.'],
            'efficiency that is not a number' => [['km_per_liter' => 'doce'], 'km_per_liter', 'El rendimiento debe ser un número.'],
            'efficiency of zero' => [['km_per_liter' => '0'], 'km_per_liter', 'El rendimiento debe ser mayor a 0.'],
        ];
    }

    public function test_rejects_plates_another_vehicle_already_has(): void
    {
        Vehicle::factory()->create(['plate_number' => 'ABC-123-D']);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('vehicles.store'), $this->validPayload(['plate_number' => 'abc-123-d']))
            ->assertSessionHasErrors(['plate_number' => 'Ya hay un vehículo con esas placas.']);

        $this->assertDatabaseCount('vehicles', 1);
    }

    public function test_allows_several_vehicles_without_plates(): void
    {
        Vehicle::factory()->create(['plate_number' => null]);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('vehicles.store'), $this->validPayload(['plate_number' => '']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('vehicles', 2);
    }

    public function test_updates_and_deactivates_a_vehicle_keeping_its_plates(): void
    {
        $vehicle = Vehicle::factory()->create(['plate_number' => 'ABC-123-D', 'km_per_liter' => 12]);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('vehicles.update', $vehicle), $this->validPayload([
                'plate_number' => 'ABC-123-D',
                'km_per_liter' => '10.5',
                'is_active' => '0',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('vehicles.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $vehicle->refresh();
        $this->assertSame('10.50', $vehicle->km_per_liter);
        $this->assertFalse($vehicle->is_active);
    }

    public function test_deletes_a_vehicle_no_requisition_uses(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('vehicles.destroy', $vehicle))
            ->assertRedirect(route('vehicles.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertModelMissing($vehicle);
    }

    public function test_does_not_delete_a_vehicle_used_by_a_requisition(): void
    {
        $vehicle = Vehicle::factory()->create();
        RequisitionItem::factory()->for($vehicle)->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('vehicles.destroy', $vehicle))
            ->assertRedirect(route('vehicles.index'))
            ->assertInertiaFlash('toast.type', 'error');

        $this->assertModelExists($vehicle);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'model' => 'Nissan NP300',
            'plate_number' => '',
            'tank_capacity_liters' => '80',
            'km_per_liter' => '12.5',
            'is_active' => '1',
            ...$overrides,
        ];
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        return User::factory()->create()->assignRole($role);
    }
}
