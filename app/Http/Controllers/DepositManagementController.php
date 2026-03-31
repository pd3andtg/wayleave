<?php

namespace App\Http\Controllers;

use App\Services\ProjectService;
use Illuminate\Http\Request;

// Deposit Management page — officer and admin only.
// Lists ALL Deposit-type wayleave payment rows (not just status=required),
// so deposits from cancelled or completed projects remain visible.
// Officers are scoped to their own unit's nd_state automatically.
// Admins can filter by nd_state and see everything.
class DepositManagementController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function index(Request $request)
    {
        $filters       = $request->only(['search', 'nd_state', 'method', 'project_status']);
        $deposits      = $this->projectService->getDepositList(auth()->user(), $filters);
        $totals        = $this->projectService->getDepositTotals(auth()->user(), $filters);
        $statusCounts  = $this->projectService->getDepositProjectStatusCounts(auth()->user(), $filters);

        return view('deposit-management.index', compact('deposits', 'filters', 'totals', 'statusCounts'));
    }

    // Export the filtered deposit list as a CSV file.
    public function export(Request $request)
    {
        $filters  = $request->only(['search', 'nd_state', 'method', 'project_status']);
        $deposits = $this->projectService->getDepositListForExport(auth()->user(), $filters);

        $filename = 'Deposit_Management_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($deposits) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No.', 'Ref No', 'Project Description', 'ND State',
                'PBT', 'Payment Type', 'Method of Payment',
                'Amount (RM)', 'EDS No', 'Application Date',
                'Received / Posted Date', 'Project Status',
            ]);

            $methodLabels = ['BG' => 'BG', 'BD_DAP' => 'BD (DAP)', 'EFT_DAP' => 'EFT (DAP)'];

            foreach ($deposits as $i => $deposit) {
                $project = $deposit->project;

                if (!$project) {
                    $projectStatus = '';
                } elseif ($project->application_status === 'cancelled') {
                    $projectStatus = 'Cancelled';
                } elseif ($project->status === 'completed') {
                    $projectStatus = 'Completed';
                } else {
                    $projectStatus = 'In Progress';
                }

                $pbt = $deposit->wayleavePhbt
                    ? ($deposit->wayleavePhbt->pbt_number . ' — ' .
                       ($deposit->wayleavePhbt->pbt_name === 'Others'
                           ? ($deposit->wayleavePhbt->pbt_name_other ?? 'Others')
                           : $deposit->wayleavePhbt->pbt_name))
                    : '';

                fputcsv($handle, [
                    $i + 1,
                    $project->ref_no ?? '',
                    $project->project_desc ?? '',
                    $project ? str_replace('_', ' ', $project->nd_state) : '',
                    $pbt,
                    $deposit->payment_type,
                    $methodLabels[$deposit->method_of_payment] ?? ($deposit->method_of_payment ?? ''),
                    $deposit->amount !== null ? number_format($deposit->amount, 2) : '',
                    $deposit->eds_no ?? '',
                    $deposit->application_date?->format('d/m/Y') ?? '',
                    $deposit->received_posted_date?->format('d/m/Y') ?? '',
                    $projectStatus,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
