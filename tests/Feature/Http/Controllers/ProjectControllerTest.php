<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Project;
use App\Models\Requisition;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    public function test_forbids_users_without_the_manage_catalogs_permission(): void
    {
        $user = $this->userWithRole('coordinator');

        $this->actingAs($user)->get(route('projects.index'))->assertForbidden();
        $this->actingAs($user)->post(route('projects.store'), ['code' => 'WHIRLPOOL', 'name' => 'Whirlpool'])->assertForbidden();

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_lists_projects_by_name_with_their_requisition_count(): void
    {
        $whirlpool = Project::factory()->create(['code' => 'WHIRLPOOL', 'name' => 'Whirlpool']);
        Project::factory()->create(['code' => 'SAN_MARCOS', 'name' => 'San Marcos']);
        Requisition::factory()->for($whirlpool)->create();
        Requisition::factory()->for($whirlpool)->create()->delete();

        $this->actingAs($this->userWithRole('admin'))
            ->get(route('projects.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('projects/index')
                ->has('projects', 2)
                ->where('projects.0.code', 'SAN_MARCOS')
                ->where('projects.0.requisitions_count', 0)
                ->where('projects.1.code', 'WHIRLPOOL')
                ->where('projects.1.requisitions_count', 2)
            );
    }

    public function test_creates_a_project_with_the_code_as_it_goes_in_the_requisition_number(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('projects.store'), ['code' => 'San  Marcos', 'name' => 'San Marcos'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('projects.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertDatabaseHas('projects', ['code' => 'SAN_MARCOS', 'name' => 'San Marcos']);
    }

    /**
     * @param  array<string, string>  $payload
     */
    #[DataProvider('invalidProjects')]
    public function test_rejects_an_invalid_project(array $payload, string $field, string $message): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->post(route('projects.store'), $payload)
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('projects', 0);
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidProjects(): array
    {
        return [
            'missing code' => [['code' => '', 'name' => 'Whirlpool'], 'code', 'Escribe la clave del proyecto.'],
            'code longer than 50 characters' => [['code' => str_repeat('A', 51), 'name' => 'Whirlpool'], 'code', 'La clave no puede tener más de 50 caracteres.'],
            'code with an accent' => [['code' => 'MONCLOVA_ÑU', 'name' => 'Monclova'], 'code', 'La clave solo puede llevar letras sin acento, números y guion bajo.'],
            'missing name' => [['code' => 'WHIRLPOOL', 'name' => ''], 'name', 'Escribe el nombre del proyecto.'],
            'name longer than 150 characters' => [['code' => 'WHIRLPOOL', 'name' => str_repeat('a', 151)], 'name', 'El nombre no puede tener más de 150 caracteres.'],
        ];
    }

    public function test_rejects_a_duplicate_code(): void
    {
        Project::factory()->create(['code' => 'WHIRLPOOL']);

        $this->actingAs($this->userWithRole('admin'))
            ->post(route('projects.store'), ['code' => 'whirlpool', 'name' => 'Whirlpool'])
            ->assertSessionHasErrors(['code' => 'Ya existe un proyecto con esa clave.']);

        $this->assertDatabaseCount('projects', 1);
    }

    public function test_updates_a_project_keeping_its_code(): void
    {
        $project = Project::factory()->create(['code' => 'WHIRLPOOL', 'name' => 'Whirpool']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('projects.update', $project), ['code' => 'WHIRLPOOL', 'name' => 'Whirlpool'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('projects.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertSame('Whirlpool', $project->refresh()->name);
    }

    public function test_rejects_changing_the_code_to_another_projects_code(): void
    {
        Project::factory()->create(['code' => 'WHIRLPOOL']);
        $project = Project::factory()->create(['code' => 'SAN_MARCOS']);

        $this->actingAs($this->userWithRole('admin'))
            ->put(route('projects.update', $project), ['code' => 'WHIRLPOOL', 'name' => 'San Marcos'])
            ->assertSessionHasErrors(['code' => 'Ya existe un proyecto con esa clave.']);

        $this->assertSame('SAN_MARCOS', $project->refresh()->code);
    }

    public function test_deletes_a_project_without_requisitions(): void
    {
        $project = Project::factory()->create();

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertModelMissing($project);
    }

    #[TestWith([false], 'active requisition')]
    #[TestWith([true], 'soft-deleted requisition')]
    public function test_does_not_delete_a_project_used_by_a_requisition(bool $requisitionIsTrashed): void
    {
        $project = Project::factory()->create();
        $requisition = Requisition::factory()->for($project)->create();

        if ($requisitionIsTrashed) {
            $requisition->delete();
        }

        $this->actingAs($this->userWithRole('admin'))
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'))
            ->assertInertiaFlash('toast.type', 'error');

        $this->assertModelExists($project);
    }

    private function userWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        return User::factory()->create()->assignRole($role);
    }
}
