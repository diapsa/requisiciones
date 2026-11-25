<?php

namespace App\Http\Requests;

use App\Models\BankAccount;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankAccountRequest extends FormRequest
{
    /**
     * Remove the spaces and dashes people type when copying an account number.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('account_number'))) {
            $this->merge(['account_number' => preg_replace('/[\s-]+/', '', $this->input('account_number'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_holder' => ['required', 'string', 'max:150'],
            'account_type' => ['required', Rule::in(BankAccount::ACCOUNT_TYPES)],
            'account_number' => ['bail', 'required', 'string', ...$this->accountNumberRules()],
            'bank_city' => ['nullable', 'string', 'max:100'],
            'branch_number' => ['nullable', 'string', 'max:20'],
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
            'user_id.required' => 'Elige al empleado dueño de la cuenta.',
            'user_id.integer' => 'Elige un empleado de la lista.',
            'user_id.exists' => 'El empleado elegido no existe.',
            'bank_name.required' => 'Escribe el banco.',
            'bank_name.max' => 'El banco no puede tener más de 100 caracteres.',
            'account_holder.required' => 'Escribe el titular de la cuenta.',
            'account_holder.max' => 'El titular no puede tener más de 150 caracteres.',
            'account_type.required' => 'Elige el tipo de cuenta.',
            'account_type.in' => 'Elige un tipo de cuenta de la lista.',
            'account_number.required' => 'Escribe el número de cuenta.',
            'account_number.digits' => $this->input('account_type') === 'clabe'
                ? 'La CLABE debe tener 18 dígitos.'
                : 'El número de tarjeta debe tener 16 dígitos.',
            'account_number.digits_between' => 'El número de cuenta debe tener entre 6 y 20 dígitos.',
            'bank_city.max' => 'La plaza no puede tener más de 100 caracteres.',
            'branch_number.max' => 'La sucursal no puede tener más de 20 caracteres.',
        ];
    }

    /**
     * Rules that depend on the account type: a CLABE also has to pass its check digit.
     *
     * @return list<string|Closure>
     */
    private function accountNumberRules(): array
    {
        return match ($this->input('account_type')) {
            'clabe' => ['digits:18', function (string $attribute, string $value, Closure $fail): void {
                if (! $this->hasValidClabeCheckDigit($value)) {
                    $fail('La CLABE no es válida; revisa que esté bien escrita.');
                }
            }],
            'debit_card' => ['digits:16'],
            'account' => ['digits_between:6,20'],
            default => [],
        };
    }

    /**
     * Validate the CLABE control digit: weights 3, 7, 1 over the first 17 digits.
     */
    private function hasValidClabeCheckDigit(string $clabe): bool
    {
        $weights = [3, 7, 1];
        $sum = 0;

        for ($position = 0; $position < 17; $position++) {
            $sum += ((int) $clabe[$position] * $weights[$position % 3]) % 10;
        }

        return (10 - $sum % 10) % 10 === (int) $clabe[17];
    }
}
