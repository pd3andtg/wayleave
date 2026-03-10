<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;

// Handles the project list, project detail, and project registration (Step 1).
// Thin controller — all business logic is in ProjectService.
// Contractors are scoped to their own company at the service level.
class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->projectService->getProjectList(auth()->user(), $request->only('search', 'status', 'nd_state'));

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
}
