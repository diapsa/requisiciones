<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends StoreUserRequest
{
    /**
     * Same rules as creating, but the user may keep their own email.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'email' => $this->emailRules($this->userBeingUpdated()->id),
        ];
    }

    /**
     * Nobody changes their own role, so the administrator cannot lock themselves out.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $user = $this->userBeingUpdated();

                if ($user->is($this->user()) && ! $user->hasRole($this->string('role')->toString())) {
                    $validator->errors()->add('role', 'No puedes cambiar tu propio rol.');
                }
            },
        ];
    }

    /**
     * The user bound to the {user} route parameter.
     */
    protected function userBeingUpdated(): User
    {
        /** @var User */
        return $this->route('user');
    }
}
