<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class VehicleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Vehicle::query()->orderBy('id')->paginate());
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(Vehicle::query()->create($data), 201);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $attributes = $vehicle->getAttributes();
        $rules = $this->rules($vehicle);
        $data = Validator::make(array_replace($attributes, $request->only(array_keys($rules))), $rules)->validate();
        $vehicle->update($data);

        return response()->json($vehicle);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?Vehicle $vehicle = null): array
    {
        return [
            'model' => ['required', 'string', 'max:100'],
            'plate_number' => ['nullable', 'string', 'max:20'],
            'tank_capacity_liters' => ['required', 'numeric', 'between:0,9999.99'],
            'km_per_liter' => ['required', 'numeric', 'between:0,9999.99'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
