<?php

namespace App\Http\Controllers;

use App\Actions\Users\AcceptInvitation;
use App\Http\Requests\AcceptInvitationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    /**
     * Show the form where an invited user chooses their password, or explain that the link no longer works.
     */
    public function show(Request $request, string $token, AcceptInvitation $acceptInvitation): Response
    {
        $email = $request->string('email')->toString();
        $invitedUser = $acceptInvitation->invitedUser($email, $token);

        return Inertia::render('auth/accept-invitation', [
            'token' => $token,
            'email' => $email,
            'name' => $invitedUser?->name,
            'isValid' => $invitedUser !== null,
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Save the chosen password, activate the account and send the user to the login page.
     */
    public function store(AcceptInvitationRequest $request, AcceptInvitation $acceptInvitation): RedirectResponse
    {
        if (! $acceptInvitation->handle($request->validated())) {
            throw ValidationException::withMessages([
                'email' => 'El enlace venció o ya se usó. Pide al administrador que te reenvíe la invitación.',
            ]);
        }

        return to_route('login')->with('status', 'Tu cuenta quedó activa. Inicia sesión con tu nueva contraseña.');
    }
}
