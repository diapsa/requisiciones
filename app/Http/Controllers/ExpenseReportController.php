<?php

namespace App\Http\Controllers;

use App\Models\ExpenseReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class ExpenseReportController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(ExpenseReport::query()->orderBy('id')->paginate());
    }

    public function show(ExpenseReport $expenseReport): JsonResponse
    {
        return response()->json($expenseReport);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(ExpenseReport::query()->create($data), 201);
    }

    public function update(Request $request, ExpenseReport $expenseReport): JsonResponse
    {
        $attributes = $expenseReport->getAttributes();
        $rules = $this->rules($expenseReport);
        $data = Validator::make(array_replace($attributes, $request->only(array_keys($rules))), $rules)->validate();
        $expenseReport->update($data);

        return response()->json($expenseReport);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?ExpenseReport $expenseReport = null): array
    {
        return [
            'requisition_id' => ['required', 'integer', 'exists:requisitions,id'],
            'sequence' => ['required', 'integer', 'between:0,65535'],
            'status' => ['required', 'in:draft,submitted,approved,rejected'],
            'notes' => ['nullable', 'string'],
            'submitted_at' => ['nullable', 'date'],
        ];
    }
}
