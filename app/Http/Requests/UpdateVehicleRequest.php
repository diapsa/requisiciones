<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends StoreVehicleRequest
{
    /**
     * Same rules as creating, but the plate number may stay the same.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'plate_number' => ['nullable', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->ignore($this->route('vehicle'))],
        ];
    }
}
