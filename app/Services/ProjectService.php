<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

// Handles all project business logic.
// Contractor queries are always scoped by company_id — never trust user input alone.
class ProjectService
{
    public function getProjectList(User $user, array $filters): LengthAwarePaginator
    {
        $query = Project::with('company')->latest();

        // Contractors are always scoped to their own company_id.
        // Officers and Admins bypass this filter to see all projects.
        if ($user->hasRole('contractor')) {
            $query->where('company_id', $user->company_id);
        }

        // Search by KUTT/PBT Ref No or Project Description.
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
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
}
