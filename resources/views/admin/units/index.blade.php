@extends('layouts.dashboard')

@section('title', 'Unit Management')

@section('content')
<div class="row">
  <div class="col-lg-8">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Unit Management</h4>
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

    {{-- Add new unit form --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="fw-semibold mb-0">Add New Unit</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.units.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="name" class="form-label">Unit Name</label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" placeholder="e.g. ND JHR" required>
          </div>
          <div class="text-center">
            <button type="submit" class="btn-action">Add Unit</button>
          </div>
          @error('name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </form>
      </div>
    </div>

    {{-- Existing units list --}}
    <div class="card">
      <div class="card-header">
        <h5 class="fw-semibold mb-0">Existing Units</h5>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th style="padding-left: 1.25rem; font-weight: 600; color: #07326A;">#</th>
              <th style="font-weight: 600; color: #07326A;">Unit Name</th>
              <th style="font-weight: 600; color: #07326A;">Officers in Unit</th>
              <th style="font-weight: 600; color: #07326A;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($units as $unit)
              <tr x-data="{ editing: false }">
                <td style="padding-left: 1.25rem;">{{ $loop->iteration }}</td>
                <td class="fw-semibold">
                  <span x-show="!editing">{{ $unit->name }}</span>
                  <span x-show="editing" x-cloak>
                    <form id="unit-edit-{{ $unit->id }}" action="{{ route('admin.units.update', $unit) }}" method="POST">
                      @csrf @method('PATCH')
                      <input type="text" name="name" value="{{ $unit->name }}" class="form-control form-control-sm d-inline" style="width:12rem;" required>
                    </form>
                  </span>
                </td>
                <td>{{ $unit->users_count }}</td>
                <td>
                  <span x-show="!editing">
                    <button type="button" class="btn-action btn-action-sm" x-on:click="editing = true">Edit</button>
                    <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="d-inline ms-1"
                          onsubmit="return confirm('Delete unit {{ addslashes($unit->name) }}? Officers in this unit will have their unit cleared.')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-action btn-action-red btn-action-sm">Delete</button>
                    </form>
                  </span>
                  <span x-show="editing" x-cloak>
                    <button type="submit" form="unit-edit-{{ $unit->id }}" class="btn-action btn-action-sm">Save</button>
                    <button type="button" class="btn-action btn-action-sm ms-1" x-on:click="editing = false">Cancel</button>
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">No units found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection
