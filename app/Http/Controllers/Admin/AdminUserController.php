<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Admin-only: view, register, edit, and delete users. Manage Officer roles.
// Contractor roles cannot be changed — only officers can be promoted/demoted.
class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with(['roles', 'company', 'unit'])
            ->when($search, fn($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%")
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $companies = Company::where('status', 'approved')->orderBy('name')->get();
        $units     = Unit::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'search', 'companies', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8'],
            'role'       => ['required', 'in:admin,officer,contractor'],
            'id_number'      => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'unit_id'        => ['nullable', 'exists:units,id'],
            'company_id'     => ['nullable', 'exists:companies,id'],
        ]);

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'id_number'      => $request->id_number,
            'contact_number' => $request->contact_number,
            'unit_id'        => $request->role === 'officer' ? $request->unit_id : null,
            'company_id'     => $request->role === 'contractor' ? $request->company_id : null,
        ]);

        $user->assignRole($request->role);

        return back()->with('success', "User \"{$request->name}\" registered as {$request->role}.");
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'unique:users,email,' . $user->id],
            'id_number'      => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'unit_id'        => ['nullable', 'exists:units,id'],
            'company_id'     => ['nullable', 'exists:companies,id'],
            'role'           => ['nullable', 'in:officer,admin'],
        ]);

        $user->update([
            'name'           => $request->name,
            'email'          => $request->email,
            'id_number'      => $request->id_number,
            'contact_number' => $request->contact_number,
            'unit_id'        => $request->unit_id,
            'company_id'     => $request->company_id,
        ]);

        // Only update role if submitted and user is not a contractor (contractor roles are fixed).
        if ($request->filled('role') && ! $user->hasRole('contractor')) {
            $user->syncRoles([$request->role]);
        }

        return back()->with('success', "User \"{$user->name}\" updated.");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account.');

        $name = $user->name;
        $user->delete();

        return back()->with('success', "User \"{$name}\" deleted.");
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

    public function suspend(User $user)
    {
        // Prevent admin from suspending their own account.
        abort_if($user->id === auth()->id(), 403, 'You cannot suspend your own account.');

        $user->update(['is_suspended' => true]);

        return back()->with('success', "\"{$user->name}\" has been suspended. They will no longer be able to log in.");
    }

    public function reactivate(User $user)
    {
        $user->update(['is_suspended' => false]);

        return back()->with('success', "\"{$user->name}\" has been reactivated and can log in again.");
    }
}
