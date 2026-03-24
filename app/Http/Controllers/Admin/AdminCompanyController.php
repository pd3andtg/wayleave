<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CompanyApproved;
use App\Mail\CompanyRejected;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

// Admin-only: view all company registration requests and approve or reject them.
// Approved companies appear in the contractor registration dropdown.
class AdminCompanyController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $companies = Company::with(['requestedBy', 'approvedBy'])
            ->when($search, fn($q) => $q->where('name', 'ilike', "%{$search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.companies.index', compact('companies', 'search'));
    }

    // Admin registers a company directly (status = approved immediately, no request needed).
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:companies,name'],
        ]);

        Company::create([
            'name'        => $request->name,
            'status'      => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', "Company \"{$request->name}\" registered and approved.");
    }

    public function approve(Company $company)
    {
        $company->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // Email the requester if we have their address on record.
        if ($company->requester_email) {
            Mail::to($company->requester_email)->send(new CompanyApproved($company));
        }

        return back()->with('success', "Company \"{$company->name}\" approved.");
    }

    public function reject(Company $company)
    {
        $company->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        // Email the requester if we have their address on record.
        if ($company->requester_email) {
            Mail::to($company->requester_email)->send(new CompanyRejected($company));
        }

        return back()->with('success', "Company \"{$company->name}\" rejected.");
    }

    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:companies,name,' . $company->id],
        ]);

        $company->update(['name' => $request->name]);

        return back()->with('success', "Company name updated to \"{$request->name}\".");
    }

    public function destroy(Company $company)
    {
        $name = $company->name;

        // Detach all users from this company before deleting so the FK doesn't block deletion.
        // Affected users will have a null company_id — admin should reassign or suspend them.
        $company->users()->update(['company_id' => null]);

        $company->delete();

        return back()->with('success', "Company \"{$name}\" has been deleted.");
    }
}
