<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StoreNotisMulaRequest;
use App\Http\Requests\Steps\StoreNotisSiapRequest;
use App\Models\Project;
use App\Services\ProjectService;

// Section 10: Contractor uploads Notis Mula Kerja only.
// Section 10: Contractor uploads Notis Siap Kerja only.
// Both use the same work_notices table — rendered as two separate sections.
// Each has its own completion indicator on the project timeline.
// Gambar (site photos) has been removed from the system entirely.
class WorkNoticeController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    // Section 10: upload Notis Mula Kerja.
    public function storeNotisMula(StoreNotisMulaRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $this->projectService->storeNotisMula($request->validated(), $project, auth()->user());

        return back()->with('success', 'Notis Mula Kerja uploaded successfully.');
    }

    // Section 11: upload Notis Siap Kerja.
    public function storeNotisSiap(StoreNotisSiapRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $this->projectService->storeNotisSiap($request->validated(), $project, auth()->user());

        return back()->with('success', 'Notis Siap Kerja uploaded successfully.');
    }
}
