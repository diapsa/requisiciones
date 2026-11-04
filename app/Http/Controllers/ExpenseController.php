<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class ExpenseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Expense::query()->orderBy('id')->paginate());
    }

    public function show(Expense $expense): JsonResponse
    {
        return response()->json($expense);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(Expense::query()->create($data), 201);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $attributes = $expense->getAttributes();
        foreach (['incurred_on'] as $date) {
            if ($expense->getRawOriginal($date) !== null) {
                $attributes[$date] = substr((string) $expense->getRawOriginal($date), 0, 10);
            }
        }
        $rules = $this->rules($expense);
        $data = Validator::make(array_replace($attributes, $request->only(array_keys($rules))), $rules)->validate();
        $expense->update($data);

        return response()->json($expense);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?Expense $expense = null): array
    {
        return [
            'expense_report_id' => ['required', 'integer', 'exists:expense_reports,id'],
            'expense_type_id' => ['required', 'integer', 'exists:expense_types,id'],
            'line_number' => ['required', 'integer', 'between:0,65535'],
            'receipt_type' => ['required', 'in:cfdi_invoice,non_fiscal_receipt,no_receipt'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'cfdi_uuid' => ['nullable', 'string', 'size:36', Rule::unique('expenses', 'cfdi_uuid')->ignore($expense)],
            'issuer_rfc' => ['nullable', 'string', 'max:13'],
            'vendor_name' => ['required', 'string', 'max:200'],
            'incurred_on' => ['required', 'date_format:Y-m-d'],
            'subtotal' => ['required', 'numeric', 'decimal:0,2', 'between:-9999999999.99,9999999999.99'],
            'tax_amount' => ['required', 'numeric', 'decimal:0,2', 'between:-9999999999.99,9999999999.99'],
            'total_amount' => ['required', 'numeric', 'decimal:0,2', 'between:-9999999999.99,9999999999.99'],
            'justification' => ['nullable', 'string'],
            'xml_path' => ['nullable', 'string', 'max:255'],
            'receipt_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
