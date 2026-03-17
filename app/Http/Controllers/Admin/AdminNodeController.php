<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use Illuminate\Http\Request;

// Admin-only: view and add TM nodes (e.g. KT, KBR, TRG).
// Stored in the database so admin can add new nodes without any code changes.
// Nodes are referenced by projects.node_id and searchable by acronym or full_name.
class AdminNodeController extends Controller
{
    public function index()
    {
        $nodes = Node::orderBy('acronym')->get();

        return view('admin.nodes.index', compact('nodes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'acronym'   => ['required', 'string', 'max:50', 'unique:nodes,acronym'],
            'full_name' => ['required', 'string', 'max:255'],
        ]);

        Node::create([
            'acronym'   => strtoupper(trim($request->acronym)),
            'full_name' => trim($request->full_name),
        ]);

        return back()->with('success', "Node \"{$request->acronym}\" added.");
    }

    public function update(Request $request, Node $node)
    {
        $request->validate([
            'acronym'   => ['required', 'string', 'max:50', 'unique:nodes,acronym,' . $node->id],
            'full_name' => ['required', 'string', 'max:255'],
        ]);

        $node->update([
            'acronym'   => strtoupper(trim($request->acronym)),
            'full_name' => trim($request->full_name),
        ]);

        return back()->with('success', "Node updated.");
    }

    public function destroy(Node $node)
    {
        // Nullify project references before deleting (node_id is nullable FK).
        $node->projects()->update(['node_id' => null]);
        $node->delete();

        return back()->with('success', 'Node deleted.');
    }
}
