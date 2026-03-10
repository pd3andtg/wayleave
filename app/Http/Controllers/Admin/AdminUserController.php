<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

// Admin-only: view all registered users and manage Officer roles.
// Contractor roles cannot be changed — only officers can be promoted/demoted.
class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'company', 'unit'])
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:officer,admin'],
        ]);

        // Contractor roles are fixed — never allow role changes for contractors.
        // This check is a safety net; the UI also hides the form for contractors.
        abort_if($user->hasRole('contractor'), 403, 'Contractor roles cannot be changed.');

        $user->syncRoles([$request->role]);

        return back()->with('success', "Role updated to \"{$request->role}\" for {$user->name}.");
    }
}
