<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InvitationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_the_form_for_a_valid_invitation(): void
    {
        [$user, $token] = $this->invitedUser();

        $this->get(route('invitation.show', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/accept-invitation')
                ->where('isValid', true)
                ->where('name', 'Ana López')
                ->where('email', 'ana@diapsa.test')
                ->where('token', $token)
            );
    }

    public function test_explains_that_a_wrong_link_does_not_work(): void
    {
        [$user] = $this->invitedUser();

        $this->get(route('invitation.show', ['token' => 'otro-token', 'email' => $user->email]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('isValid', false)
                ->where('name', null)
            );
    }

    public function test_explains_that_an_expired_invitation_no_longer_works(): void
    {
        [$user, $token] = $this->invitedUser();

        $this->travel(25)->hours();

        $this->get(route('invitation.show', ['token' => $token, 'email' => $user->email]))
            ->assertInertia(fn (Assert $page) => $page->where('isValid', false));
    }

    public function test_activates_the_account_with_the_chosen_password(): void
    {
        [$user, $token] = $this->invitedUser();

        $this->post(route('invitation.store'), $this->payload($token))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Tu cuenta quedó activa. Inicia sesión con tu nueva contraseña.');

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue(Hash::check('contraseña-segura', $user->password));
        $this->assertFalse(Password::broker('invitations')->tokenExists($user, $token));
    }

    public function test_rejects_a_password_that_does_not_match_its_confirmation(): void
    {
        [$user, $token] = $this->invitedUser();

        $this->post(route('invitation.store'), $this->payload($token, ['password_confirmation' => 'otra-contraseña']))
            ->assertSessionHasErrors(['password' => 'Las contraseñas no coinciden.']);

        $this->assertFalse($user->refresh()->hasVerifiedEmail());
    }

    public function test_rejects_an_expired_invitation(): void
    {
        [$user, $token] = $this->invitedUser();

        $this->travel(25)->hours();

        $this->post(route('invitation.store'), $this->payload($token))
            ->assertSessionHasErrors(['email' => 'El enlace venció o ya se usó. Pide al administrador que te reenvíe la invitación.']);

        $this->assertFalse($user->refresh()->hasVerifiedEmail());
    }

    public function test_an_active_user_cannot_change_their_password_from_the_invitation_page(): void
    {
        $user = User::factory()->create(['email' => 'ana@diapsa.test', 'password' => 'contraseña-original']);
        $token = Password::broker('invitations')->createToken($user);

        $this->get(route('invitation.show', ['token' => $token, 'email' => $user->email]))
            ->assertInertia(fn (Assert $page) => $page->where('isValid', false));

        $this->post(route('invitation.store'), $this->payload($token))
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('contraseña-original', $user->refresh()->password));
    }

    /**
     * A user the administrator just registered, with their invitation token.
     *
     * @return array{User, string}
     */
    private function invitedUser(): array
    {
        $user = User::factory()->unverified()->create(['name' => 'Ana López', 'email' => 'ana@diapsa.test']);

        return [$user, Password::broker('invitations')->createToken($user)];
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function payload(string $token, array $overrides = []): array
    {
        return [
            'token' => $token,
            'email' => 'ana@diapsa.test',
            'password' => 'contraseña-segura',
            'password_confirmation' => 'contraseña-segura',
            ...$overrides,
        ];
    }
}
