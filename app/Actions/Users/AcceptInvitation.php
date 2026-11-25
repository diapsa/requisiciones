<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AcceptInvitation
{
    /**
     * The invited user when the link is still valid: the account is not active yet and the token has not expired or been used.
     */
    public function invitedUser(string $email, string $token): ?User
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || $user->hasVerifiedEmail()) {
            return null;
        }

        return Password::broker('invitations')->tokenExists($user, $token) ? $user : null;
    }

    /**
     * Save the password the invited user chose and activate the account.
     * Choosing it from the emailed link proves the email is theirs, so it is marked as verified.
     *
     * @param  array{email: string, token: string, password: string}  $input
     */
    public function handle(array $input): bool
    {
        if ($this->invitedUser($input['email'], $input['token']) === null) {
            return false;
        }

        $status = Password::broker('invitations')->reset($input, function (User $user, string $password): void {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            $user->markEmailAsVerified();

            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET;
    }
}
