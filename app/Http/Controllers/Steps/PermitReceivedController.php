<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StorePermitReceivedRequest;
use App\Models\Project;
use App\Services\ProjectService;

// Step 7: contractor records the permit received date and uploads the permit file.
class PermitReceivedController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(StorePermitReceivedRequest $request, Project $project)
    {
        $this->projectService->storePermitReceived($request->validated(), $project, auth()->user());

        return redirect(route('projects.show', $project) . '#section-9')->with('success', 'Permit received recorded.');
    }
}
