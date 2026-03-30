<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StorePermitSubmissionRequest;
use App\Http\Requests\Steps\UpdatePermitSubmissionRequest;
use App\Models\PermitSubmission;
use App\Models\Project;
use App\Services\ProjectService;

// Section 8: contractor submits permit documents to PBT. Up to 3 per project.
class PermitSubmissionController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(StorePermitSubmissionRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        // Enforce max 3 submissions per project.
        if ($project->permitSubmissions()->count() >= 3) {
            return back()->with('error', 'Maximum of 3 permit submissions allowed per project.');
        }

        $this->projectService->storePermitSubmission($request->validated(), $project, auth()->user());

        return redirect(route('projects.show', $project) . '#section-8')->with('success', 'Permit submission recorded.');
    }

    public function update(UpdatePermitSubmissionRequest $request, Project $project, PermitSubmission $permitSubmission)
    {
        $this->projectService->updatePermitSubmission($request->validated(), $permitSubmission, $project, auth()->user());

        return redirect(route('projects.show', $project) . '#section-8')->with('success', 'Permit submission updated.');
    }

    public function destroy(Project $project, PermitSubmission $permitSubmission)
    {
        $this->authorize('update', $project);

        $this->projectService->deletePermitSubmission($permitSubmission);

        return redirect(route('projects.show', $project) . '#section-8')->with('success', 'Permit submission deleted.');
    }
}
