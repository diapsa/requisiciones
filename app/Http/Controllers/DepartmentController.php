<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    /**
     * Show the departments catalog.
     */
    public function index(): Response
    {
        return Inertia::render('departments/index', [
            'departments' => Department::query()
                ->select(['id', 'name'])
                ->withCount(['requisitions' => fn ($query) => $query->withTrashed()])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a new department.
     */
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        Department::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Departamento creado.']);

        return to_route('departments.index');
    }

    /**
     * Rename a department.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Departamento actualizado.']);

        return to_route('departments.index');
    }

    /**
     * Delete a department that no requisition uses, including soft-deleted ones.
     */
    public function destroy(Department $department): RedirectResponse
    {
        if ($department->requisitions()->withTrashed()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se puede eliminar: hay requisiciones con este departamento.']);

            return to_route('departments.index');
        }

        $department->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Departamento eliminado.']);

        return to_route('departments.index');
    }
}
