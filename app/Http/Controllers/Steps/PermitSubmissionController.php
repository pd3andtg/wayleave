<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StorePermitSubmissionRequest;
use App\Models\Project;
use App\Services\ProjectService;

// Step 6: contractor submits the permit document to KUTT/PBT.
class PermitSubmissionController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(StorePermitSubmissionRequest $request, Project $project)
    {
        $this->projectService->storePermitSubmission($request->validated(), $project, auth()->user());

        return back()->with('success', 'Permit submission recorded.');
    }
}
