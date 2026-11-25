<?php

namespace App\Http\Requests;

use App\Models\ExpenseRate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreExpenseRateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'expense_type_id' => ['required', 'integer', Rule::exists('expense_types', 'id')],
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'valid_from' => ['required', 'date_format:Y-m-d'],
            'valid_until' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:valid_from'],
        ];
    }

    /**
     * Reject a rate whose validity overlaps another rate with the same name and expense type,
     * so there is only one amount for any given date.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['expense_type_id', 'name', 'valid_from', 'valid_until'])) {
                    return;
                }

                if ($this->overlapsAnotherRate()) {
                    $validator->errors()->add('valid_from', 'Ya hay una tarifa con ese nombre y tipo de gasto vigente en esas fechas.');
                }
            },
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
            'expense_type_id.required' => 'Elige el tipo de gasto.',
            'expense_type_id.integer' => 'Elige un tipo de gasto de la lista.',
            'expense_type_id.exists' => 'El tipo de gasto elegido no existe.',
            'name.required' => 'Escribe el nombre de la tarifa.',
            'name.max' => 'El nombre no puede tener más de 100 caracteres.',
            'amount.required' => 'Escribe el monto.',
            'amount.numeric' => 'El monto debe ser un número.',
            'amount.decimal' => 'El monto admite hasta 2 decimales.',
            'amount.min' => 'El monto no puede ser negativo.',
            'amount.max' => 'El monto es demasiado grande.',
            'valid_from.required' => 'Escribe desde cuándo aplica la tarifa.',
            'valid_from.date_format' => 'La fecha de inicio no es válida.',
            'valid_until.date_format' => 'La fecha de fin no es válida.',
            'valid_until.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ];
    }

    /**
     * The rate being edited, which the overlap check must skip.
     */
    protected function rateBeingUpdated(): ?ExpenseRate
    {
        return null;
    }

    private function overlapsAnotherRate(): bool
    {
        $validFrom = $this->string('valid_from')->toString();
        $validUntil = $this->input('valid_until');

        return ExpenseRate::query()
            ->where('expense_type_id', $this->integer('expense_type_id'))
            ->where('name', $this->string('name')->toString())
            ->when($this->rateBeingUpdated(), fn (Builder $query, ExpenseRate $rate) => $query->whereKeyNot($rate->getKey()))
            ->where(fn (Builder $query) => $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', $validFrom))
            ->when($validUntil, fn (Builder $query, string $until) => $query->whereDate('valid_from', '<=', $until))
            ->exists();
    }
}
