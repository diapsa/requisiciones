<?php

namespace App\Http\Controllers;

use App\Actions\Users\SendUserInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class UserInvitationController extends Controller
{
    /**
     * Send the invitation again to a user who has not activated their account.
     */
    public function store(User $user, SendUserInvitation $sendUserInvitation): RedirectResponse
    {
        if ($user->hasVerifiedEmail()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Esta cuenta ya está activa.']);

            return to_route('users.index');
        }

        if (! $sendUserInvitation->handle($user)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se pudo enviar la invitación. Espera un minuto e inténtalo de nuevo.']);

            return to_route('users.index');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => "Le reenviamos la invitación a {$user->email}."]);

        return to_route('users.index');
    }
}
