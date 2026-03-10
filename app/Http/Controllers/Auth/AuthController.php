<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Company;
use App\Models\Unit;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Handles showing and processing login and registration forms.
// Registration business logic (role assignment) is delegated to AuthService.
class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        // Pass approved companies and units to populate the register form dropdowns.
        $companies = Company::where('status', 'approved')->orderBy('name')->get();
        $units     = Unit::orderBy('name')->get();

        return view('auth.register', compact('companies', 'units'));
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->registerUser($request->validated());

        Auth::login($user);

        return redirect()->route('projects.index');
    }

    public function showRegisterCompany()
    {
        return view('auth.register-company');
    }

    public function registerCompany(Request $request)
    {
        // Validate and store the company registration request.
        // Admin will review and approve/reject from the admin panel.
        $request->validate([
            'company_name'    => ['required', 'string', 'max:255', 'unique:companies,name'],
            'requester_name'  => ['required', 'string', 'max:255'],
            'requester_email' => ['required', 'email', 'max:255'],
        ]);

        // requested_by is left null — requester has no account yet.
        Company::create([
            'name'   => $request->company_name,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your company registration request has been submitted. Admin will review it shortly.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('projects.index'));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
