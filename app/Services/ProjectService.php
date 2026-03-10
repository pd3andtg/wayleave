<?php

namespace App\Services;

use App\Models\BqInv;
use App\Models\CpcApplication;
use App\Models\CpcReceived;
use App\Models\InvPayment;
use App\Models\PermitReceived;
use App\Models\PermitSubmission;
use App\Models\Project;
use App\Models\User;
use App\Models\WayleavePhbt;
use App\Models\WorkNotice;
use Illuminate\Pagination\LengthAwarePaginator;

// Handles all project business logic across all 10 workflow steps.
// File storage uses the configured default disk (local or S3) — controlled by .env only.
// Contractors are always scoped by company_id — never trust user input alone.
class ProjectService
{
    // ── Helpers ────────────────────────────────────────────────────────────────

    // Stores a file on the configured filesystem disk and returns its path/key.
    private function storeFile($file, string $folder): string
    {
        return $file->store($folder, config('filesystems.default'));
    }

    // ── Project (Step 1) ───────────────────────────────────────────────────────

    public function getProjectList(User $user, array $filters): LengthAwarePaginator
    {
        $query = Project::with('company')->latest();

        // Contractors are always scoped to their own company_id.
        // Officers and Admins bypass this filter to see all projects.
        if ($user->hasRole('contractor')) {
            $query->where('company_id', $user->company_id);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                // ilike is PostgreSQL's case-insensitive LIKE — matches upper, lower, mixed.
                $q->where('ref_no', 'ilike', "%{$search}%")
                  ->orWhere('project_desc', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['nd_state'])) {
            $query->where('nd_state', $filters['nd_state']);
        }

        return $query->paginate(15)->withQueryString();
    }

    public function createProject(array $data, User $user): Project
    {
        return Project::create([
            'ref_no'       => $data['ref_no'] ?? null,
            'lor_no'       => $data['lor_no'] ?? null,
            'project_no'   => $data['project_no'] ?? null,
            'project_desc' => $data['project_desc'],
            'nd_state'     => $data['nd_state'],
            'remarks'      => $data['remarks'] ?? null,
            'company_id'   => $user->company_id,
            'created_by'   => $user->id,
            'status'       => 'outstanding',
        ]);
    }

    public function updateProject(array $data, Project $project): void
    {
        $project->update($data);
    }

    // ── Step 2: BQ/INV Upload (Contractor) ────────────────────────────────────

    public function storeBqInv(array $data, Project $project, User $user): BqInv
    {
        $folder   = 'projects/' . $project->id . '/bq-inv';
        $existing = $project->bqInv;

        $filePath = isset($data['bq_inv_file'])
            ? $this->storeFile($data['bq_inv_file'], $folder)
            : $existing?->bq_inv_file;

        return BqInv::updateOrCreate(
            ['project_id' => $project->id],
            ['bq_inv_file' => $filePath, 'uploaded_by' => $user->id]
        );
    }

    // ── Step 3: Officer Endorsement + Invoice Payments ─────────────────────────

    public function endorseBqInv(array $data, Project $project, User $user): BqInv
    {
        $folder   = 'projects/' . $project->id . '/bq-inv';
        $existing = $project->bqInv;

        $endorsedPath = isset($data['endorsed_file'])
            ? $this->storeFile($data['endorsed_file'], $folder)
            : $existing?->endorsed_file;

        // updateOrCreate — bq_inv_file and uploaded_by are nullable so this works
        // even if the contractor hasn't uploaded yet.
        return BqInv::updateOrCreate(
            ['project_id' => $project->id],
            [
                'endorsed_file'  => $endorsedPath,
                'payment_status' => $data['payment_status'] ?? $existing?->payment_status,
                'endorsed_by'    => $user->id,
            ]
        );
    }

    public function storeInvPayment(array $data, Project $project): InvPayment
    {
        return InvPayment::updateOrCreate(
            ['project_id' => $project->id, 'inv_number' => $data['inv_number']],
            [
                'eds_no'         => $data['eds_no'] ?? null,
                'date'           => $data['date'] ?? null,
                'amount'         => $data['amount'] ?? null,
                'payment_status' => $data['payment_status'] ?? null,
            ]
        );
    }

    // ── Step 4: Wayleave PBT Upload (Contractor) ──────────────────────────────

    public function storeWayleavePhbt(array $data, Project $project, User $user): WayleavePhbt
    {
        $folder       = 'projects/' . $project->id . '/wayleave-pbts';
        $wayleaveFile = $this->storeFile($data['wayleave_file'], $folder);

        return WayleavePhbt::create([
            'project_id'             => $project->id,
            'pbt_number'             => $data['pbt_number'],
            'pbt_name'               => $data['pbt_name'],
            'pbt_name_other'         => $data['pbt_name_other'] ?? null,
            'wayleave_file'          => $wayleaveFile,
            'wayleave_received_date' => $data['wayleave_received_date'],
        ]);
    }

    // ── Step 5: Officer Endorses Wayleave PBT ─────────────────────────────────

    public function endorseWayleavePhbt(array $data, Project $project, WayleavePhbt $pbt, User $user): WayleavePhbt
    {
        $folder = 'projects/' . $project->id . '/wayleave-endorsed';

        $endorsedPath = isset($data['endorsed_file'])
            ? $this->storeFile($data['endorsed_file'], $folder)
            : $pbt->endorsed_file;

        $pbt->update([
            'endorsed_file'        => $endorsedPath,
            'fi_payment'           => $data['fi_payment'] ?? $pbt->fi_payment,
            'fi_eds_no'            => $data['fi_eds_no'] ?? $pbt->fi_eds_no,
            'fi_date'              => $data['fi_date'] ?? $pbt->fi_date,
            'deposit_payment'      => $data['deposit_payment'] ?? $pbt->deposit_payment,
            'deposit_eds_no'       => $data['deposit_eds_no'] ?? $pbt->deposit_eds_no,
            'deposit_payment_type' => $data['deposit_payment_type'] ?? $pbt->deposit_payment_type,
            'deposit_date'         => $data['deposit_date'] ?? $pbt->deposit_date,
            'endorsed_by'          => $user->id,
        ]);

        return $pbt;
    }

    // ── Step 6: Permit Submission (Contractor) ────────────────────────────────

    public function storePermitSubmission(array $data, Project $project, User $user): PermitSubmission
    {
        $folder   = 'projects/' . $project->id . '/permit-submission';
        $existing = $project->permitSubmission;

        $filePath = isset($data['submission_file'])
            ? $this->storeFile($data['submission_file'], $folder)
            : $existing?->submission_file;

        return PermitSubmission::updateOrCreate(
            ['project_id' => $project->id],
            [
                'submit_date'     => $data['submit_date'] ?? $existing?->submit_date,
                'submission_file' => $filePath,
                'submitted_by'    => $user->id,
            ]
        );
    }

    // ── Step 7: Permit Received (Contractor) ──────────────────────────────────

    public function storePermitReceived(array $data, Project $project, User $user): PermitReceived
    {
        $folder   = 'projects/' . $project->id . '/permit-received';
        $existing = $project->permitReceived;

        $filePath = isset($data['permit_file'])
            ? $this->storeFile($data['permit_file'], $folder)
            : $existing?->permit_file;

        return PermitReceived::updateOrCreate(
            ['project_id' => $project->id],
            [
                'permit_received_date' => $data['permit_received_date'] ?? $existing?->permit_received_date,
                'permit_file'          => $filePath,
                'uploaded_by'          => $user->id,
            ]
        );
    }

    // ── Step 8: Work Notices + Site Photos (Contractor) ───────────────────────

    public function storeWorkNotice(array $data, Project $project, User $user): WorkNotice
    {
        $folder   = 'projects/' . $project->id . '/work-notices';
        $existing = $project->workNotice;

        return WorkNotice::updateOrCreate(
            ['project_id' => $project->id],
            [
                'notis_mula_file' => isset($data['notis_mula_file']) ? $this->storeFile($data['notis_mula_file'], $folder) : $existing?->notis_mula_file,
                'notis_siap_file' => isset($data['notis_siap_file']) ? $this->storeFile($data['notis_siap_file'], $folder) : $existing?->notis_siap_file,
                'gambar_file'     => isset($data['gambar_file'])     ? $this->storeFile($data['gambar_file'], $folder)     : $existing?->gambar_file,
                'uploaded_by'     => $user->id,
            ]
        );
    }

    // ── Step 9: CPC Application (Contractor) ──────────────────────────────────

    public function storeCpcApplication(array $data, Project $project, User $user): CpcApplication
    {
        $folder   = 'projects/' . $project->id . '/cpc-application';
        $existing = $project->cpcApplication;

        return CpcApplication::updateOrCreate(
            ['project_id' => $project->id],
            [
                'surat_serahan_file'     => isset($data['surat_serahan_file'])     ? $this->storeFile($data['surat_serahan_file'], $folder)     : $existing?->surat_serahan_file,
                'laporan_bergambar_file' => isset($data['laporan_bergambar_file']) ? $this->storeFile($data['laporan_bergambar_file'], $folder) : $existing?->laporan_bergambar_file,
                'salinan_coa_file'       => isset($data['salinan_coa_file'])       ? $this->storeFile($data['salinan_coa_file'], $folder)       : $existing?->salinan_coa_file,
                'salinan_permit_file'    => isset($data['salinan_permit_file'])    ? $this->storeFile($data['salinan_permit_file'], $folder)    : $existing?->salinan_permit_file,
                'date_submit_to_kutt'    => $data['date_submit_to_kutt'] ?? $existing?->date_submit_to_kutt,
                'submitted_by'           => $user->id,
            ]
        );
    }

    // ── Step 10: CPC Received → Project Completed (Contractor) ────────────────

    public function storeCpcReceived(array $data, Project $project, User $user): CpcReceived
    {
        $folder   = 'projects/' . $project->id . '/cpc-received';
        $existing = $project->cpcReceived;

        $filePath = isset($data['cpc_file'])
            ? $this->storeFile($data['cpc_file'], $folder)
            : $existing?->cpc_file;

        $record = CpcReceived::updateOrCreate(
            ['project_id' => $project->id],
            ['cpc_file' => $filePath, 'uploaded_by' => $user->id]
        );

        // Uploading the CPC marks the project as completed.
        $project->update(['status' => 'completed']);

        return $record;
    }
}
