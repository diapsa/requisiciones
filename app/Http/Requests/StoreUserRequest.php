<?php

namespace App\Http\Requests;

use App\Concerns\ProfileValidationRules;
use App\Enums\Role;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * The coordinator select sends "none" because Radix does not allow an empty option value.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('coordinator_id') === 'none') {
            $this->merge(['coordinator_id' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request. There is no password: the user chooses it from the emailed invitation.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules(),
            'role' => ['required', Rule::enum(Role::class)],
            'coordinator_id' => ['nullable', 'integer', $this->mustBeAnotherCoordinator(...)],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'email.required' => 'Escribe el correo.',
            'email.email' => 'Escribe un correo válido.',
            'email.max' => 'El correo no puede tener más de 255 caracteres.',
            'email.unique' => 'Ya hay un usuario con ese correo.',
            'role.required' => 'Elige el rol.',
            'role.enum' => 'Elige un rol de la lista.',
            'coordinator_id.integer' => 'Elige un coordinador de la lista.',
        ];
    }

    /**
     * The user being edited, or null when creating one.
     */
    protected function userBeingUpdated(): ?User
    {
        return null;
    }

    /**
     * The coordinator has to be another user with the coordinator role.
     */
    private function mustBeAnotherCoordinator(string $attribute, mixed $value, Closure $fail): void
    {
        $isAnotherCoordinator = User::role(Role::Coordinator)
            ->whereKey($value)
            ->when($this->userBeingUpdated(), fn (Builder $query, User $user) => $query->whereKeyNot($user->id))
            ->exists();

        if (! $isAnotherCoordinator) {
            $fail('Elige un coordinador de la lista.');
        }
    }
}
