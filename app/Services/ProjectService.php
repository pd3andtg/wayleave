<?php

namespace App\Services;

use App\Models\BqEndorsement;
use App\Models\BqInvFile;
use App\Models\CpcApplication;
use App\Models\CpcReceived;
use App\Models\InvEndorsement;
use App\Models\PermitReceived;
use App\Models\PermitSubmission;
use App\Models\Project;
use App\Models\User;
use App\Models\WayleavePayment;
use App\Models\WayleavePhbt;
use App\Models\WorkNotice;
use Illuminate\Pagination\LengthAwarePaginator;

// Handles all project business logic across all 12 workflow steps.
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
                // Use 'like' for SQLite compatibility (case-insensitive for ASCII by default).
                // PostgreSQL also supports 'like'; switch to 'ilike' only if case sensitivity
                // becomes an issue on the production PostgreSQL database.
                $q->where('ref_no', 'like', "%{$search}%")
                  ->orWhere('project_desc', 'like', "%{$search}%");
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

    // ── Step 4: BQ/INV File Upload (Contractor) ───────────────────────────────

    // Stores a single BQ/INV file for the given file_number slot (1-6).
    // If a record for that slot already exists, it is replaced.
    public function storeBqInvFile(array $data, Project $project, User $user): BqInvFile
    {
        $folder   = 'projects/' . $project->id . '/bq-inv-files';
        $existing = $project->bqInvFiles()->where('file_number', $data['file_number'])->first();

        $filePath = isset($data['file_path'])
            ? $this->storeFile($data['file_path'], $folder)
            : $existing?->file_path;

        return BqInvFile::updateOrCreate(
            ['project_id' => $project->id, 'file_number' => $data['file_number']],
            [
                'file_path'     => $filePath,
                'document_info' => $data['document_info'],
                'payment_type'  => $data['payment_type'],
                'date'          => $data['date'],
                'amount'        => $data['amount'],
                'eds_no'        => $data['eds_no'],
                'remarks'       => $data['remarks'] ?? null,
                'uploaded_by'   => $user->id,
            ]
        );
    }

    // ── Step 5: Officer Endorses a BQ/INV File ────────────────────────────────

    // Routes to bq_endorsements or inv_endorsements based on the file payment_type.
    public function endorseBqInvFile(array $data, Project $project, BqInvFile $bqInvFile, User $user): BqEndorsement|InvEndorsement
    {
        if ($bqInvFile->payment_type === 'BQ') {
            $bqPayload = [
                'project_id'    => $project->id,
                'document_info' => $data['document_info'],
                'date'          => $data['date'],
                'remarks'       => $data['remarks'] ?? null,
                'endorsed_by'   => $user->id,
            ];

            // Only update endorsed_file if a new file was provided — preserves existing file otherwise.
            if (isset($data['endorsed_file'])) {
                $bqPayload['endorsed_file'] = $data['endorsed_file'];
            }

            return BqEndorsement::updateOrCreate(
                ['bq_inv_file_id' => $bqInvFile->id],
                $bqPayload
            );
        }

        // INV type — includes amount, eds_no, and payment_status.
        $invPayload = [
            'project_id'     => $project->id,
            'document_info'  => $data['document_info'],
            'date'           => $data['date'],
            'amount'         => $data['amount'],
            'payment_status' => $data['payment_status'],
            'eds_no'         => $data['eds_no'],
            'remarks'        => $data['remarks'] ?? null,
            'endorsed_by'    => $user->id,
        ];

        // Only update endorsed_file if a new file was provided — preserves existing file otherwise.
        if (isset($data['endorsed_file'])) {
            $invPayload['endorsed_file'] = $data['endorsed_file'];
        }

        return InvEndorsement::updateOrCreate(
            ['bq_inv_file_id' => $bqInvFile->id],
            $invPayload
        );
    }

    // ── Step 6: Wayleave PBT Upload (Contractor) ──────────────────────────────

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

    // ── Step 6 (Officer): Overwrite Wayleave File ─────────────────────────────

    // Officer uploads the endorsed version, overwriting the contractor's file.
    // endorsement_remarks is automatically set to "Endorsed" on upload.
    public function endorseWayleavePhbt(array $data, Project $project, WayleavePhbt $pbt, User $user): WayleavePhbt
    {
        $folder       = 'projects/' . $project->id . '/wayleave-pbts';
        $wayleaveFile = $this->storeFile($data['wayleave_file'], $folder);

        $pbt->update([
            'wayleave_file'       => $wayleaveFile,
            'endorsement_remarks' => 'Endorsed',
            'endorsed_by'         => $user->id,
        ]);

        return $pbt;
    }

    // ── Step 3 (Contractor): Replace Wayleave File ────────────────────────────

    // Contractor replaces their own wayleave file before officer endorsement.
    // Does not touch endorsed_by or endorsement_remarks.
    public function replaceWayleavePhbt(array $data, Project $project, WayleavePhbt $pbt): WayleavePhbt
    {
        $folder       = 'projects/' . $project->id . '/wayleave-pbts';
        $wayleaveFile = $this->storeFile($data['wayleave_file'], $folder);

        $pbt->update(['wayleave_file' => $wayleaveFile]);

        return $pbt;
    }

    // ── Step 7: Wayleave Payment (Officer) ────────────────────────────────────

    public function storeWayleavePayment(array $data, Project $project, User $user): WayleavePayment
    {
        return WayleavePayment::updateOrCreate(
            ['project_id' => $project->id, 'wayleave_pbt_id' => $data['wayleave_pbt_id']],
            [
                'fi_payment'               => $data['fi_payment'] ?? null,
                'fi_eds_no'                => $data['fi_eds_no'] ?? null,
                'fi_application_date'      => $data['fi_application_date'] ?? null,
                'deposit_payment'          => $data['deposit_payment'] ?? null,
                'deposit_eds_no'           => $data['deposit_eds_no'] ?? null,
                'deposit_payment_type'     => $data['deposit_payment_type'] ?? null,
                'deposit_application_date' => $data['deposit_application_date'] ?? null,
                'recorded_by'              => $user->id,
            ]
        );
    }

    // ── Step 8: Permit Submission to KUTT (Contractor) ────────────────────────

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

    // ── Step 9: Permit Received (Contractor) ──────────────────────────────────

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

    // ── Step 10: Work Notices (Contractor) ────────────────────────────────────

    // Gambar (site photos) has been removed from the system.
    // Only Notis Mula and Notis Siap are stored.
    public function storeWorkNotice(array $data, Project $project, User $user): WorkNotice
    {
        $folder   = 'projects/' . $project->id . '/work-notices';
        $existing = $project->workNotice;

        return WorkNotice::updateOrCreate(
            ['project_id' => $project->id],
            [
                'notis_mula_file' => isset($data['notis_mula_file']) ? $this->storeFile($data['notis_mula_file'], $folder) : $existing?->notis_mula_file,
                'notis_siap_file' => isset($data['notis_siap_file']) ? $this->storeFile($data['notis_siap_file'], $folder) : $existing?->notis_siap_file,
                'uploaded_by'     => $user->id,
            ]
        );
    }

    // ── Step 11: CPC Application (Contractor) ─────────────────────────────────

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

    // ── Step 12: CPC Received → Project Completed (Contractor) ───────────────

    public function storeCpcReceived(array $data, Project $project, User $user): CpcReceived
    {
        $folder   = 'projects/' . $project->id . '/cpc-received';
        $existing = $project->cpcReceived;

        $filePath = isset($data['cpc_file'])
            ? $this->storeFile($data['cpc_file'], $folder)
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
}
