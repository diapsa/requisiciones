<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class ExpenseTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ExpenseType::query()->orderBy('id')->paginate());
    }

    public function show(ExpenseType $expenseType): JsonResponse
    {
        return response()->json($expenseType);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(ExpenseType::query()->create($data), 201);
    }

    public function update(Request $request, ExpenseType $expenseType): JsonResponse
    {
        $attributes = $expenseType->getAttributes();
        $rules = $this->rules($expenseType);
        $data = Validator::make(array_replace($attributes, $request->only(array_keys($rules))), $rules)->validate();
        $expenseType->update($data);

        return response()->json($expenseType);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?ExpenseType $expenseType = null): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('expense_types', 'code')->ignore($expenseType)],
            'name' => ['required', 'string', 'max:100'],
            'calculation_method' => ['required', 'string', 'in:fuel_by_distance,per_person_meals,breakfast_reference,per_night,fixed'],
            'default_unit' => ['required', 'string', 'max:20'],
            'is_requestable' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'between:0,65535'],
        ];
    }
}
