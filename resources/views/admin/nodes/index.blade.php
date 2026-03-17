@extends('layouts.dashboard')

@section('title', 'Node Management')

@section('content')
<div class="row">
  <div class="col-lg-8">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Node Management</h4>
    </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    {{-- Add new node form --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="fw-semibold mb-0">Add New Node</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.nodes.store') }}" method="POST">
          @csrf
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="acronym" class="form-label">Acronym <span class="text-danger">*</span></label>
              <input type="text" id="acronym" name="acronym"
                     class="form-control @error('acronym') is-invalid @enderror"
                     value="{{ old('acronym') }}" placeholder="e.g. KT" required>
              @error('acronym')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-8">
              <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" id="full_name" name="full_name"
                     class="form-control @error('full_name') is-invalid @enderror"
                     value="{{ old('full_name') }}" placeholder="e.g. Kota Tinggi" required>
              @error('full_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="text-center">
            <button type="submit" class="btn-action">Add Node</button>
          </div>
        </form>
      </div>
    </div>

    {{-- Existing nodes list --}}
    <div class="card">
      <div class="card-header">
        <h5 class="fw-semibold mb-0">Existing Nodes</h5>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th style="padding-left: 1.25rem; font-weight: 600; color: #07326A;">#</th>
              <th style="font-weight: 600; color: #07326A;">Acronym</th>
              <th style="font-weight: 600; color: #07326A;">Full Name</th>
              <th style="font-weight: 600; color: #07326A;">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($nodes as $node)
              <tr x-data="{ editing: false }">
                <td style="padding-left: 1.25rem;">{{ $loop->iteration }}</td>
                <td class="fw-semibold">
                  <span x-show="!editing">{{ $node->acronym }}</span>
                  <span x-show="editing" x-cloak>
                    <form id="node-edit-{{ $node->id }}" action="{{ route('admin.nodes.update', $node) }}" method="POST">
                      @csrf @method('PATCH')
                      <input type="text" name="acronym" value="{{ $node->acronym }}" class="form-control form-control-sm d-inline" style="width:5rem;" required>
                    </form>
                  </span>
                </td>
                <td>
                  <span x-show="!editing">{{ $node->full_name }}</span>
                  <span x-show="editing" x-cloak>
                    <input type="text" name="full_name" form="node-edit-{{ $node->id }}" value="{{ $node->full_name }}" class="form-control form-control-sm d-inline" style="width:12rem;" required>
                  </span>
                </td>
                <td>
                  <span x-show="!editing">
                    <button type="button" class="btn-action btn-action-sm" x-on:click="editing = true">Edit</button>
                    <form action="{{ route('admin.nodes.destroy', $node) }}" method="POST" class="d-inline ms-1"
                          onsubmit="return confirm('Delete node {{ $node->acronym }}? Projects referencing it will have node cleared.')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn-action btn-action-red btn-action-sm">Delete</button>
                    </form>
                  </span>
                  <span x-show="editing" x-cloak>
                    <button type="submit" form="node-edit-{{ $node->id }}" class="btn-action btn-action-sm">Save</button>
                    <button type="button" class="btn-action btn-action-sm ms-1" x-on:click="editing = false">Cancel</button>
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">No nodes found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection
