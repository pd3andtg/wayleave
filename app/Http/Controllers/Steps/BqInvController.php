<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\EndorseBqInvFileRequest;
use App\Http\Requests\Steps\StoreBqInvFileRequest;
use App\Models\BqInvFile;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Support\Facades\Storage;

// Step 4 (contractor uploads BQ/INV file) and Step 5 (officer endorses each file).
// Supports up to 6 files per project, each identified by file_number (1-6).
// Endorsement type is determined by the file's payment_type (BQ or INV).
class BqInvController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    // Step 4: contractor uploads a BQ/INV file for a specific slot (file_number 1-6).
    public function store(StoreBqInvFileRequest $request, Project $project)
    {
        $this->projectService->storeBqInvFile($request->validated(), $project, auth()->user());

        return back()->with('success', 'BQ/INV file uploaded successfully.');
    }

    // Step 5: officer endorses a specific BQ/INV file.
    // Routes to bq_endorsements or inv_endorsements based on the file payment_type.
    // If an endorsed_file PDF is uploaded, it is stored and its path included in the data.
    public function endorse(EndorseBqInvFileRequest $request, Project $project, BqInvFile $bqInvFile)
    {
        $data = $request->validated();

        // Store the endorsed file if provided, replacing any previous upload.
        if ($request->hasFile('endorsed_file')) {
            $data['endorsed_file'] = $request->file('endorsed_file')
                ->store('projects/' . $project->id . '/bq-endorsements', config('filesystems.default'));
        }

        $this->projectService->endorseBqInvFile($data, $project, $bqInvFile, auth()->user());

        return back()->with('success', 'Endorsement saved.');
    }
}
