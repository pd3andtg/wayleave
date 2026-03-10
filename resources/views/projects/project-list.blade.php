@extends('layouts.dashboard')

@section('title', 'Projects')

@section('content')

<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Project List</h4>
          @role('contractor')
            <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
              + Register New Project
            </a>
          @endrole
        </div>

        {{-- Search & Filter --}}
        <form method="GET" action="{{ route('projects.index') }}" class="mb-4">
          <div class="row g-2">
            <div class="col-md-5">
              <input type="text"
                     name="search"
                     class="form-control"
                     placeholder="Search Ref No or Project Description"
                     value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
              <select name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="outstanding" {{ request('status') === 'outstanding' ? 'selected' : '' }}>Outstanding</option>
                <option value="completed"   {{ request('status') === 'completed'   ? 'selected' : '' }}>Completed</option>
              </select>
            </div>
            <div class="col-md-3">
              <select name="nd_state" class="form-control">
                <option value="">All ND States</option>
                <option value="ND_TRG" {{ request('nd_state') === 'ND_TRG' ? 'selected' : '' }}>ND TRG</option>
                <option value="ND_PHG" {{ request('nd_state') === 'ND_PHG' ? 'selected' : '' }}>ND PHG</option>
                <option value="ND_KEL" {{ request('nd_state') === 'ND_KEL' ? 'selected' : '' }}>ND KEL</option>
              </select>
            </div>
            <div class="col-md-1">
              <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
          </div>
        </form>

        {{-- Success message --}}
        @if (session('success'))
          <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif

        {{-- Table --}}
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Ref No</th>
                <th>Project Description</th>
                <th>ND State</th>
                @role('admin|officer')
                  <th>Company</th>
                @endrole
                <th>Status</th>
                <th>Registered</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($projects as $project)
                <tr>
                  <td>{{ $project->ref_no ?? '—' }}</td>
                  <td>{{ $project->project_desc }}</td>
                  <td>{{ str_replace('_', ' ', $project->nd_state) }}</td>
                  @role('admin|officer')
                    <td>{{ $project->company->name ?? '—' }}</td>
                  @endrole
                  <td>
                    @if ($project->status === 'completed')
                      <span class="badge bg-success">Completed</span>
                    @else
                      <span class="badge bg-warning text-dark">Outstanding</span>
                    @endif
                  </td>
                  <td>{{ $project->created_at->format('d M Y') }}</td>
                  <td>
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary btn-sm">View</a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">No projects found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
          {{ $projects->links() }}
        </div>

      </div>
    </div>
  </div>
</div>

@endsection
