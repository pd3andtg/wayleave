<?php

namespace App\Policies;

use App\Models\DocumentReference;
use App\Models\User;

// All authenticated users can view and download document references.
// Only admin can upload, edit, or delete.
class DocumentReferencePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, DocumentReference $reference): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, DocumentReference $reference): bool
    {
        return $user->hasRole('admin');
    }
}
