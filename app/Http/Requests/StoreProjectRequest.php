<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    /**
     * Normalize the code the way it appears in the requisition number: "San Marcos" becomes "SAN_MARCOS".
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => Str::of($this->input('code'))->trim()->upper()->replaceMatches('/\s+/', '_')->toString()]);
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
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/', Rule::unique('projects', 'code')],
            'name' => ['required', 'string', 'max:150'],
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
            'code.required' => 'Escribe la clave del proyecto.',
            'code.max' => 'La clave no puede tener más de 50 caracteres.',
            'code.regex' => 'La clave solo puede llevar letras sin acento, números y guion bajo.',
            'code.unique' => 'Ya existe un proyecto con esa clave.',
            'name.required' => 'Escribe el nombre del proyecto.',
            'name.max' => 'El nombre no puede tener más de 150 caracteres.',
        ];
    }
}
