<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    /**
     * Uppercase the plate number and read the unchecked "active" checkbox as false.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);

        if (is_string($this->input('plate_number'))) {
            $this->merge(['plate_number' => Str::upper($this->input('plate_number'))]);
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
            'model' => ['required', 'string', 'max:100'],
            'plate_number' => ['nullable', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')],
            'tank_capacity_liters' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999.99'],
            'km_per_liter' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999.99'],
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
            'model.required' => 'Escribe el modelo del vehículo.',
            'model.max' => 'El modelo no puede tener más de 100 caracteres.',
            'plate_number.max' => 'Las placas no pueden tener más de 20 caracteres.',
            'plate_number.unique' => 'Ya hay un vehículo con esas placas.',
            'tank_capacity_liters.required' => 'Escribe la capacidad del tanque.',
            'tank_capacity_liters.numeric' => 'La capacidad del tanque debe ser un número.',
            'tank_capacity_liters.decimal' => 'La capacidad del tanque admite hasta 2 decimales.',
            'tank_capacity_liters.gt' => 'La capacidad del tanque debe ser mayor a 0.',
            'tank_capacity_liters.max' => 'La capacidad del tanque no puede pasar de 9,999.99 litros.',
            'km_per_liter.required' => 'Escribe el rendimiento.',
            'km_per_liter.numeric' => 'El rendimiento debe ser un número.',
            'km_per_liter.decimal' => 'El rendimiento admite hasta 2 decimales.',
            'km_per_liter.gt' => 'El rendimiento debe ser mayor a 0.',
            'km_per_liter.max' => 'El rendimiento no puede pasar de 9,999.99 km/L.',
        ];
    }
}
