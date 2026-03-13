<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\EndorseWayleavePhbtRequest;
use App\Http\Requests\Steps\StoreWayleavePhbtRequest;
use App\Models\Project;
use App\Models\WayleavePhbt;
use App\Services\ProjectService;

// Step 6: contractor uploads wayleave file per PBT (up to 3 per project).
// Officer also acts in Step 6 by overwriting the file with the endorsed version.
// endorsement_remarks is set automatically to "Endorsed" when officer uploads.
class WayleavePhbtController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    // Step 6 (Contractor): add a new wayleave PBT record.
    public function store(StoreWayleavePhbtRequest $request, Project $project)
    {
        $this->projectService->storeWayleavePhbt($request->validated(), $project, auth()->user());

        return back()->with('success', 'Wayleave PBT added successfully.');
    }

    // Step 6 (Officer): overwrite wayleave file with endorsed version.
    public function endorse(EndorseWayleavePhbtRequest $request, Project $project, WayleavePhbt $wayleavePhbt)
    {
        $this->projectService->endorseWayleavePhbt($request->validated(), $project, $wayleavePhbt, auth()->user());

        return back()->with('success', 'Wayleave file endorsed successfully.');
    }
}
