<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

// Admin-only: view all company registration requests and approve or reject them.
// Approved companies appear in the contractor registration dropdown.
class AdminCompanyController extends Controller
{
    public function index()
    {
        $companies = Company::with(['requestedBy', 'approvedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.companies.index', compact('companies'));
    }

    public function approve(Company $company)
    {
        $company->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', "Company \"{$company->name}\" approved.");
    }

    public function reject(Company $company)
    {
        $company->update([
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', "Company \"{$company->name}\" rejected.");
    }
}
