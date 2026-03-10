<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Handles the project list, project detail, registration (Step 1), and file downloads.
// Thin controller — all business logic is in ProjectService.
// Contractors are scoped to their own company at the service level.
class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->projectService->getProjectList(
            auth()->user(),
            $request->only('search', 'status', 'nd_state')
        );

        return view('projects.project-list', compact('projects'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        return view('projects.create-project');
    }

    public function store(StoreProjectRequest $request)
    {
        $project = $this->projectService->createProject(
            $request->validated(),
            auth()->user()
        );

        return redirect()->route('projects.show', $project)
                         ->with('success', 'Project registered successfully.');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load([
            'company',
            'createdBy',
            'bqInv.uploadedBy',
            'bqInv.endorsedBy',
            'invPayments',
            'wayleavePhbts.endorsedBy',
            'permitSubmission.submittedBy',
            'permitReceived.uploadedBy',
            'workNotice.uploadedBy',
            'cpcApplication.submittedBy',
            'cpcReceived.uploadedBy',
        ]);

        return view('projects.project-detail', compact('project'));
    }

    // Step 1: update project information.
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->projectService->updateProject($request->validated(), $project);

        return back()->with('success', 'Project updated successfully.');
    }

    // Serves uploaded files securely.
    // On S3: redirects to a signed temporary URL (30 min expiry).
    // On local disk: streams the file directly.
    public function downloadFile(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $path = $request->query('path');

        // Ensure the path belongs to this project to prevent directory traversal.
        abort_if(!str_starts_with($path, 'projects/' . $project->id . '/'), 403);

        $disk = config('filesystems.default');

        if ($disk === 's3') {
            return redirect(
                Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(30))
            );
        }

        return Storage::disk('local')->download($path);
    }
}
