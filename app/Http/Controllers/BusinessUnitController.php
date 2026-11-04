<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class BusinessUnitController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(BusinessUnit::query()->orderBy('id')->paginate());
    }

    public function show(BusinessUnit $businessUnit): JsonResponse
    {
        return response()->json($businessUnit);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(BusinessUnit::query()->create($data), 201);
    }

    public function update(Request $request, BusinessUnit $businessUnit): JsonResponse
    {
        $attributes = $businessUnit->getAttributes();
        $rules = $this->rules($businessUnit);
        $data = Validator::make(array_replace($attributes, $request->only(array_keys($rules))), $rules)->validate();
        $businessUnit->update($data);

        return response()->json($businessUnit);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?BusinessUnit $businessUnit = null): array
    {
        return [
            'code' => ['required', 'string', 'max:10', Rule::unique('business_units', 'code')->ignore($businessUnit)],
            'name' => ['required', 'string', 'max:100'],
        ];
    }
}
