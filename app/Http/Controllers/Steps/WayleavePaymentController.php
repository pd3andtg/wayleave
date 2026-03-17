<?php

namespace App\Http\Controllers\Steps;

use App\Http\Controllers\Controller;
use App\Http\Requests\Steps\StorePbtWayleavePaymentsRequest;
use App\Http\Requests\Steps\StoreWayleavePaymentRequest;
use App\Http\Requests\Steps\UpdateWayleavePaymentReceivedRequest;
use App\Models\Project;
use App\Models\WayleavePayment;
use App\Services\ProjectService;

// Section 6 (Officer): records FI and Deposit payment details per PBT.
//   One row per payment_type (FI or Deposit) per PBT.
// Section 7 (Officer): records received_posted_date and uploads bg_bd document.
//   Only shows rows where status = required.
//   Uses the same wayleave_payments table — extra columns only.
class WayleavePaymentController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    // Section 6: create or update one payment row (FI or Deposit) per PBT.
    public function store(StoreWayleavePaymentRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $this->projectService->storeWayleavePayment($request->validated(), $project, auth()->user());

        return back()->with('success', 'Payment details saved.');
    }

    // Section 6 (combined): saves both FI and Deposit for one PBT in a single request.
    public function storePbt(StorePbtWayleavePaymentsRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $this->projectService->storePbtWayleavePayments($request->validated(), $project, auth()->user());

        return back()->with('success', 'Payment details saved.');
    }

    // Section 7: update received_posted_date and/or bg_bd_file on an existing payment row.
    public function updateReceived(UpdateWayleavePaymentReceivedRequest $request, Project $project, WayleavePayment $wayleavePayment)
    {
        $this->authorize('update', $project);

        $data = $request->validated();

        if ($request->hasFile('bg_bd_file')) {
            $data['bg_bd_file'] = $request->file('bg_bd_file');
        }

        $this->projectService->updateWayleavePaymentReceived($data, $project, $wayleavePayment, auth()->user());

        return back()->with('success', 'BG/BD received details saved.');
    }
}
