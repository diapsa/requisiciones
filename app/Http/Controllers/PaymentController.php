<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Campos y validaciones del diccionario de docs/base-de-datos.md.
 */
class PaymentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Payment::query()->orderBy('id')->paginate());
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        return response()->json(Payment::query()->create($data), 201);
    }

    /** @return array<string, list<mixed>> */
    private function rules(?Payment $payment = null): array
    {
        return [
            'requisition_id' => ['required', 'integer', 'exists:requisitions,id'],
            'type' => ['required', 'in:advance,reimbursement,refund,payroll_deduction'],
            'payment_method' => ['required', 'in:card,transfer,cash'],
            'source_account' => ['nullable', 'string', 'max:100'],
            'paid_on' => ['required', 'date_format:Y-m-d'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'between:-9999999999.99,9999999999.99'],
            'reference' => ['nullable', 'string', 'max:100'],
            'proof_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
