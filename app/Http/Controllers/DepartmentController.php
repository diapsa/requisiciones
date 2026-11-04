<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Department::query()->orderBy('id')->paginate());
    }

    public function show(Department $department): JsonResponse
    {
        return response()->json($department);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(Department::query()->create($data), 201);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $attributes = $department->getAttributes();
        $rules = $this->rules($department);
        $data = Validator::make(array_replace($attributes, $request->only(array_keys($rules))), $rules)->validate();
        $department->update($data);

        return response()->json($department);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?Department $department = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
        ];
    }
}
