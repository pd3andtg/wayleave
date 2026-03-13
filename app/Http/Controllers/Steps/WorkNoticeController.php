<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StoreWorkNoticeRequest;
use App\Models\Project;
use App\Services\ProjectService;

// Step 10: contractor uploads Notis Mula Kerja and Notis Siap Kerja.
// Gambar (site photos) has been removed from the system.
class WorkNoticeController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(StoreWorkNoticeRequest $request, Project $project)
    {
        $this->projectService->storeWorkNotice($request->validated(), $project, auth()->user());

        return back()->with('success', 'Work notices uploaded successfully.');
    }
}
