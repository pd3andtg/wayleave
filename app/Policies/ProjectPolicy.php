<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

// Authorises all project-level actions.
// Key rule: contractors can only access projects belonging to their own company.
// Officers and admins have no project-level restrictions.
class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        // All authenticated users can view the project list.
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        // Contractors are always scoped to their own company_id.
        if ($user->hasRole('contractor')) {
            return $user->company_id === $project->company_id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        // All authenticated users can register new projects.
        return true;
    }

    // Reopen a cancelled project — admin only.
    public function reopen(User $user, Project $project): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Project $project): bool
    {
        // Officers and admins can edit any project.
        // Contractors can only edit their own company's projects.
        if ($user->hasRole('contractor')) {
            return $user->company_id === $project->company_id;
        }

        return true;
    }
}
