<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class BankAccountController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(BankAccount::query()->orderBy('id')->paginate());
    }

    public function show(BankAccount $bankAccount): JsonResponse
    {
        return response()->json($bankAccount);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(BankAccount::query()->create($data), 201);
    }

    // Las cuentas anteriores se conservan: se desactivan en lugar de editarlas.
    public function deactivate(BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->update(['is_active' => false]);

        return response()->json($bankAccount);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?BankAccount $bankAccount = null): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_holder' => ['required', 'string', 'max:150'],
            'account_type' => ['required', 'in:debit_card,account,clabe'],
            'account_number' => ['required', 'string'],
            'bank_city' => ['nullable', 'string', 'max:100'],
            'branch_number' => ['nullable', 'string', 'max:20'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
