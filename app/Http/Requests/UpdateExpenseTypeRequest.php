<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateExpenseTypeRequest extends StoreExpenseTypeRequest
{
    /**
     * Same rules as creating, but the code may stay the same.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'code' => ['required', 'string', 'max:30', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('expense_types', 'code')->ignore($this->route('expense_type'))],
        ];
    }
}
