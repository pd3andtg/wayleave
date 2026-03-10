<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\EndorseBqInvRequest;
use App\Http\Requests\Steps\StoreBqInvRequest;
use App\Models\Project;
use App\Services\ProjectService;

// Step 2 (contractor uploads BQ/INV) and Step 3 (officer endorses + sets payment status).
class BqInvController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    // Step 2: contractor uploads the BQ/INV file.
    public function store(StoreBqInvRequest $request, Project $project)
    {
        $this->projectService->storeBqInv($request->validated(), $project, auth()->user());

        return back()->with('success', 'BQ/INV uploaded successfully.');
    }

    // Step 3: officer endorses the BQ/INV, sets payment status, and saves all INV rows.
    public function endorse(EndorseBqInvRequest $request, Project $project)
    {
        $validated = $request->validated();
        $this->projectService->endorseBqInv($validated, $project, auth()->user());

        // Save INV payment rows — skip rows where the user left all fields blank.
        foreach ($validated['inv'] ?? [] as $invNumber => $row) {
            $hasData = !empty($row['eds_no']) || !empty($row['date'])
                    || !empty($row['amount']) || !empty($row['payment_status']);
            if (!$hasData) {
                continue;
            }
            $this->projectService->storeInvPayment(
                array_merge($row, ['inv_number' => $invNumber]),
                $project
            );
        }

        return back()->with('success', 'BQ/INV endorsement saved.');
    }
}
