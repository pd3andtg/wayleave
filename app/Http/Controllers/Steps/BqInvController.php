<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StoreBoqInvItemRequest;
use App\Http\Requests\Steps\UpdateBoqInvItemRequest;
use App\Models\BoqInvItem;
use App\Models\Project;
use App\Services\ProjectService;

// Section 2 & 3: BOQ/INV items shared table.
// store()  — Contractor adds a new BOQ/INV row (visible in both Section 2 and Section 3).
// update() — Officer/admin updates Section 3 fields (eds_no, payment_status, endorsed file).
//            If a new file is uploaded, it overwrites the contractor's original.
class BqInvController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    // Section 2/3 — "Add New BOQ/INV" button: contractor adds a new row.
    public function store(StoreBoqInvItemRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validated();

        // Store the uploaded file and pass the file object to the service.
        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file');
        }

        $this->projectService->storeBoqInvItem($data, $project, auth()->user());

        return redirect(route('projects.show', $project) . '#section-2')->with('success', 'BOQ/INV item added successfully.');
    }

    // Section 2/3 — Delete a BOQ/INV row. Anyone with project update access can delete.
    public function destroy(Project $project, BoqInvItem $boqInvItem)
    {
        $this->authorize('update', $project);

        $boqInvItem->delete();

        return redirect(route('projects.show', $project) . '#section-3')->with('success', 'BOQ/INV item deleted.');
    }

    // Section 3 — Officer/admin updates a row (eds_no, payment_status, endorsed file).
    public function update(UpdateBoqInvItemRequest $request, Project $project, BoqInvItem $boqInvItem)
    {
        $this->authorize('update', $project);

        $data = $request->validated();

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file');
        }

        $this->projectService->updateBoqInvItem($data, $project, $boqInvItem, auth()->user());

        return redirect(route('projects.show', $project) . '#section-3')->with('success', 'BOQ/INV item updated.');
    }
}
