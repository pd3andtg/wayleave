<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ExampleImageController;
use App\Http\Requests\CancelProjectRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Company;
use App\Models\Node;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Handles the project list, project detail, registration, update, cancel/reopen, and file downloads.
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
            $request->only('search', 'status', 'nd_state', 'per_page')
        );

        // Compute timeline progress count and next step label for each project on this page.
        // Relationships are already eager-loaded by getProjectList — no N+1 queries here.
        $timelineData = [];
        foreach ($projects as $project) {
            $status = $this->projectService->getTimelineStatus($project);
            $timelineData[$project->id] = [
                'count'    => count(array_filter($status)),
                'nextStep' => $this->projectService->getNextStepLabel($status),
            ];
        }

        $statusCounts = $this->projectService->getStatusCounts(auth()->user());

        return view('projects.project-list', compact('projects', 'timelineData', 'statusCounts'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        // Officers/admins need the approved company list to assign the project.
        // Contractors do not see the company dropdown — their company is auto-set.
        $companies = auth()->user()->hasRole('contractor')
            ? collect()
            : Company::where('status', 'approved')->orderBy('name')->get();

        $nodes = Node::orderBy('acronym')->get();

        return view('projects.create-project', compact('companies', 'nodes'));
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
            'node',
            'boqInvItems.endorsedBy',
            'boqInvItems.updatedBy',
            'wayleavePhbts.endorsedBy',
            'wayleavePhbts.payments',
            'wayleavePayments.wayleavePhbt',
            'permitSubmissions.submittedBy',
            'permitReceiveds.uploadedBy',
            'workNotice.uploadedBy',
            'cpcApplication.submittedBy',
            'cpcReceived.uploadedBy',
        ]);

        // Pass timeline completion status and section dates to the view.
        $timelineStatus = $this->projectService->getTimelineStatus($project);
        $timelineDates  = $this->projectService->getTimelineDates($project);

        // Officers/admins need company list for the Section 1 edit form.
        $companies = auth()->user()->hasRole('contractor')
            ? collect()
            : Company::where('status', 'approved')->orderBy('name')->get();

        $nodes = Node::orderBy('acronym')->get();

        // Global example/reference images — same across all projects.
        // Resolved here so Blade templates contain no storage logic.
        $exampleImages = [
            'section8'  => ExampleImageController::exists('section8'),
            'section9'  => ExampleImageController::exists('section9'),
            'section10' => ExampleImageController::exists('section10'),
            'section11' => ExampleImageController::exists('section11'),
            'section12' => ExampleImageController::exists('section12'),
        ];

        return view('projects.project-detail', compact('project', 'timelineStatus', 'timelineDates', 'companies', 'nodes', 'exampleImages'));
    }

    // Section 1: update project information (editable by anyone with access).
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $this->projectService->updateProject($request->validated(), $project, auth()->user());

        return redirect(route('projects.show', $project) . '#section-1')->with('success', 'Project updated successfully.');
    }

    // Cancel a project — anyone (contractor, officer, admin) can cancel.
    // Cancellation reason is compulsory.
    public function cancel(CancelProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $this->projectService->cancelProject($project, $request->validated()['cancellation_reason']);

        return back()->with('success', 'Project cancelled.');
    }

    // Export the filtered project list as a CSV file.
    // Applies the same scoping and filters as the index page (no pagination).
    public function export(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->projectService->getProjectListForExport(
            auth()->user(),
            $request->only('search', 'status', 'nd_state')
        );

        $isOfficerOrAdmin = auth()->user()->hasRole('officer') || auth()->user()->hasRole('admin');

        $statusLabel = match($request->input('status')) {
            'in_progress' => 'In_Progress',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
            default       => 'All',
        };

        $filename = 'Projects_' . $statusLabel . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($projects, $isOfficerOrAdmin) {
            $handle = fopen('php://output', 'w');

            // Header row
            $columns = ['No.', 'Ref No', 'LOR No', 'Project No', 'Project Description', 'ND State', 'PIC Name'];
            if ($isOfficerOrAdmin) {
                $columns[] = 'Company';
            }
            $columns = array_merge($columns, ['Application Date', 'Status', 'Progress (Sections Completed)', 'Payment to PBT', 'Remarks']);
            fputcsv($handle, $columns);

            foreach ($projects as $i => $project) {
                // Derive display status
                if ($project->application_status === 'cancelled') {
                    $displayStatus = 'Cancelled';
                } elseif ($project->status === 'completed') {
                    $displayStatus = 'Completed';
                } else {
                    $displayStatus = 'In Progress';
                }

                $tlStatus  = $this->projectService->getTimelineStatus($project);
                $completed = count(array_filter($tlStatus));

                $row = [
                    $i + 1,
                    $project->ref_no ?? '',
                    $project->lor_no ?? '',
                    $project->project_no ?? '',
                    $project->project_desc,
                    str_replace('_', ' ', $project->nd_state),
                    $project->pic_name,
                ];

                if ($isOfficerOrAdmin) {
                    $row[] = $project->company->name ?? '';
                }

                $row[] = $project->application_date?->format('d/m/Y') ?? '';
                $row[] = $displayStatus;
                $row[] = $completed . '/13';
                $row[] = $project->payment_to_pbt ? ucfirst(str_replace('_', ' ', $project->payment_to_pbt)) : '';
                $row[] = $project->remarks ?? '';

                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Permanently delete a project — admin only.
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $this->projectService->deleteProject($project);

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    // Reopen a cancelled project — admin only.
    public function reopen(Project $project)
    {
        $this->authorize('reopen', $project);

        $this->projectService->reopenProject($project);

        return back()->with('success', 'Project reopened.');
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

        abort_if(!Storage::disk($disk === 's3' ? 's3' : 'local')->exists($path), 404, 'File not found.');

        if ($disk === 's3') {
            return redirect(
                Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(30))
            );
        }

        return Storage::disk('local')->download($path);
    }
}
