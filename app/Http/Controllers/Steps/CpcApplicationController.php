<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StoreCpcApplicationRequest;
use App\Models\CpcApplication;
use App\Models\Project;
use App\Services\ProjectService;

// Section 12: contractor uploads CPC application documents and submits to PBT.
class CpcApplicationController extends Controller
{
    // The four valid file columns — used to whitelist the $field parameter.
    private const FILE_FIELDS = [
        'surat_serahan_file',
        'laporan_bergambar_file',
        'salinan_coa_file',
        'salinan_permit_file',
    ];

    public function __construct(private ProjectService $projectService) {}

    public function store(StoreCpcApplicationRequest $request, Project $project)
    {
        $this->projectService->storeCpcApplication($request->validated(), $project, auth()->user());

        return redirect(route('projects.show', $project) . '#section-12')->with('success', 'CPC application submitted.');
    }

    // Delete one specific file from the CPC application record.
    // $field is whitelisted against FILE_FIELDS to prevent arbitrary column injection.
    public function destroyFile(Project $project, CpcApplication $cpcApplication, string $field)
    {
        $this->authorize('update', $project);

        if (!in_array($field, self::FILE_FIELDS, true)) {
            abort(404);
        }

        $this->projectService->deleteCpcFile($cpcApplication, $field);

        return redirect(route('projects.show', $project) . '#section-12')->with('success', 'File deleted.');
    }

    // Delete all four uploaded files from the CPC application record.
    public function destroyAllFiles(Project $project, CpcApplication $cpcApplication)
    {
        $this->authorize('update', $project);

        $this->projectService->deleteAllCpcFiles($cpcApplication);

        return redirect(route('projects.show', $project) . '#section-12')->with('success', 'All CPC files deleted.');
    }
}
