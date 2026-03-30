<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\EndorseWayleavePhbtRequest;
use App\Http\Requests\Steps\ReplaceWayleavePhbtRequest;
use App\Http\Requests\Steps\StoreWayleavePhbtRequest;
use App\Http\Requests\Steps\UpdateWayleavePhbtRequest;
use App\Models\Project;
use App\Models\WayleavePhbt;
use App\Services\ProjectService;

// Section 4: Contractor uploads wayleave file per PBT (up to 3 per project).
// Section 5: Officer uploads endorsed version, overwriting contractor's file.
//            Sets endorsed_by — no other fields in Section 5.
class WayleavePhbtController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    // Section 4 (Contractor): add a new wayleave PBT record.
    public function store(StoreWayleavePhbtRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $this->projectService->storeWayleavePhbt($request->validated(), $project, auth()->user());

        return redirect(route('projects.show', $project) . '#section-4')->with('success', 'Wayleave PBT added successfully.');
    }

    // Section 4 (All users): update PBT name, date, and optionally the file.
    public function update(UpdateWayleavePhbtRequest $request, Project $project, WayleavePhbt $wayleavePhbt)
    {
        $this->authorize('update', $project);

        $this->projectService->updateWayleavePhbt($request->validated(), $project, $wayleavePhbt);

        return redirect(route('projects.show', $project) . '#section-4')->with('success', 'Wayleave PBT updated successfully.');
    }

    // Section 4 (Contractor): replace their own wayleave file before officer endorsement.
    public function replace(ReplaceWayleavePhbtRequest $request, Project $project, WayleavePhbt $wayleavePhbt)
    {
        $this->authorize('update', $project);

        $this->projectService->replaceWayleavePhbt($request->validated(), $project, $wayleavePhbt);

        return redirect(route('projects.show', $project) . '#section-4')->with('success', 'Wayleave file replaced successfully.');
    }

    // Section 5 (Officer/Admin): upload endorsed file, sets endorsed_by.
    // File overwrites contractor's original — one shared file_path per PBT.
    public function endorse(EndorseWayleavePhbtRequest $request, Project $project, WayleavePhbt $wayleavePhbt)
    {
        $this->authorize('update', $project);

        $this->projectService->endorseWayleavePhbt($request->validated(), $project, $wayleavePhbt, auth()->user());

        return redirect(route('projects.show', $project) . '#section-5')->with('success', 'Wayleave endorsed successfully.');
    }
}
