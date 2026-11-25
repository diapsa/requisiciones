<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    /**
     * Show the employees' bank accounts. Only the last digits of each account number leave the server.
     */
    public function index(): Response
    {
        return Inertia::render('bank-accounts/index', [
            'bankAccounts' => BankAccount::query()
                ->with('user:id,name')
                ->withCount(['requisitions' => fn ($query) => $query->withTrashed()])
                ->orderBy(User::query()->select('name')->whereColumn('users.id', 'bank_accounts.user_id'))
                ->orderByDesc('is_active')
                ->orderByDesc('id')
                ->get()
                ->map(fn (BankAccount $bankAccount): array => [
                    'id' => $bankAccount->id,
                    'user' => $bankAccount->user->only(['id', 'name']),
                    'bank_name' => $bankAccount->bank_name,
                    'account_holder' => $bankAccount->account_holder,
                    'account_type' => $bankAccount->account_type,
                    'account_number_last_digits' => Str::substr($bankAccount->account_number, -4),
                    'bank_city' => $bankAccount->bank_city,
                    'branch_number' => $bankAccount->branch_number,
                    'is_active' => $bankAccount->is_active,
                    'requisitions_count' => $bankAccount->requisitions_count,
                ]),
            'users' => User::query()->select(['id', 'name'])->orderBy('name')->get(),
        ]);
    }

    /**
     * Register a new, active bank account for an employee.
     */
    public function store(StoreBankAccountRequest $request): RedirectResponse
    {
        BankAccount::query()->create([...$request->validated(), 'is_active' => true]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cuenta bancaria registrada.']);

        return to_route('bank-accounts.index');
    }

    /**
     * Activate or deactivate a bank account. Its details never change.
     */
    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): RedirectResponse
    {
        $bankAccount->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => $bankAccount->is_active ? 'Cuenta activada.' : 'Cuenta desactivada.']);

        return to_route('bank-accounts.index');
    }

    /**
     * Delete a bank account that no requisition was paid to; a used one can only be deactivated.
     */
    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        if ($bankAccount->requisitions()->withTrashed()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se puede eliminar: hay requisiciones con esta cuenta. Desactívala en su lugar.']);

            return to_route('bank-accounts.index');
        }

        $bankAccount->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cuenta bancaria eliminada.']);

        return to_route('bank-accounts.index');
    }
}
