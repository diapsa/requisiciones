<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateBusinessUnitRequest extends StoreBusinessUnitRequest
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
            'code' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/', Rule::unique('business_units', 'code')->ignore($this->route('business_unit'))],
        ];
    }
}
