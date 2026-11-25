<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\Role;
use App\Models\BankAccount;
use App\Models\Requisition;
use App\Models\User;
use App\Notifications\UserInvitation;
use Closure;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_forbids_users_without_the_manage_users_permission(): void
    {
        $manager = $this->userWithRole(Role::Manager);
        $pendingUser = $this->userWithRole(Role::Requester, ['email_verified_at' => null]);

        $this->actingAs($manager)->get(route('users.index'))->assertForbidden();
        $this->actingAs($manager)->post(route('users.store'), $this->validPayload())->assertForbidden();
        $this->actingAs($manager)->post(route('users.invitation.store', $pendingUser))->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'ana@diapsa.test']);
        Notification::assertNothingSent();
    }

    public function test_lists_users_with_their_role_coordinator_and_account_status_without_sensitive_data(): void
    {
        $coordinator = $this->userWithRole(Role::Coordinator, ['name' => 'Carla']);
        $this->userWithRole(Role::Requester, ['name' => 'Beto', 'coordinator_id' => $coordinator->id]);
        $pendingUser = $this->userWithRole(Role::Requester, ['name' => 'Diana', 'email_verified_at' => null]);
        Password::broker('invitations')->createToken($pendingUser);
        $admin = $this->userWithRole(Role::Admin, ['name' => 'Zoe']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/index')
                ->has('users', 4)
                ->where('users.0.name', 'Beto')
                ->where('users.0.role', 'requester')
                ->where('users.0.coordinator.name', 'Carla')
                ->where('users.0.is_active', true)
                ->where('users.0.invitation', null)
                ->where('users.0.has_history', false)
                ->missing('users.0.password')
                ->missing('users.0.two_factor_secret')
                ->where('users.1.name', 'Carla')
                ->where('users.1.coordinated_users_count', 1)
                ->where('users.1.has_history', true)
                ->where('users.2.name', 'Diana')
                ->where('users.2.is_active', false)
                ->whereType('users.2.invitation.sent_at', 'string')
                ->where('users.2.invitation.is_expired', false)
                ->has('roles', 4)
                ->where('roles.0.value', 'requester')
                ->where('roles.0.label', 'Solicitante')
                ->where('roles.1.value', 'coordinator')
                ->has('coordinators', 1)
                ->where('coordinators.0.name', 'Carla')
                ->where('invitationExpiresInHours', 24)
            );
    }

    public function test_reports_expired_and_unsent_invitations(): void
    {
        $expiredUser = $this->userWithRole(Role::Requester, ['name' => 'Ana', 'email_verified_at' => null]);
        Password::broker('invitations')->createToken($expiredUser);
        $this->userWithRole(Role::Requester, ['name' => 'Beto', 'email_verified_at' => null]);
        $admin = $this->userWithRole(Role::Admin, ['name' => 'Zoe']);

        $this->travel(25)->hours();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('users.0.name', 'Ana')
                ->whereType('users.0.invitation.sent_at', 'string')
                ->where('users.0.invitation.is_expired', true)
                ->where('users.1.name', 'Beto')
                ->where('users.1.invitation.sent_at', null)
                ->where('users.1.invitation.is_expired', true)
            );
    }

    public function test_creates_a_pending_user_and_emails_the_invitation(): void
    {
        $coordinator = $this->userWithRole(Role::Coordinator);

        $this->actingAs($this->userWithRole(Role::Admin))
            ->post(route('users.store'), $this->validPayload(['coordinator_id' => (string) $coordinator->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $user = User::query()->where('email', 'ana@diapsa.test')->sole();
        $this->assertSame('Ana López', $user->name);
        $this->assertTrue($user->hasRole(Role::Requester));
        $this->assertTrue($user->coordinator->is($coordinator));
        $this->assertFalse($user->hasVerifiedEmail());

        Notification::assertSentTo($user, UserInvitation::class, function (UserInvitation $notification) use ($user): bool {
            $mail = $notification->toMail($user);

            return $mail->viewData['url'] === route('invitation.show', ['token' => $notification->token, 'email' => $user->email])
                && str_contains((string) $mail->render(), 'Configurar mi contraseña')
                && Password::broker('invitations')->tokenExists($user, $notification->token);
        });
    }

    public function test_creates_a_user_without_coordinator(): void
    {
        $this->actingAs($this->userWithRole(Role::Admin))
            ->post(route('users.store'), $this->validPayload(['coordinator_id' => 'none']))
            ->assertSessionHasNoErrors();

        $this->assertNull(User::query()->where('email', 'ana@diapsa.test')->sole()->coordinator_id);
    }

    /**
     * @param  array<string, string>  $overrides
     */
    #[DataProvider('invalidUsers')]
    public function test_rejects_an_invalid_user(array $overrides, string $field, string $message): void
    {
        $this->actingAs($this->userWithRole(Role::Admin))
            ->post(route('users.store'), $this->validPayload($overrides))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseMissing('users', ['email' => 'ana@diapsa.test']);
        Notification::assertNothingSent();
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidUsers(): array
    {
        return [
            'missing name' => [['name' => ''], 'name', 'Escribe el nombre.'],
            'invalid email' => [['email' => 'ana-diapsa.test'], 'email', 'Escribe un correo válido.'],
            'missing role' => [['role' => ''], 'role', 'Elige el rol.'],
            'unknown role' => [['role' => 'supervisor'], 'role', 'Elige un rol de la lista.'],
        ];
    }

    public function test_rejects_an_email_another_user_already_has(): void
    {
        $this->userWithRole(Role::Requester, ['email' => 'ana@diapsa.test']);

        $this->actingAs($this->userWithRole(Role::Admin))
            ->post(route('users.store'), $this->validPayload())
            ->assertSessionHasErrors(['email' => 'Ya hay un usuario con ese correo.']);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_rejects_a_coordinator_without_the_coordinator_role(): void
    {
        $manager = $this->userWithRole(Role::Manager);

        $this->actingAs($this->userWithRole(Role::Admin))
            ->post(route('users.store'), $this->validPayload(['coordinator_id' => (string) $manager->id]))
            ->assertSessionHasErrors(['coordinator_id' => 'Elige un coordinador de la lista.']);

        $this->assertDatabaseMissing('users', ['email' => 'ana@diapsa.test']);
    }

    public function test_updates_a_user_and_their_role(): void
    {
        $user = $this->userWithRole(Role::Requester, ['email' => 'ana@diapsa.test']);

        $this->actingAs($this->userWithRole(Role::Admin))
            ->put(route('users.update', $user), $this->validPayload([
                'name' => 'Ana María López',
                'role' => 'coordinator',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $user->refresh();
        $this->assertSame('Ana María López', $user->name);
        $this->assertSame(['coordinator'], $user->getRoleNames()->all());
        Notification::assertNothingSent();
    }

    public function test_sends_the_invitation_to_the_new_email_of_a_pending_user(): void
    {
        $user = $this->userWithRole(Role::Requester, ['email' => 'ana@diapsa.test', 'email_verified_at' => null]);

        $this->actingAs($this->userWithRole(Role::Admin))
            ->put(route('users.update', $user), $this->validPayload(['email' => 'ana.lopez@diapsa.test']))
            ->assertSessionHasNoErrors()
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertSame('ana.lopez@diapsa.test', $user->refresh()->email);
        Notification::assertSentTo($user, UserInvitation::class);
    }

    public function test_rejects_making_a_coordinator_their_own_coordinator(): void
    {
        $coordinator = $this->userWithRole(Role::Coordinator, ['email' => 'ana@diapsa.test']);

        $this->actingAs($this->userWithRole(Role::Admin))
            ->put(route('users.update', $coordinator), $this->validPayload([
                'role' => 'coordinator',
                'coordinator_id' => (string) $coordinator->id,
            ]))
            ->assertSessionHasErrors(['coordinator_id' => 'Elige un coordinador de la lista.']);

        $this->assertNull($coordinator->refresh()->coordinator_id);
    }

    public function test_an_administrator_cannot_change_their_own_role(): void
    {
        $admin = $this->userWithRole(Role::Admin, ['email' => 'ana@diapsa.test']);

        $this->actingAs($admin)
            ->put(route('users.update', $admin), $this->validPayload(['role' => 'requester']))
            ->assertSessionHasErrors(['role' => 'No puedes cambiar tu propio rol.']);

        $this->assertTrue($admin->refresh()->hasRole(Role::Admin));
    }

    public function test_resends_the_invitation_to_a_pending_user(): void
    {
        $user = $this->userWithRole(Role::Requester, ['email_verified_at' => null]);

        $this->actingAs($this->userWithRole(Role::Admin))
            ->post(route('users.invitation.store', $user))
            ->assertRedirect(route('users.index'))
            ->assertInertiaFlash('toast.type', 'success');

        Notification::assertSentTo($user, UserInvitation::class);
    }

    public function test_does_not_resend_the_invitation_to_an_active_user(): void
    {
        $user = $this->userWithRole(Role::Requester);

        $this->actingAs($this->userWithRole(Role::Admin))
            ->post(route('users.invitation.store', $user))
            ->assertRedirect(route('users.index'))
            ->assertInertiaFlash('toast.message', 'Esta cuenta ya está activa.');

        Notification::assertNothingSent();
    }

    public function test_does_not_resend_the_invitation_twice_within_a_minute(): void
    {
        $user = $this->userWithRole(Role::Requester, ['email_verified_at' => null]);
        $admin = $this->userWithRole(Role::Admin);

        $this->actingAs($admin)->post(route('users.invitation.store', $user));

        $this->actingAs($admin)
            ->post(route('users.invitation.store', $user))
            ->assertInertiaFlash('toast.type', 'error');

        Notification::assertSentToTimes($user, UserInvitation::class, 1);
    }

    public function test_deletes_a_user_without_history(): void
    {
        $user = $this->userWithRole(Role::Requester);

        $this->actingAs($this->userWithRole(Role::Admin))
            ->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'))
            ->assertInertiaFlash('toast.type', 'success');

        $this->assertModelMissing($user);
    }

    public function test_an_administrator_cannot_delete_their_own_account(): void
    {
        $admin = $this->userWithRole(Role::Admin);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'))
            ->assertInertiaFlash('toast.message', 'No puedes eliminar tu propia cuenta.');

        $this->assertModelExists($admin);
    }

    /**
     * @return array<string, array{Closure(User): mixed}>
     */
    public static function historyRecords(): array
    {
        return [
            'soft-deleted requisition' => [fn (User $user) => Requisition::factory()->for($user, 'requester')->create()->delete()],
            'bank account' => [fn (User $user) => BankAccount::factory()->for($user)->create()],
            'user in their charge' => [fn (User $user) => User::factory()->create(['coordinator_id' => $user->id])],
        ];
    }

    /**
     * @param  Closure(User): mixed  $createHistory
     */
    #[DataProvider('historyRecords')]
    public function test_does_not_delete_a_user_with_history(Closure $createHistory): void
    {
        $user = $this->userWithRole(Role::Coordinator);
        $createHistory($user);

        $this->actingAs($this->userWithRole(Role::Admin))
            ->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'))
            ->assertInertiaFlash('toast.type', 'error');

        $this->assertModelExists($user);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'name' => 'Ana López',
            'email' => 'ana@diapsa.test',
            'role' => 'requester',
            'coordinator_id' => 'none',
            ...$overrides,
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function userWithRole(Role $role, array $attributes = []): User
    {
        $this->seed(RolePermissionSeeder::class);

        return User::factory()->create($attributes)->assignRole($role);
    }
}
