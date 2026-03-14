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
            </tr>
          </thead>
          <tbody>
            @forelse ($units as $unit)
              <tr>
                <td style="padding-left: 1.25rem;">{{ $loop->iteration }}</td>
                <td class="fw-semibold">{{ $unit->name }}</td>
                <td>{{ $unit->users_count }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted py-4">No units found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection
