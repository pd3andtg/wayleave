<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StorePermitReceivedRequest;
use App\Http\Requests\Steps\UpdatePermitReceivedRequest;
use App\Models\PermitReceived;
use App\Models\Project;
use App\Services\ProjectService;

// Section 9: contractor/officer records permits received from PBT. Up to 3 per project.
class PermitReceivedController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(StorePermitReceivedRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        // Enforce max 3 permits received per project.
        if ($project->permitReceiveds()->count() >= 3) {
            return back()->with('error', 'Maximum of 3 permit received records allowed per project.');
        }

        $this->projectService->storePermitReceived($request->validated(), $project, auth()->user());

        return redirect(route('projects.show', $project) . '#section-9')->with('success', 'Permit received recorded.');
    }

    public function update(UpdatePermitReceivedRequest $request, Project $project, PermitReceived $permitReceived)
    {
        $this->projectService->updatePermitReceived($request->validated(), $permitReceived, $project, auth()->user());

        return redirect(route('projects.show', $project) . '#section-9')->with('success', 'Permit received updated.');
    }

    public function destroy(Project $project, PermitReceived $permitReceived)
    {
        $this->authorize('update', $project);

        $this->projectService->deletePermitReceived($permitReceived);

        return redirect(route('projects.show', $project) . '#section-9')->with('success', 'Permit received deleted.');
    }
}
