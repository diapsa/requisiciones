<?php

namespace App\Http\Controllers;

use App\Models\RequisitionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class RequisitionItemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(RequisitionItem::query()->orderBy('id')->paginate());
    }

    public function show(RequisitionItem $requisitionItem): JsonResponse
    {
        return response()->json($requisitionItem);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(RequisitionItem::query()->create($data), 201);
    }

    public function update(Request $request, RequisitionItem $requisitionItem): JsonResponse
    {
        $attributes = $requisitionItem->getAttributes();
        $rules = $this->rules($requisitionItem);
        $data = Validator::make(array_replace($attributes, $request->only(array_keys($rules))), $rules)->validate();
        $requisitionItem->update($data);

        return response()->json($requisitionItem);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?RequisitionItem $requisitionItem = null): array
    {
        return [
            'requisition_id' => ['required', 'integer', 'exists:requisitions,id'],
            'expense_type_id' => ['required', 'integer', 'exists:expense_types,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'people_count' => ['nullable', 'integer', 'between:0,255'],
            'origin' => ['nullable', 'string', 'max:150'],
            'destination' => ['nullable', 'string', 'max:150'],
            'distance_km' => ['nullable', 'numeric', 'decimal:0,2', 'between:-999999.99,999999.99'],
            'tank_capacity_liters' => ['nullable', 'numeric', 'decimal:0,2', 'between:-9999.99,9999.99'],
            'km_per_liter' => ['nullable', 'numeric', 'decimal:0,2', 'between:-9999.99,9999.99'],
            'fuel_price_per_liter' => ['nullable', 'numeric', 'decimal:0,2', 'between:-999999.99,999999.99'],
            'days' => ['nullable', 'integer', 'between:0,65535'],
            'meals_per_day' => ['nullable', 'integer', 'between:0,255'],
            'unit_price' => ['required', 'numeric', 'decimal:0,2', 'between:-9999999999.99,9999999999.99'],
            'quantity' => ['required', 'numeric', 'decimal:0,2', 'between:-999999.99,999999.99'],
            'unit' => ['required', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'between:-9999999999.99,9999999999.99'],
        ];
    }
}
