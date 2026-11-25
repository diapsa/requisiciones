<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseTypeRequest;
use App\Http\Requests\UpdateExpenseTypeRequest;
use App\Models\ExpenseType;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseTypeController extends Controller
{
    /**
     * Show the expense types catalog in on-screen order.
     */
    public function index(): Response
    {
        return Inertia::render('expense-types/index', [
            'expenseTypes' => ExpenseType::query()
                ->select(['id', 'code', 'name', 'calculation_method', 'default_unit', 'is_requestable', 'sort_order'])
                ->withCount(['expenseRates', 'requisitionItems', 'expenses'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a new expense type.
     */
    public function store(StoreExpenseTypeRequest $request): RedirectResponse
    {
        ExpenseType::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tipo de gasto creado.']);

        return to_route('expense-types.index');
    }

    /**
     * Update an expense type.
     */
    public function update(UpdateExpenseTypeRequest $request, ExpenseType $expenseType): RedirectResponse
    {
        $expenseType->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tipo de gasto actualizado.']);

        return to_route('expense-types.index');
    }

    /**
     * Delete an expense type that has no rates and that no requisition or expense report uses.
     */
    public function destroy(ExpenseType $expenseType): RedirectResponse
    {
        if ($expenseType->expenseRates()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se puede eliminar: tiene tarifas registradas.']);

            return to_route('expense-types.index');
        }

        if ($expenseType->requisitionItems()->exists() || $expenseType->expenses()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se puede eliminar: hay requisiciones o comprobaciones con este tipo de gasto.']);

            return to_route('expense-types.index');
        }

        $expenseType->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tipo de gasto eliminado.']);

        return to_route('expense-types.index');
    }
}
