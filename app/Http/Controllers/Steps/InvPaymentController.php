<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StoreInvPaymentRequest;
use App\Models\Project;
use App\Services\ProjectService;

// Step 3 (officer): manages invoice payment records (INV1, INV2, INV3).
// Receives all three rows at once and saves each via the service.
class InvPaymentController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(StoreInvPaymentRequest $request, Project $project)
    {
        foreach ($request->validated()['inv'] as $invNumber => $row) {
            $this->projectService->storeInvPayment(
                array_merge($row, ['inv_number' => $invNumber]),
                $project
            );
        }

        return back()->with('success', 'Invoice payments saved.');
    }
}
