<?php

namespace App\Http\Controllers;

use App\Mail\UserApproved;
use App\Mail\UserRejected;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

// Handles the user approval queue.
// Both Admin and Officer can approve or reject pending user registrations.
// Approved users receive an email and can log in immediately.
// Rejected users receive an email explaining the outcome.
class ApprovalController extends Controller
{
    public function index()
    {
        // Show all non-admin pending users, most recent first.
        $pendingUsers = User::where('status', 'pending')
            ->with(['company', 'unit'])
            ->latest()
            ->get();

        return view('approvals.index', compact('pendingUsers'));
    }

    public function approve(User $user)
    {
        $user->update(['status' => 'approved']);

        Mail::to($user->email)->send(new UserApproved($user));

        return back()->with('success', "Account for \"{$user->name}\" approved.");
    }

    public function reject(User $user)
    {
        $user->update(['status' => 'rejected']);

        Mail::to($user->email)->send(new UserRejected($user));

        return back()->with('success', "Account for \"{$user->name}\" rejected.");
    }
}
