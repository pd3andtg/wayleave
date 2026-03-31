<?php

namespace App\Services;

use App\Models\BoqInvItem;
use App\Models\CpcApplication;
use App\Models\CpcReceived;
use App\Models\PermitReceived;
use App\Models\PermitSubmission;
use App\Models\Project;
use App\Models\User;
use App\Models\WayleavePayment;
use App\Models\WayleavePhbt;
use App\Models\WorkNotice;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

// Handles all project business logic across all 13 workflow sections.
// File storage uses the configured default disk (local or S3) — controlled by .env only.
// Contractors are always scoped by company_id — never trust user input alone.
class ProjectService
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    // Stores a file on the configured filesystem disk and returns its path/key.
    // Filename is auto-generated as: "{ref_no} - {label}.{ext}" for readability.
    // Timestamp prefix added to avoid collisions when the same section is re-uploaded.
    private function storeFile($file, string $folder, Project $project, string $label): string
    {
        $ext      = $file->getClientOriginalExtension() ?: 'pdf';
        $ref      = $project->ref_no
            ? preg_replace('/[^a-zA-Z0-9._-]/', '_', $project->ref_no) . ' - '
            : '';
        $filename = time() . '_' . $ref . $label . '.' . $ext;

        return $file->storeAs($folder, $filename, config('filesystems.default'));
    }

    // ── Project List ──────────────────────────────────────────────────────────

    public function getProjectList(User $user, array $filters): LengthAwarePaginator
    {
        // Eager load all relationships needed for the timeline progress bar on the list page.
        $query = Project::with([
            'company',
            'boqInvItems',
            'wayleavePhbts',
            'wayleavePayments',
            'permitSubmissions',
            'permitReceiveds',
            'workNotice',
            'cpcApplication',
            'cpcReceived',
        ])->latest();

        // Contractors are always scoped to their own company_id.
        // Officers are auto-scoped to their unit's ND state (e.g. ND TRG -> ND_TRG).
        // Admins see all projects with no restriction.
        if ($user->hasRole('contractor')) {
            $query->where('company_id', $user->company_id);
        } elseif ($user->hasRole('officer') && $user->unit) {
            $ndState = strtoupper(str_replace(' ', '_', $user->unit->name));
            $query->where('nd_state', $ndState);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $operator = config('database.default') === 'pgsql' ? 'like' : 'like';
                $q->where('ref_no', $operator, "%{$search}%")
                  ->orWhere('project_desc', $operator, "%{$search}%");
            });
        }

        // Admin can manually filter by ND state. Officers are auto-scoped above.
        if (!empty($filters['nd_state'])) {
            $query->where('nd_state', $filters['nd_state']);
        }

        // Status filter maps the three display states to the two DB columns.
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $query->where('status', 'completed');
            } elseif ($filters['status'] === 'cancelled') {
                $query->where('application_status', 'cancelled');
            } elseif ($filters['status'] === 'in_progress') {
                $query->where('application_status', 'in_progress')
                      ->where('status', '!=', 'completed');
            }
        }

        return $query->paginate(15)->withQueryString();
    }

    // Same as getProjectList but returns all matching records without pagination.
    // Used by the CSV export — no per_page limit applied.
    public function getProjectListForExport(User $user, array $filters): \Illuminate\Database\Eloquent\Collection
    {
        $query = Project::with([
            'company',
            'node',
            'boqInvItems',
            'wayleavePhbts',
            'wayleavePayments',
            'permitSubmissions',
            'permitReceiveds',
            'workNotice',
            'cpcApplication',
            'cpcReceived',
        ])->latest();

        if ($user->hasRole('contractor')) {
            $query->where('company_id', $user->company_id);
        } elseif ($user->hasRole('officer') && $user->unit) {
            $ndState = strtoupper(str_replace(' ', '_', $user->unit->name));
            $query->where('nd_state', $ndState);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $operator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
                $q->where('ref_no', $operator, "%{$search}%")
                  ->orWhere('project_desc', $operator, "%{$search}%");
            });
        }

        if (!empty($filters['nd_state'])) {
            $query->where('nd_state', $filters['nd_state']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $query->where('status', 'completed');
            } elseif ($filters['status'] === 'cancelled') {
                $query->where('application_status', 'cancelled');
            } elseif ($filters['status'] === 'in_progress') {
                $query->where('application_status', 'in_progress')
                      ->where('status', '!=', 'completed');
            }
        }

        return $query->get();
    }

    // Returns in_progress, completed, and cancelled counts for the user's visible scope.
    // Uses the same company/unit scoping as getProjectList but ignores search/status filters
    // so the counts always reflect the full picture, not just the current filtered view.
    public function getStatusCounts(User $user): array
    {
        $base = Project::query();

        if ($user->hasRole('contractor')) {
            $base->where('company_id', $user->company_id);
        } elseif ($user->hasRole('officer') && $user->unit) {
            $ndState = strtoupper(str_replace(' ', '_', $user->unit->name));
            $base->where('nd_state', $ndState);
        }

        return [
            'in_progress' => (clone $base)->where('application_status', 'in_progress')->where('status', '!=', 'completed')->count(),
            'completed'   => (clone $base)->where('status', 'completed')->count(),
            'cancelled'   => (clone $base)->where('application_status', 'cancelled')->count(),
        ];
    }

    // Returns the label for the first incomplete timeline stage.
    // Used on the project list page to show the next action required.
    public function getNextStepLabel(array $timelineStatus): string
    {
        $labels = [
            1  => '01 | NF | PROJECT INFORMATION',
            2  => '02 | NF | UPLOAD BQ/INV',
            3  => '03 | TM | UPLOAD ENDORSED BQ/INV AND UPDATE PAYMENT STATUS',
            4  => '04 | NF | UPLOAD WAYLEAVE',
            5  => '05 | TM | UPLOAD ENDORSED WAYLEAVE',
            6  => '06 | TM | UPDATE BG APPLICATION DATE (DEPOSIT) & FI PAYMENT STATUS',
            7  => '07 | TM | UPLOAD BG & BD RECEIVED FROM FINSSO',
            8  => '08 | NF | UPLOAD PERMIT DOCUMENT SUBMISSION TO PBT',
            9  => '09 | TM | UPLOAD PERMIT RECEIVED FROM PBT',
            10 => '10 | NF | UPLOAD "NOTIS MULA KERJA"',
            11 => '11 | NF | UPLOAD "NOTIS SIAP KERJA"',
            12 => '12 | NF | UPLOAD PERMOHONAN SIJIL PERAKUAN SIAP KERJA (CPC)',
            13 => '13 | NF | UPLOAD SIJIL PERAKUAN SIAP KERJA',
        ];

        foreach ($labels as $step => $label) {
            if (!$timelineStatus[$step]) {
                return $label;
            }
        }

        return 'ALL STEPS COMPLETED';
    }

    // ── Section 1: Create / Update Project ────────────────────────────────────

    public function createProject(array $data, User $user): Project
    {
        // If officer/admin sets self_applied_by_tm = true, use TM's company_id.
        // Otherwise use the submitting user's company_id.
        $isSelfApplied = !empty($data['self_applied_by_tm']);
        if ($isSelfApplied) {
            $tmCompany = \App\Models\Company::where('name', 'TM')->first();
            $companyId = $tmCompany?->id ?? $user->company_id;
        } elseif (!empty($data['company_id'])) {
            // Officer/admin can assign project to any company.
            $companyId = $data['company_id'];
        } else {
            $companyId = $user->company_id;
        }

        return Project::create([
            'ref_no'             => $data['ref_no'] ?? null,
            'lor_no'             => $data['lor_no'] ?? null,
            'project_no'         => $data['project_no'] ?? null,
            'project_desc'       => $data['project_desc'],
            'pic_name'           => $user->name,         // Auto-filled; stored as text to persist if user deleted
            'nd_state'           => $data['nd_state'],
            'node_id'            => $data['node_id'] ?? null,
            'application_date'   => $data['application_date'] ?? null,
            'self_applied_by_tm' => $isSelfApplied,
            'payment_to_pbt'     => $data['payment_to_pbt'] ?? null,
            'remarks'            => $data['remarks'] ?? null,
            'company_id'         => $companyId,
            'created_by'         => $user->id,
            'status'             => 'outstanding',
            'application_status' => 'in_progress',
        ]);
    }

    public function updateProject(array $data, Project $project, User $user): void
    {
        // If officer/admin changes self_applied_by_tm, update company_id accordingly.
        if (isset($data['self_applied_by_tm'])) {
            if ($data['self_applied_by_tm']) {
                $tmCompany = \App\Models\Company::where('name', 'TM')->first();
                $data['company_id'] = $tmCompany?->id ?? $project->company_id;
            } elseif (isset($data['company_id'])) {
                // Keep the provided company_id
            }
        }
        // pic_name is NOT NULL — use empty string when user explicitly clears it (None selected).
        if (!isset($data['pic_name']) || $data['pic_name'] === null) {
            $data['pic_name'] = $project->pic_name ?? '';
        }

        $project->update($data);
    }

    // ── Delete Project ─────────────────────────────────────────────────────────

    // Admin only — permanently deletes the project and all related records.
    public function deleteProject(Project $project): void
    {
        $project->delete();
    }

    // ── Cancel / Reopen Project ────────────────────────────────────────────────

    // Anyone (contractor, officer, admin) can cancel. Reason is compulsory.
    public function cancelProject(Project $project, string $reason): void
    {
        $project->update([
            'application_status'  => 'cancelled',
            'cancellation_reason' => $reason,
        ]);
    }

    // Admin only — reopens a cancelled project.
    public function reopenProject(Project $project): void
    {
        $project->update([
            'application_status'  => 'in_progress',
            'cancellation_reason' => null,
        ]);
    }

    // ── Timeline Completion Logic (13 Stages) ──────────────────────────────────

    // Returns an array of 13 booleans indicating which sections are complete.
    // Used by the project detail view to render the read-only timeline.
    public function getTimelineStatus(Project $project): array
    {
        $boqItems     = $project->boqInvItems;
        $wayleavePhbts = $project->wayleavePhbts;
        $payments     = $project->wayleavePayments;

        // Sections 2 & 3: if payment_to_pbt = waived/not_required, auto-complete (waived).
        $boqWaived = in_array($project->payment_to_pbt, ['waived', 'not_required']);

        // Section 3 complete when ALL rows are settled:
        // BQ rows need status = endorsed OR waived (no "endorsed_and_paid" for BQ).
        // INV rows need status = endorsed_and_paid OR waived.
        $sec3Complete = $boqWaived || (
            $boqItems->count() > 0 &&
            $boqItems->every(function ($item) {
                if ($item->type === 'BQ') {
                    return in_array($item->payment_status, ['endorsed', 'waived']);
                }
                return in_array($item->payment_status, ['endorsed_and_paid', 'waived']);
            })
        );

        // Section 7: all required payment rows must have received_posted_date set.
        $requiredPayments = $payments->where('status', 'required');
        $sec7Complete = $requiredPayments->count() === 0
            ? false
            : $requiredPayments->every(fn($p) => !is_null($p->received_posted_date));

        return [
            1  => true,                                                      // Section 1: project exists
            2  => $boqWaived || $boqItems->count() > 0,                     // Section 2: any boq_inv_items row
            3  => $sec3Complete,                                             // Section 3: all rows endorsed_and_paid or waived
            4  => $wayleavePhbts->count() > 0,                              // Section 4: any PBT record
            5  => $wayleavePhbts->whereNotNull('endorsed_by')->count() > 0, // Section 5: any PBT endorsed
            6  => $payments->count() > 0,                                   // Section 6: any payment record
            7  => $sec7Complete,                                             // Section 7: all required rows posted
            8  => $project->permitSubmissions->count() > 0,                  // Section 8
            9  => $project->permitReceiveds->count() > 0,                   // Section 9
            10 => !is_null($project->workNotice?->notis_mula_file),         // Section 10
            11 => !is_null($project->workNotice?->notis_siap_file),         // Section 11
            12 => !is_null($project->cpcApplication),                       // Section 12
            13 => !is_null($project->cpcReceived),                          // Section 13
        ];
    }

    // Returns the most relevant date for each timeline section.
    // Each section uses the actual business date entered by the user, not system timestamps.
    // Returns null for sections with no data yet (displayed as blank in the view).
    public function getTimelineDates(Project $project): array
    {
        $boqItems      = $project->boqInvItems;
        $wayleavePhbts = $project->wayleavePhbts;
        $payments      = $project->wayleavePayments;

        // Section 3: latest eds_application_date recorded by officer across all BOQ/INV rows.
        $sec3Date = $boqItems->whereNotNull('eds_application_date')
            ->sortByDesc('eds_application_date')->first()?->eds_application_date;

        // Section 5: latest endorsed_date recorded by officer across all PBT rows.
        $sec5Date = $wayleavePhbts->whereNotNull('endorsed_date')
            ->sortByDesc('endorsed_date')->first()?->endorsed_date;

        // Section 7: latest received_posted_date among required payment rows.
        $sec7Date = $payments->where('status', 'required')->whereNotNull('received_posted_date')
            ->sortByDesc('received_posted_date')->first()?->received_posted_date;

        return [
            1  => $project->application_date,                                                                              // Registration date at KUTT/BKI/KUP/PBT
            2  => $boqItems->whereNotNull('date_received')->sortByDesc('date_received')->first()?->date_received,          // Latest date_received in BOQ/INV items
            3  => $sec3Date,                                                                                               // Latest EDS application date
            4  => $wayleavePhbts->whereNotNull('wayleave_received_date')->sortByDesc('wayleave_received_date')->first()?->wayleave_received_date, // Latest wayleave received date
            5  => $sec5Date,                                                                                               // Latest endorsed date
            6  => $payments->whereNotNull('application_date')->sortByDesc('application_date')->first()?->application_date, // Latest FI/Deposit application date
            7  => $sec7Date,                                                                                               // Latest BG/BD received/posted date
            8  => $project->permitSubmissions->whereNotNull('submit_date')->sortByDesc('submit_date')->first()?->submit_date,         // Latest permit submission date
            9  => $project->permitReceiveds->whereNotNull('permit_received_date')->sortByDesc('permit_received_date')->first()?->permit_received_date, // Latest permit received date
            10 => $project->workNotice?->tarikh_mula_kerja,                                                               // Notis Mula date
            11 => $project->workNotice?->tarikh_siap_kerja,                                                               // Notis Siap date
            12 => $project->cpcApplication?->date_submit_to_pbt,                                                          // CPC application submission date
            13 => $project->cpcReceived?->cpc_date,                                                                       // CPC received date
        ];
    }

    // ── Section 2 & 3: BOQ/INV Items ──────────────────────────────────────────

    // Contractor adds a new BOQ/INV row (visible in both Section 2 and Section 3).
    public function storeBoqInvItem(array $data, Project $project, User $user): BoqInvItem
    {
        $folder   = 'projects/' . $project->id . '/boq-inv';
        $boqLabel = implode('_', array_filter([$data['document_info'] ?? null, $data['type'] ?? null])) ?: 'BOQ_INV_File';
        $filePath = isset($data['file']) ? $this->storeFile($data['file'], $folder, $project, $boqLabel) : null;

        return BoqInvItem::create([
            'project_id'    => $project->id,
            'document_info' => $data['document_info'],
            'type'          => $data['type'],
            'date_received' => $data['date_received'],
            'amount'        => $data['amount'] ?? null,
            'file_path'     => $filePath,
            'remarks'       => $data['remarks'] ?? null,
            'updated_by'    => $user->id,
        ]);
    }

    // Officer/admin updates Section 3 fields on an existing row.
    // If a new file is provided, it overwrites the contractor's original.
    public function updateBoqInvItem(array $data, Project $project, BoqInvItem $item, User $user): BoqInvItem
    {
        $folder = 'projects/' . $project->id . '/boq-inv';

        $payload = [
            'document_info'       => $data['document_info'] ?? $item->document_info,
            'type'                => $data['type'] ?? $item->type,
            'date_received'       => $data['date_received'] ?? $item->date_received,
            'amount'              => $data['amount'] ?? $item->amount,
            'eds_no'              => $data['eds_no'] ?? $item->eds_no,
            'eds_application_date'=> $data['eds_application_date'] ?? $item->eds_application_date,
            'payment_status'      => $data['payment_status'] ?? $item->payment_status,
            'remarks'             => $data['remarks'] ?? $item->remarks,
            'updated_by'          => $user->id,
        ];

        // Officer/admin overwrites file if a new one is uploaded.
        if (isset($data['file'])) {
            $endorsedLabel           = implode('_', array_filter([$data['document_info'] ?? $item->document_info, $data['type'] ?? $item->type, 'Endorsed'])) ?: 'BOQ_INV_Endorsed';
            $payload['file_path']    = $this->storeFile($data['file'], $folder, $project, $endorsedLabel);
            $payload['endorsed_by']  = $user->id;
        }

        $item->update($payload);

        return $item;
    }

    // ── Section 4: Wayleave PBT Upload (Contractor) ───────────────────────────

    public function storeWayleavePhbt(array $data, Project $project, User $user): WayleavePhbt
    {
        $folder       = 'projects/' . $project->id . '/wayleave-pbts';
        $wayleaveFile = isset($data['wayleave_file']) ? $this->storeFile($data['wayleave_file'], $folder, $project, 'Wayleave_' . $data['pbt_number']) : null;

        return WayleavePhbt::create([
            'project_id'             => $project->id,
            'pbt_number'             => $data['pbt_number'],
            'pbt_name'               => $data['pbt_name'],
            'pbt_name_other'         => $data['pbt_name_other'] ?? null,
            'wayleave_file'          => $wayleaveFile,
            'wayleave_received_date' => $data['wayleave_received_date'] ?? null,
        ]);
    }

    // Contractor replaces their own wayleave file. Does not touch endorsed_by.
    public function replaceWayleavePhbt(array $data, Project $project, WayleavePhbt $pbt): WayleavePhbt
    {
        $folder       = 'projects/' . $project->id . '/wayleave-pbts';
        $wayleaveFile = $this->storeFile($data['wayleave_file'], $folder, $project, 'Wayleave_' . $pbt->pbt_number);
        $pbt->update(['wayleave_file' => $wayleaveFile]);

        return $pbt;
    }

    // Section 4 (All users): update PBT name, date, and optionally replace file.
    // Does not touch endorsed_by — endorsement is managed separately in Section 5.
    public function updateWayleavePhbt(array $data, Project $project, WayleavePhbt $pbt): WayleavePhbt
    {
        $payload = [
            'pbt_name'               => $data['pbt_name'],
            'pbt_name_other'         => $data['pbt_name'] === 'Others' ? ($data['pbt_name_other'] ?? null) : null,
            'wayleave_received_date' => $data['wayleave_received_date'] ?? null,
        ];

        if (isset($data['wayleave_file'])) {
            $folder            = 'projects/' . $project->id . '/wayleave-pbts';
            $payload['wayleave_file'] = $this->storeFile($data['wayleave_file'], $folder, $project, 'Wayleave_' . $pbt->pbt_number);
        }

        $pbt->update($payload);

        return $pbt;
    }

    // ── Section 5: Officer Endorses Wayleave File ─────────────────────────────

    // Officer uploads the endorsed version, overwriting the contractor's file.
    // Sets endorsed_by to mark which officer endorsed this PBT.
    public function endorseWayleavePhbt(array $data, Project $project, WayleavePhbt $pbt, User $user): WayleavePhbt
    {
        $folder       = 'projects/' . $project->id . '/wayleave-pbts';
        $pbtName      = $pbt->pbt_name === 'Others' ? ($pbt->pbt_name_other ?? 'Others') : $pbt->pbt_name;
        $wayleaveFile = $this->storeFile($data['wayleave_file'], $folder, $project, 'Wayleave_' . $pbtName . '_Endorsed');

        $pbt->update([
            'wayleave_file' => $wayleaveFile,
            'endorsed_by'   => $user->id,
            'endorsed_date' => $data['endorsed_date'] ?? null,
        ]);

        return $pbt;
    }

    // ── Section 6: Wayleave Payment (Officer) ─────────────────────────────────

    // Creates or updates one payment row (FI or Deposit) per PBT.
    public function storeWayleavePayment(array $data, Project $project, User $user): WayleavePayment
    {
        return WayleavePayment::updateOrCreate(
            [
                'project_id'      => $project->id,
                'wayleave_pbt_id' => $data['wayleave_pbt_id'],
                'payment_type'    => $data['payment_type'],
            ],
            [
                'status'            => $data['status'] ?? null,
                'amount'            => $data['amount'] ?? null,
                'eds_no'            => $data['eds_no'] ?? null,
                'method_of_payment' => $data['method_of_payment'] ?? null,
                'application_date'  => $data['application_date'] ?? null,
                'recorded_by'       => $user->id,
            ]
        );
    }

    // Creates or updates both FI and Deposit rows for one PBT in a single call.
    // Called from storePbt — receives fi[*] and deposit[*] grouped data.
    public function storePbtWayleavePayments(array $data, Project $project, User $user): void
    {
        foreach (['fi' => 'FI', 'deposit' => 'Deposit'] as $key => $paymentType) {
            $group = $data[$key] ?? [];
            WayleavePayment::updateOrCreate(
                [
                    'project_id'      => $project->id,
                    'wayleave_pbt_id' => $data['wayleave_pbt_id'],
                    'payment_type'    => $paymentType,
                ],
                [
                    'status'            => $group['status'] ?? null,
                    'amount'            => $group['amount'] ?? null,
                    'eds_no'            => $group['eds_no'] ?? null,
                    'method_of_payment' => $group['method_of_payment'] ?? null,
                    'application_date'  => $group['application_date'] ?? null,
                    'recorded_by'       => $user->id,
                ]
            );
        }
    }

    // ── Section 7: BG & BD Received from FINSSO (Officer) ─────────────────────

    // Updates received_posted_date and/or bg_bd_file on an existing payment row.
    public function updateWayleavePaymentReceived(array $data, Project $project, WayleavePayment $payment, User $user): WayleavePayment
    {
        $folder = 'projects/' . $project->id . '/bg-bd-docs';

        $payload = [
            'received_posted_date' => $data['received_posted_date'] ?? $payment->received_posted_date,
            'recorded_by'          => $user->id,
        ];

        if (isset($data['bg_bd_file'])) {
            $pbt    = $payment->wayleavePhbt;
            $parts  = array_filter([
                $pbt?->pbt_name_other ?? $pbt?->pbt_name,
                $payment->payment_type,
                $payment->method_of_payment,
            ]);
            $label  = implode('_', $parts) ?: 'BG_BD_Document';
            $payload['bg_bd_file_path'] = $this->storeFile($data['bg_bd_file'], $folder, $project, $label);
        }

        $payment->update($payload);

        return $payment;
    }

    // ── Section 8: Permit Submission to PBT (Contractor) ──────────────────────

    // Always creates a new record — up to 3 per project (enforced in the controller).
    public function storePermitSubmission(array $data, Project $project, User $user): PermitSubmission
    {
        $folder   = 'projects/' . $project->id . '/permit-submission';
        $filePath = $this->storeFile($data['submission_file'], $folder, $project, 'Permit_Submission');

        return PermitSubmission::create([
            'project_id'      => $project->id,
            'submit_date'     => $data['submit_date'],
            'submission_file' => $filePath,
            'submitted_by'    => $user->id,
        ]);
    }

    public function updatePermitSubmission(array $data, PermitSubmission $submission, Project $project, User $user): PermitSubmission
    {
        $folder   = 'projects/' . $project->id . '/permit-submission';
        $filePath = isset($data['submission_file'])
            ? $this->storeFile($data['submission_file'], $folder, $project, 'Permit_Submission')
            : $submission->submission_file;

        $submission->update([
            'submit_date'     => $data['submit_date'],
            'submission_file' => $filePath,
            'submitted_by'    => $user->id,
        ]);

        return $submission;
    }

    public function deletePermitSubmission(PermitSubmission $submission): void
    {
        Storage::disk(config('filesystems.default'))->delete($submission->submission_file);
        $submission->delete();
    }

    // ── Section 9: Permit Received (Contractor/Officer) ───────────────────────

    // Always creates a new record — up to 3 per project (enforced in the controller).
    public function storePermitReceived(array $data, Project $project, User $user): PermitReceived
    {
        $folder   = 'projects/' . $project->id . '/permit-received';
        $filePath = $this->storeFile($data['permit_file'], $folder, $project, 'Permit_Received');

        return PermitReceived::create([
            'project_id'           => $project->id,
            'permit_received_date' => $data['permit_received_date'],
            'permit_file'          => $filePath,
            'remarks'              => $data['remarks'] ?? null,
            'uploaded_by'          => $user->id,
        ]);
    }

    public function updatePermitReceived(array $data, PermitReceived $permitReceived, Project $project, User $user): PermitReceived
    {
        $folder   = 'projects/' . $project->id . '/permit-received';
        $filePath = isset($data['permit_file'])
            ? $this->storeFile($data['permit_file'], $folder, $project, 'Permit_Received')
            : $permitReceived->permit_file;

        $permitReceived->update([
            'permit_received_date' => $data['permit_received_date'],
            'permit_file'          => $filePath,
            'remarks'              => $data['remarks'] ?? null,
            'uploaded_by'          => $user->id,
        ]);

        return $permitReceived;
    }

    public function deletePermitReceived(PermitReceived $permitReceived): void
    {
        Storage::disk(config('filesystems.default'))->delete($permitReceived->permit_file);
        $permitReceived->delete();
    }

    // ── Section 10: Notis Mula Kerja (Contractor) ─────────────────────────────

    public function storeNotisMula(array $data, Project $project, User $user): WorkNotice
    {
        $folder   = 'projects/' . $project->id . '/work-notices';
        $existing = $project->workNotice;

        $filePath = $this->storeFile($data['notis_mula_file'], $folder, $project, 'Notis_Mula_Kerja');

        return WorkNotice::updateOrCreate(
            ['project_id' => $project->id],
            [
                'notis_mula_file'   => $filePath,
                'tarikh_mula_kerja' => $data['tarikh_mula_kerja'],
                'notis_siap_file'   => $existing?->notis_siap_file,  // Preserve existing notis siap
                'tarikh_siap_kerja' => $existing?->tarikh_siap_kerja, // Preserve existing tarikh siap
                'uploaded_by'       => $user->id,
            ]
        );
    }

    // ── Section 11: Notis Siap Kerja (Contractor) ─────────────────────────────

    public function storeNotisSiap(array $data, Project $project, User $user): WorkNotice
    {
        $folder   = 'projects/' . $project->id . '/work-notices';
        $existing = $project->workNotice;

        $filePath = $this->storeFile($data['notis_siap_file'], $folder, $project, 'Notis_Siap_Kerja');

        return WorkNotice::updateOrCreate(
            ['project_id' => $project->id],
            [
                'notis_mula_file'   => $existing?->notis_mula_file,  // Preserve existing notis mula
                'tarikh_mula_kerja' => $existing?->tarikh_mula_kerja, // Preserve existing tarikh mula
                'notis_siap_file'   => $filePath,
                'tarikh_siap_kerja' => $data['tarikh_siap_kerja'],
                'uploaded_by'       => $user->id,
            ]
        );
    }

    // ── Section 12: CPC Application (Contractor) ──────────────────────────────

    public function storeCpcApplication(array $data, Project $project, User $user): CpcApplication
    {
        $folder   = 'projects/' . $project->id . '/cpc-application';
        $existing = $project->cpcApplication;

        return CpcApplication::updateOrCreate(
            ['project_id' => $project->id],
            [
                'surat_serahan_file'     => isset($data['surat_serahan_file'])     ? $this->storeFile($data['surat_serahan_file'], $folder, $project, 'CPC_Surat_Serahan')         : $existing?->surat_serahan_file,
                'laporan_bergambar_file' => isset($data['laporan_bergambar_file']) ? $this->storeFile($data['laporan_bergambar_file'], $folder, $project, 'CPC_Laporan_Bergambar')  : $existing?->laporan_bergambar_file,
                'salinan_coa_file'       => isset($data['salinan_coa_file'])       ? $this->storeFile($data['salinan_coa_file'], $folder, $project, 'CPC_Salinan_COA')            : $existing?->salinan_coa_file,
                'salinan_permit_file'    => isset($data['salinan_permit_file'])    ? $this->storeFile($data['salinan_permit_file'], $folder, $project, 'CPC_Salinan_Permit')      : $existing?->salinan_permit_file,
                'date_submit_to_pbt'     => $data['date_submit_to_pbt'] ?? $existing?->date_submit_to_pbt,
                'submitted_by'           => $user->id,
            ]
        );
    }

    // Delete one specific file from a CPC application and null its column.
    // $field must be one of the four valid file columns (validated in controller).
    public function deleteCpcFile(CpcApplication $cpcApp, string $field): void
    {
        if ($cpcApp->$field) {
            Storage::disk(config('filesystems.default'))->delete($cpcApp->$field);
        }
        $cpcApp->update([$field => null]);
    }

    // Delete all four uploaded files from a CPC application and null all file columns.
    public function deleteAllCpcFiles(CpcApplication $cpcApp): void
    {
        $disk = Storage::disk(config('filesystems.default'));
        foreach (['surat_serahan_file', 'laporan_bergambar_file', 'salinan_coa_file', 'salinan_permit_file'] as $field) {
            if ($cpcApp->$field) {
                $disk->delete($cpcApp->$field);
            }
        }
        $cpcApp->update([
            'surat_serahan_file'     => null,
            'laporan_bergambar_file' => null,
            'salinan_coa_file'       => null,
            'salinan_permit_file'    => null,
        ]);
    }

    // ── Section 13: CPC Received → Project Completed (Contractor) ─────────────

    public function storeCpcReceived(array $data, Project $project, User $user): CpcReceived
    {
        $folder   = 'projects/' . $project->id . '/cpc-received';
        $existing = $project->cpcReceived;

        $filePath = isset($data['cpc_file'])
            ? $this->storeFile($data['cpc_file'], $folder, $project, 'CPC_Sijil_Perakuan')
            : $existing?->cpc_file;

        $record = CpcReceived::updateOrCreate(
            ['project_id' => $project->id],
            [
                'cpc_file'    => $filePath,
                'cpc_date'    => $data['cpc_date'] ?? $existing?->cpc_date,
                'uploaded_by' => $user->id,
            ]
        );

        // Uploading the CPC marks the project as completed.
        $project->update(['status' => 'completed']);

        return $record;
    }

    // ── Deposit Management ────────────────────────────────────────────────────

    // Returns a paginated list of Deposit-type wayleave payment rows where status = required.
    // Officers are scoped to their unit's nd_state. Admins see all.
    // Supports search (ref_no / project_desc), admin-only nd_state filter, and method_of_payment filter.
    public function getDepositList(User $user, array $filters): LengthAwarePaginator
    {
        $query = WayleavePayment::with(['project.company', 'wayleavePhbt'])
            ->where('payment_type', 'Deposit')
            ->where('status', 'required')
            ->whereHas('project', function ($q) use ($user, $filters) {
                // Officers are auto-scoped to their unit's nd_state.
                if ($user->hasRole('officer') && $user->unit) {
                    $ndState = strtoupper(str_replace(' ', '_', $user->unit->name));
                    $q->where('nd_state', $ndState);
                }

                // Admin-only nd_state filter.
                if (!empty($filters['nd_state'])) {
                    $q->where('nd_state', $filters['nd_state']);
                }

                // Search by ref_no or project_desc.
                if (!empty($filters['search'])) {
                    $search   = $filters['search'];
                    $operator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
                    $q->where(function ($sq) use ($search, $operator) {
                        $sq->where('ref_no', $operator, "%{$search}%")
                           ->orWhere('project_desc', $operator, "%{$search}%");
                    });
                }
            });

        // Method of payment filter (BG / BD_DAP / EFT_DAP).
        if (!empty($filters['method'])) {
            $query->where('method_of_payment', $filters['method']);
        }

        return $query->latest()->paginate(20)->withQueryString();
    }

    // Returns totals (count + sum of amount) per method_of_payment for the summary cards.
    // Applies the same search/nd_state filters but ignores the method filter so totals are always visible.
    public function getDepositTotals(User $user, array $filters): array
    {
        $query = WayleavePayment::where('payment_type', 'Deposit')
            ->where('status', 'required')
            ->whereHas('project', function ($q) use ($user, $filters) {
                if ($user->hasRole('officer') && $user->unit) {
                    $ndState = strtoupper(str_replace(' ', '_', $user->unit->name));
                    $q->where('nd_state', $ndState);
                }
                if (!empty($filters['nd_state'])) {
                    $q->where('nd_state', $filters['nd_state']);
                }
                if (!empty($filters['search'])) {
                    $search   = $filters['search'];
                    $operator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
                    $q->where(function ($sq) use ($search, $operator) {
                        $sq->where('ref_no', $operator, "%{$search}%")
                           ->orWhere('project_desc', $operator, "%{$search}%");
                    });
                }
            });

        $rows = $query->selectRaw('method_of_payment, COUNT(*) as total_count, SUM(amount) as total_amount')
                      ->groupBy('method_of_payment')
                      ->get()
                      ->keyBy('method_of_payment');

        $methods = ['BG', 'BD_DAP', 'EFT_DAP'];
        $totals  = [];
        foreach ($methods as $method) {
            $totals[$method] = [
                'count'  => $rows[$method]->total_count ?? 0,
                'amount' => $rows[$method]->total_amount ?? 0,
            ];
        }

        return $totals;
    }
}
