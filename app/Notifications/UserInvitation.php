<?php

namespace App\Notifications;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitation extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $token) {}

    /**
     * Hours the invitation link stays valid (auth.passwords.invitations.expire is in minutes).
     */
    public static function expiresInHours(): int
    {
        return intdiv(config()->integer('auth.passwords.invitations.expire'), 60);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(User $notifiable): MailMessage
    {
        $role = Role::tryFrom((string) $notifiable->getRoleNames()->first());

        return (new MailMessage)
            ->subject('Activa tu cuenta de '.config('app.name'))
            ->markdown('mail.user-invitation', [
                'name' => $notifiable->name,
                'role' => $role?->label(),
                'url' => route('invitation.show', [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ]),
                'expiresInHours' => self::expiresInHours(),
            ]);
    }
}
