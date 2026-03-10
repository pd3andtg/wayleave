<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StoreCpcReceivedRequest;
use App\Models\Project;
use App\Services\ProjectService;

// Step 10: contractor uploads the received CPC.
// Creating this record triggers project status → completed.
class CpcReceivedController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(StoreCpcReceivedRequest $request, Project $project)
    {
        $this->projectService->storeCpcReceived($request->validated(), $project, auth()->user());

        return back()->with('success', 'CPC uploaded. Project is now Completed.');
    }
}
