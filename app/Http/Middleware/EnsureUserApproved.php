<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Blocks access for authenticated users whose account is not yet approved.
// Applied to all auth-protected routes so pending or rejected users cannot
// browse the system after logging in.
// Logout is exempted so users can still sign out without looping.
class EnsureUserApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->status !== 'approved') {
            // Allow the logout route so the user can sign out cleanly.
            if ($request->routeIs('logout')) {
                return $next($request);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->status === 'rejected'
                ? 'Your account has been rejected. Please contact the administrator.'
                : 'Your account is pending approval. You will be notified by email once it is reviewed.';

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
