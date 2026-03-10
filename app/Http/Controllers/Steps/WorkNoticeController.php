<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StoreWorkNoticeRequest;
use App\Models\Project;
use App\Services\ProjectService;

// Step 8: contractor uploads Notis Mula, Notis Siap, and combined site photos PDF.
class WorkNoticeController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(StoreWorkNoticeRequest $request, Project $project)
    {
        $this->projectService->storeWorkNotice($request->validated(), $project, auth()->user());

        return back()->with('success', 'Work notices uploaded successfully.');
    }
}
