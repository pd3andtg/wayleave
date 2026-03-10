<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\EndorseWayleavePhbtRequest;
use App\Http\Requests\Steps\StoreWayleavePhbtRequest;
use App\Models\Project;
use App\Models\WayleavePhbt;
use App\Services\ProjectService;

// Step 4 (contractor uploads wayleave per PBT) and Step 5 (officer endorses each PBT).
class WayleavePhbtController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    // Step 4: contractor adds a wayleave PBT record (up to 3 per project).
    public function store(StoreWayleavePhbtRequest $request, Project $project)
    {
        $this->projectService->storeWayleavePhbt($request->validated(), $project, auth()->user());

        return back()->with('success', 'Wayleave PBT added successfully.');
    }

    // Step 5: officer endorses a specific PBT record.
    public function endorse(EndorseWayleavePhbtRequest $request, Project $project, WayleavePhbt $wayleavePhbt)
    {
        $this->projectService->endorseWayleavePhbt($request->validated(), $project, $wayleavePhbt, auth()->user());

        return back()->with('success', 'PBT endorsement saved.');
    }
}
