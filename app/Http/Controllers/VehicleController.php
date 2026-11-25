<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    /**
     * Show the vehicles catalog, active vehicles first, with how many requisitions use each one.
     */
    public function index(): Response
    {
        return Inertia::render('vehicles/index', [
            'vehicles' => Vehicle::query()
                ->select(['id', 'model', 'plate_number', 'tank_capacity_liters', 'km_per_liter', 'is_active'])
                ->withCount(['requisitionItems as requisitions_count' => fn (Builder $query) => $query->select(DB::raw('count(distinct requisition_id)'))])
                ->orderByDesc('is_active')
                ->orderBy('model')
                ->get(),
        ]);
    }

    /**
     * Store a new vehicle.
     */
    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        Vehicle::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Vehículo creado.']);

        return to_route('vehicles.index');
    }

    /**
     * Update a vehicle. Requisition items keep their own copy of the tank and efficiency figures.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Vehículo actualizado.']);

        return to_route('vehicles.index');
    }

    /**
     * Delete a vehicle that no requisition item uses; a used one can only be deactivated.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->requisitionItems()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se puede eliminar: hay requisiciones con este vehículo. Desactívalo en su lugar.']);

            return to_route('vehicles.index');
        }

        $vehicle->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Vehículo eliminado.']);

        return to_route('vehicles.index');
    }
}
