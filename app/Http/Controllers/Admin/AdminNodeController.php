<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\Unit;
use Illuminate\Http\Request;

// Admin-only: view and add TM nodes (e.g. KT, KBR, TRG).
// Stored in the database so admin can add new nodes without any code changes.
// Nodes are referenced by projects.node_id and searchable by acronym or full_name.
class AdminNodeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $nodes = Node::when($search, fn($q) => $q
                ->where('acronym',   'ilike', "%{$search}%")
                ->orWhere('full_name', 'ilike', "%{$search}%")
                ->orWhere('nd',        'ilike', "%{$search}%")
                ->orWhere('state',     'ilike', "%{$search}%")
            )
            ->orderBy('acronym')
            ->get();

        // Build ND options from units table so new units appear automatically.
        // Units are stored as "ND TRG" — strip the "ND " prefix for the stored value.
        $ndOptions = Unit::orderBy('name')->pluck('name')->mapWithKeys(function ($name) {
            $value = ltrim(str_replace('ND ', '', $name));
            return [$value => $value];
        });

        return view('admin.nodes.index', compact('nodes', 'ndOptions', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'acronym'   => ['required', 'string', 'max:50', 'unique:nodes,acronym'],
            'full_name' => ['required', 'string', 'max:255'],
            'nd'        => ['nullable', 'string', 'max:50'],
            'state'     => ['nullable', 'string', 'max:100'],
        ]);

        Node::create([
            'acronym'   => strtoupper(trim($request->acronym)),
            'full_name' => trim($request->full_name),
            'nd'        => $request->nd    ? strtoupper(trim($request->nd))    : null,
            'state'     => $request->state ? trim($request->state) : null,
        ]);

        return back()->with('success', "Node \"{$request->acronym}\" added.");
    }

    public function update(Request $request, Node $node)
    {
        $request->validate([
            'acronym'   => ['required', 'string', 'max:50', 'unique:nodes,acronym,' . $node->id],
            'full_name' => ['required', 'string', 'max:255'],
            'nd'        => ['nullable', 'string', 'max:50'],
            'state'     => ['nullable', 'string', 'max:100'],
        ]);

        $node->update([
            'acronym'   => strtoupper(trim($request->acronym)),
            'full_name' => trim($request->full_name),
            'nd'        => $request->nd    ? strtoupper(trim($request->nd))    : null,
            'state'     => $request->state ? trim($request->state) : null,
        ]);

        return back()->with('success', "Node updated.");
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'nodes'               => ['required', 'array', 'min:1'],
            'nodes.*.acronym'     => ['required', 'string', 'max:50'],
            'nodes.*.full_name'   => ['required', 'string', 'max:255'],
            'nodes.*.nd'          => ['nullable', 'string', 'max:50'],
            'nodes.*.state'       => ['nullable', 'string', 'max:100'],
        ]);

        $added = 0;
        $skipped = 0;

        foreach ($request->nodes as $row) {
            $acronym = strtoupper(trim($row['acronym']));
            if (Node::where('acronym', $acronym)->exists()) {
                $skipped++;
                continue;
            }
            Node::create([
                'acronym'   => $acronym,
                'full_name' => trim($row['full_name']),
                'nd'        => !empty($row['nd'])    ? strtoupper(trim($row['nd']))    : null,
                'state'     => !empty($row['state']) ? trim($row['state'])             : null,
            ]);
            $added++;
        }

        $msg = "{$added} node(s) added.";
        if ($skipped) $msg .= " {$skipped} skipped (duplicate acronym).";

        return back()->with('success', $msg);
    }

    public function destroy(Node $node)
    {
        // Nullify project references before deleting (node_id is nullable FK).
        $node->projects()->update(['node_id' => null]);
        $node->delete();

        return back()->with('success', 'Node deleted.');
    }
}
