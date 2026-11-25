<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreBusinessUnitRequest extends FormRequest
{
    /**
     * Uppercase the code, since it goes into the requisition number (089_SALT_PROJECT).
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => Str::upper($this->input('code'))]);
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
            'code' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/', Rule::unique('business_units', 'code')],
            'name' => ['required', 'string', 'max:100'],
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
            'code.required' => 'Escribe la clave de la unidad de negocio.',
            'code.max' => 'La clave no puede tener más de 10 caracteres.',
            'code.regex' => 'La clave solo puede llevar letras sin acento y números, sin espacios.',
            'code.unique' => 'Ya existe una unidad de negocio con esa clave.',
            'name.required' => 'Escribe el nombre de la unidad de negocio.',
            'name.max' => 'El nombre no puede tener más de 100 caracteres.',
        ];
    }
}
