<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

// Admin-only: view and add units (e.g. ND TRG, ND KEL, ND PHG).
// Stored in the database so admin can add new regions without any code changes.
class AdminUnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount('users')->orderBy('name')->get();

        return view('admin.units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name'],
        ]);

        Unit::create(['name' => $request->name]);

        return back()->with('success', "Unit \"{$request->name}\" added.");
    }
}
