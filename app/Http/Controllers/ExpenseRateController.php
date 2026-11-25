<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRateRequest;
use App\Http\Requests\UpdateExpenseRateRequest;
use App\Models\ExpenseRate;
use App\Models\ExpenseType;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseRateController extends Controller
{
    /**
     * Show the expense rates grouped by expense type, newest validity first.
     */
    public function index(): Response
    {
        return Inertia::render('expense-rates/index', [
            'expenseRates' => ExpenseRate::query()
                ->select(['id', 'expense_type_id', 'name', 'amount', 'valid_from', 'valid_until'])
                ->with('expenseType:id,name')
                ->orderBy(ExpenseType::query()->select('sort_order')->whereColumn('expense_types.id', 'expense_rates.expense_type_id'))
                ->orderBy('name')
                ->orderByDesc('valid_from')
                ->get(),
            'expenseTypes' => ExpenseType::query()
                ->select(['id', 'name'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a new expense rate.
     */
    public function store(StoreExpenseRateRequest $request): RedirectResponse
    {
        ExpenseRate::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tarifa creada.']);

        return to_route('expense-rates.index');
    }

    /**
     * Update an expense rate. Requisition items keep the amounts they were requested with.
     */
    public function update(UpdateExpenseRateRequest $request, ExpenseRate $expenseRate): RedirectResponse
    {
        $expenseRate->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tarifa actualizada.']);

        return to_route('expense-rates.index');
    }

    /**
     * Delete an expense rate. Nothing references a rate, so it can always be deleted.
     */
    public function destroy(ExpenseRate $expenseRate): RedirectResponse
    {
        $expenseRate->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tarifa eliminada.']);

        return to_route('expense-rates.index');
    }
}
