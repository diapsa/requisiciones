<?php

namespace App\Http\Controllers;

use App\Models\ExpenseRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class ExpenseRateController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ExpenseRate::query()->orderBy('id')->paginate());
    }

    public function show(ExpenseRate $expenseRate): JsonResponse
    {
        return response()->json($expenseRate);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(ExpenseRate::query()->create($data), 201);
    }

    public function update(Request $request, ExpenseRate $expenseRate): JsonResponse
    {
        $attributes = $expenseRate->getAttributes();
        $rules = $this->rules($expenseRate);
        $data = Validator::make(array_replace($attributes, $request->only(array_keys($rules))), $rules)->validate();
        $expenseRate->update($data);

        return response()->json($expenseRate);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?ExpenseRate $expenseRate = null): array
    {
        return [
            'expense_type_id' => ['required', 'integer', 'exists:expense_types,id'],
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'between:-9999999999.99,9999999999.99'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }
}
