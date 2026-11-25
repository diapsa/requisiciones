<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Show the projects catalog.
     */
    public function index(): Response
    {
        return Inertia::render('projects/index', [
            'projects' => Project::query()
                ->select(['id', 'code', 'name'])
                ->withCount(['requisitions' => fn ($query) => $query->withTrashed()])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Store a new project.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Project::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Proyecto creado.']);

        return to_route('projects.index');
    }

    /**
     * Update a project's code and name.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Proyecto actualizado.']);

        return to_route('projects.index');
    }

    /**
     * Delete a project that no requisition uses, including soft-deleted ones.
     */
    public function destroy(Project $project): RedirectResponse
    {
        if ($project->requisitions()->withTrashed()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No se puede eliminar: hay requisiciones con este proyecto.']);

            return to_route('projects.index');
        }

        $project->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Proyecto eliminado.']);

        return to_route('projects.index');
    }
}
