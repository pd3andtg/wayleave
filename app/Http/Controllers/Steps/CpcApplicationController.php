<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StoreCpcApplicationRequest;
use App\Models\Project;
use App\Services\ProjectService;

// Step 9: contractor uploads CPC application documents and submits to KUTT.
class CpcApplicationController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(StoreCpcApplicationRequest $request, Project $project)
    {
        $this->projectService->storeCpcApplication($request->validated(), $project, auth()->user());

        return redirect(route('projects.show', $project) . '#section-12')->with('success', 'CPC application submitted.');
    }
}
