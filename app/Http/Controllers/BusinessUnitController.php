<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessUnitRequest;
use App\Http\Requests\UpdateBusinessUnitRequest;
use App\Models\BusinessUnit;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BusinessUnitController extends Controller
{
    /**
     * Show the business units catalog.
     */
    public function index(): Response
    {
        return Inertia::render('business-units/index', [
            'businessUnits' => BusinessUnit::query()
                ->select(['id', 'code', 'name'])
                ->withCount(['requisitions' => fn ($query) => $query->withTrashed()])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a new business unit.
     */
    public function store(StoreBusinessUnitRequest $request): RedirectResponse
    {
        BusinessUnit::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Unidad de negocio creada.']);

        return to_route('business-units.index');
    }

    /**
     * Update a business unit's code and name.
     */
    public function update(UpdateBusinessUnitRequest $request, BusinessUnit $businessUnit): RedirectResponse
    {
        $businessUnit->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Unidad de negocio actualizada.']);

        return to_route('business-units.index');
    }

    /**
     * Delete a business unit that no requisition uses, including soft-deleted ones.
     */
    public function destroy(BusinessUnit $businessUnit): RedirectResponse
    {
        if ($businessUnit->requisitions()->withTrashed()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se puede eliminar: hay requisiciones con esta unidad de negocio.']);

            return to_route('business-units.index');
        }

        $businessUnit->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Unidad de negocio eliminada.']);

        return to_route('business-units.index');
    }
}
