<?php

namespace App\Http\Requests;

use App\Models\ExpenseType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreExpenseTypeRequest extends FormRequest
{
    /**
     * Normalize the internal code to snake_case, uppercase the unit and read the unchecked checkbox as false.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_requestable' => $this->boolean('is_requestable')]);

        if (is_string($this->input('code'))) {
            $this->merge(['code' => Str::of($this->input('code'))->trim()->lower()->replaceMatches('/\s+/', '_')->toString()]);
        }

        if (is_string($this->input('default_unit'))) {
            $this->merge(['default_unit' => Str::upper($this->input('default_unit'))]);
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
            'code' => ['required', 'string', 'max:30', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('expense_types', 'code')],
            'name' => ['required', 'string', 'max:100'],
            'calculation_method' => ['required', Rule::in(ExpenseType::CALCULATION_METHODS)],
            'default_unit' => ['required', 'string', 'max:20'],
            'is_requestable' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
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
            'code.required' => 'Escribe la clave del tipo de gasto.',
            'code.max' => 'La clave no puede tener más de 30 caracteres.',
            'code.regex' => 'La clave debe empezar con una letra y solo llevar minúsculas sin acento, números y guion bajo.',
            'code.unique' => 'Ya existe un tipo de gasto con esa clave.',
            'name.required' => 'Escribe el nombre del tipo de gasto.',
            'name.max' => 'El nombre no puede tener más de 100 caracteres.',
            'calculation_method.required' => 'Elige cómo se calcula el importe.',
            'calculation_method.in' => 'Elige un método de cálculo de la lista.',
            'default_unit.required' => 'Escribe la unidad.',
            'default_unit.max' => 'La unidad no puede tener más de 20 caracteres.',
            'sort_order.required' => 'Escribe el orden.',
            'sort_order.integer' => 'El orden debe ser un número entero.',
            'sort_order.min' => 'El orden no puede ser negativo.',
            'sort_order.max' => 'El orden no puede pasar de 65,535.',
        ];
    }
}
