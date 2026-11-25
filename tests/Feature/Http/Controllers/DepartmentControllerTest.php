<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Department;
use App\Models\Requisition;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class DepartmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('departments.index'))->assertRedirect(route('login'));
    }

    #[TestWith(['requester'])]
    #[TestWith(['coordinator'])]
    #[TestWith(['manager'])]
    public function test_forbids_users_without_the_manage_catalogs_permission(string $role): void
    {
        $user = $this->userWithRole($role);

        $this->actingAs($user)->get(route('departments.index'))->assertForbidden();
        $this->actingAs($user)->post(route('departments.store'), ['name' => 'Técnico'])->assertForbidden();

        $this->assertDatabaseMissing('departments', ['name' => 'Técnico']);
    }

    public function test_lists_departments_by_name_with_their_requisition_count(): void
    {
        $technical = Department::factory()->create(['name' => 'Técnico']);
        Department::factory()->create(['name' => 'Administración']);
        Requisition::factory()->for($technical)->create();
        Requisition::factory()->for($technical)->create()->delete();

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('departments.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('departments/index')
                ->has('departments', 2)
                ->where('departments.0.name', 'Administración')
                ->where('departments.0.requisitions_count', 0)
                ->where('departments.1.name', 'Técnico')
                ->where('departments.1.requisitions_count', 2)
            );
    }

    public function test_creates_a_department(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('departments.store'), ['name' => 'Técnico'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('departments.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertDatabaseHas('departments', ['name' => 'Técnico']);
    }

    public function test_rejects_a_department_without_a_name(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('departments.store'), ['name' => ''])
            ->assertSessionHasErrors(['name' => 'Escribe el nombre del departamento.']);

        $this->assertDatabaseCount('departments', 0);
    }

    public function test_rejects_a_name_longer_than_100_characters(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('departments.store'), ['name' => str_repeat('a', 101)])
            ->assertSessionHasErrors(['name' => 'El nombre no puede tener más de 100 caracteres.']);

        $this->assertDatabaseCount('departments', 0);
    }

    public function test_rejects_a_duplicate_department_name(): void
    {
        Department::factory()->create(['name' => 'Técnico']);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('departments.store'), ['name' => 'Técnico'])
            ->assertSessionHasErrors(['name' => 'Ya existe un departamento con ese nombre.']);

        $this->assertDatabaseCount('departments', 1);
    }

    public function test_renames_a_department(): void
    {
        $department = Department::factory()->create(['name' => 'Tecnico']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('departments.update', $department), ['name' => 'Técnico'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('departments.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertSame('Técnico', $department->refresh()->name);
    }

    public function test_keeps_the_same_name_when_updating_a_department(): void
    {
        $department = Department::factory()->create(['name' => 'Técnico']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('departments.update', $department), ['name' => 'Técnico'])
            ->assertSessionHasNoErrors();
    }

    public function test_rejects_renaming_to_another_department_name(): void
    {
        Department::factory()->create(['name' => 'Técnico']);
        $department = Department::factory()->create(['name' => 'Ventas']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('departments.update', $department), ['name' => 'Técnico'])
            ->assertSessionHasErrors(['name' => 'Ya existe un departamento con ese nombre.']);

        $this->assertSame('Ventas', $department->refresh()->name);
    }

    public function test_deletes_a_department_without_requisitions(): void
    {
        $department = Department::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('departments.destroy', $department))
            ->assertRedirect(route('departments.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertModelMissing($department);
    }

    #[TestWith([false], 'active requisition')]
    #[TestWith([true], 'soft-deleted requisition')]
    public function test_does_not_delete_a_department_used_by_a_requisition(bool $requisitionIsTrashed): void
    {
        $department = Department::factory()->create();
        $requisition = Requisition::factory()->for($department)->create();

        if ($requisitionIsTrashed) {
            $requisition->delete();
        }

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('departments.destroy', $department))
            ->assertRedirect(route('departments.index'))
            ->assertInertiaFlash('toast.type', 'error');

        $this->assertModelExists($department);
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        return User::factory()->create()->assignRole($role);
    }
}
