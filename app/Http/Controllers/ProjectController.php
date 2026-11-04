<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Project::query()->orderBy('id')->paginate());
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(Project::query()->create($data), 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $attributes = $project->getAttributes();
        $rules = $this->rules($project);
        $data = Validator::make(array_replace($attributes, $request->only(array_keys($rules))), $rules)->validate();
        $project->update($data);

        return response()->json($project);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?Project $project = null): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('projects', 'code')->ignore($project)],
            'name' => ['required', 'string', 'max:150'],
        ];
    }
}
