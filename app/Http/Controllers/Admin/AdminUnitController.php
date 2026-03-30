<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

// Admin-only: view, add, rename, and delete units (e.g. ND TRG, ND KEL, ND PHG).
// Stored in the database so admin can add new regions without any code changes.
class AdminUnitController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $units = Unit::withCount('users')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return view('admin.units.index', compact('units', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name'],
        ]);

        Unit::create(['name' => $request->name]);

        return back()->with('success', "Unit \"{$request->name}\" added.");
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:units,name,' . $unit->id],
        ]);

        $unit->update(['name' => $request->name]);

        return back()->with('success', "Unit renamed to \"{$request->name}\".");
    }

    public function destroy(Unit $unit)
    {
        // Nullify officer references before deleting (unit_id is nullable FK).
        $unit->users()->update(['unit_id' => null]);
        $unit->delete();

        return back()->with('success', 'Unit deleted.');
    }
}
