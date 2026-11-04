<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class RequisitionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Requisition::query()->orderBy('id')->paginate());
    }

    public function show(Requisition $requisition): JsonResponse
    {
        return response()->json($requisition);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(Requisition::query()->create($data), 201);
    }

    public function update(Request $request, Requisition $requisition): JsonResponse
    {
        $attributes = $requisition->getAttributes();
        foreach (['requested_on', 'starts_on', 'ends_on'] as $date) {
            if ($requisition->getRawOriginal($date) !== null) {
                $attributes[$date] = substr((string) $requisition->getRawOriginal($date), 0, 10);
            }
        }
        $rules = $this->rules($requisition);
        $data = Validator::make(array_replace($attributes, $request->only(array_keys($rules))), $rules)->validate();
        $requisition->update($data);

        return response()->json($requisition);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?Requisition $requisition = null): array
    {
        return [
            'number' => ['required', 'string', 'max:50', Rule::unique('requisitions', 'number')->ignore($requisition)],
            'sequence' => ['required', 'integer', 'between:0,4294967295'],
            'suffix' => ['nullable', 'string', 'max:5'],
            'parent_id' => ['nullable', 'integer', 'exists:requisitions,id'],
            'requester_id' => ['required', 'integer', 'exists:users,id'],
            'bank_account_id' => ['required', 'integer', 'exists:bank_accounts,id'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'business_unit_id' => ['required', 'integer', 'exists:business_units,id'],
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'service_description' => ['required', 'string'],
            'service_location' => ['nullable', 'string', 'max:100'],
            'requested_on' => ['required', 'date_format:Y-m-d'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'status' => ['required', 'in:draft,submitted,approved,rejected,advance_received,closed,cancelled'],
            'approved_amount' => ['nullable', 'numeric', 'decimal:0,2', 'between:-9999999999.99,9999999999.99'],
            'submitted_at' => ['nullable', 'date'],
            'terms_accepted_at' => ['nullable', 'date'],
        ];
    }
}
