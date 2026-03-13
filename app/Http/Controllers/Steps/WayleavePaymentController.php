<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StoreWayleavePaymentRequest;
use App\Models\Project;
use App\Services\ProjectService;

// Step 7 (officer): records FI and deposit payment details per PBT.
// Separated from wayleave_pbts so Step 6 (file/endorsement) and Step 7 (payment)
// have distinct completion boundaries for the project timeline.
class WayleavePaymentController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(StoreWayleavePaymentRequest $request, Project $project)
    {
        $this->projectService->storeWayleavePayment($request->validated(), $project, auth()->user());

        return back()->with('success', 'Payment details saved.');
    }
}
