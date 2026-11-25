<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SendUserInvitation
{
    /**
     * Email the user a link to set their own password.
     * Returns false when the mail could not be sent or one was sent less than a minute ago.
     */
    public function handle(User $user): bool
    {
        try {
            $status = Password::broker('invitations')->sendResetLink(
                ['email' => $user->email],
                function (User $user, string $token): void {
                    $user->notify(new UserInvitation($token));
                },
            );
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return false;
        }

        return $status === Password::RESET_LINK_SENT;
    }
}
