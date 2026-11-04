<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class ApprovalController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Approval::query()->orderBy('id')->paginate());
    }

    public function show(Approval $approval): JsonResponse
    {
        return response()->json($approval);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(Approval::query()->create($data), 201);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?Approval $approval = null): array
    {
        return [
            'approvable_type' => ['required', 'string', 'max:50'],
            'approvable_id' => ['required', 'integer', 'min:0'],
            'approver_id' => ['required', 'integer', 'exists:users,id'],
            'stage' => ['required', 'in:coordinator,administration,deposit_confirmation'],
            'decision' => ['required', 'in:approved,rejected,returned,confirmed,not_received'],
            'comments' => ['nullable', 'string'],
            'decided_at' => ['required', 'date'],
        ];
    }
}
