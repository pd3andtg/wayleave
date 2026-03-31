<?php

namespace App\Http\Controllers;

use App\Services\ProjectService;
use Illuminate\Http\Request;

// Deposit Management page — officer and admin only.
// Lists all Deposit-type wayleave payment rows where status = required.
// Officers are scoped to their own unit's nd_state automatically.
// Admins can filter by nd_state and see everything.
class DepositManagementController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function index(Request $request)
    {
        $filters  = $request->only(['search', 'nd_state', 'method']);
        $deposits = $this->projectService->getDepositList(auth()->user(), $filters);
        $totals   = $this->projectService->getDepositTotals(auth()->user(), $filters);

        return view('deposit-management.index', compact('deposits', 'filters', 'totals'));
    }
}
