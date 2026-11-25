<?php

namespace App\Http\Controllers;

use App\Actions\Users\CreateUser;
use App\Actions\Users\SendUserInvitation;
use App\Actions\Users\UpdateUser;
use App\Enums\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Show the users with their role, coordinator and account status. Passwords and two-factor data never leave the server.
     */
    public function index(): Response
    {
        $invitationsSentAt = $this->invitationsSentAt();

        return Inertia::render('users/index', [
            'users' => User::query()
                ->with(['roles:id,name', 'coordinator:id,name'])
                ->withCount([
                    'requisitions' => fn ($query) => $query->withTrashed(),
                    'approvals',
                    'bankAccounts',
                    'coordinatedUsers',
                ])
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()->first(),
                    'coordinator' => $user->coordinator?->only(['id', 'name']),
                    'requisitions_count' => $user->requisitions_count,
                    'approvals_count' => $user->approvals_count,
                    'coordinated_users_count' => $user->coordinated_users_count,
                    'is_active' => $user->hasVerifiedEmail(),
                    'invitation' => $user->hasVerifiedEmail() ? null : $this->invitationStatus($invitationsSentAt->get($user->email)),
                    'has_history' => $user->requisitions_count + $user->approvals_count + $user->bank_accounts_count + $user->coordinated_users_count > 0,
                ]),
            'roles' => Role::options(),
            'coordinators' => User::role(Role::Coordinator)->select(['id', 'name'])->orderBy('name')->get(),
            'invitationExpiresInHours' => UserInvitation::expiresInHours(),
        ]);
    }

    /**
     * Register a new user and email them the invitation to choose their password.
     */
    public function store(StoreUserRequest $request, CreateUser $createUser, SendUserInvitation $sendUserInvitation): RedirectResponse
    {
        $user = $createUser->handle($request->userAttributes());

        Inertia::flash('toast', $sendUserInvitation->handle($user)
            ? ['type' => 'success', 'message' => "Usuario creado. Le enviamos la invitación a {$user->email}."]
            : ['type' => 'warning', 'message' => 'Usuario creado, pero no se pudo enviar la invitación. Reenvíala desde la lista.']);

        return to_route('users.index');
    }

    /**
     * Update a user's data, role and coordinator. A pending user whose email changed gets the invitation at the new address.
     */
    public function update(UpdateUserRequest $request, User $user, UpdateUser $updateUser, SendUserInvitation $sendUserInvitation): RedirectResponse
    {
        $updateUser->handle($user, $request->userAttributes());

        if (! $user->wasChanged('email') || $user->hasVerifiedEmail()) {
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuario actualizado.']);

            return to_route('users.index');
        }

        Inertia::flash('toast', $sendUserInvitation->handle($user)
            ? ['type' => 'success', 'message' => "Usuario actualizado. Le enviamos la invitación a {$user->email}."]
            : ['type' => 'warning', 'message' => 'Usuario actualizado, pero no se pudo enviar la invitación al nuevo correo. Reenvíala desde la lista.']);

        return to_route('users.index');
    }

    /**
     * Delete a user without history. Nobody can delete their own account from here.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No puedes eliminar tu propia cuenta.']);

            return to_route('users.index');
        }

        if ($user->hasHistory()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se puede eliminar: tiene requisiciones, autorizaciones, cuentas bancarias o personas a su cargo.']);

            return to_route('users.index');
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Usuario eliminado.']);

        return to_route('users.index');
    }

    /**
     * When the last invitation or password reset link was sent to each email. Both use the same token table.
     *
     * @return Collection<string, Carbon>
     */
    private function invitationsSentAt(): Collection
    {
        return DB::table(config()->string('auth.passwords.invitations.table'))
            ->pluck('created_at', 'email')
            ->filter()
            ->map(fn (string $createdAt): Carbon => Carbon::parse($createdAt));
    }

    /**
     * Status of the invitation of a user who has not activated their account.
     *
     * @return array{sent_at: string|null, is_expired: bool}
     */
    private function invitationStatus(?Carbon $sentAt): array
    {
        return [
            'sent_at' => $sentAt?->toIso8601String(),
            'is_expired' => $sentAt === null || $sentAt->copy()->addMinutes(config()->integer('auth.passwords.invitations.expire'))->isPast(),
        ];
    }
}
