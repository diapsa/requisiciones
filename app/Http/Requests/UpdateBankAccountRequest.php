<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBankAccountRequest extends FormRequest
{
    /**
     * Bank accounts are never edited: only activated or deactivated, so old requisitions keep the account they were paid to.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
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
            'is_active.required' => 'Indica si la cuenta queda activa.',
            'is_active.boolean' => 'Indica si la cuenta queda activa.',
        ];
    }
}
