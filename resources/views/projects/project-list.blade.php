@extends('layouts.dashboard')

@section('title', 'Projects')

@section('content')

<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Project List</h4>
          <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
            + Register New Project
          </a>
        </div>

        {{-- Search & Filter — dropdowns auto-submit on change; search submits on Enter --}}
        <form method="GET" action="{{ route('projects.index') }}" id="filter-form">
          <div class="row g-2 mb-4">
            <div class="col">
              <input type="text"
                     name="search"
                     class="form-control"
                     placeholder="Search Ref No or Project Description"
                     value="{{ request('search') }}"
                     style="height: 52px;">
            </div>
            <div class="col-auto">
              <select name="status" class="form-control" onchange="document.getElementById('filter-form').submit()"
                      style="height: 52px;">
                <option value="">All Statuses</option>
                <option value="outstanding" {{ request('status') === 'outstanding' ? 'selected' : '' }}>Outstanding</option>
                <option value="completed"   {{ request('status') === 'completed'   ? 'selected' : '' }}>Completed</option>
              </select>
            </div>
            <div class="col-auto">
              <select name="nd_state" class="form-control" onchange="document.getElementById('filter-form').submit()"
                      style="height: 52px;">
                <option value="">All ND States</option>
                <option value="ND_TRG" {{ request('nd_state') === 'ND_TRG' ? 'selected' : '' }}>ND TRG</option>
                <option value="ND_PHG" {{ request('nd_state') === 'ND_PHG' ? 'selected' : '' }}>ND PHG</option>
                <option value="ND_KEL" {{ request('nd_state') === 'ND_KEL' ? 'selected' : '' }}>ND KEL</option>
              </select>
            </div>
          </div>
        </form>

        {{-- Success message --}}
        @if (session('success'))
          <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif

        {{-- Table --}}
        <div class="table-responsive">
          <table class="table table-hover w-100">
            <thead>
              <tr>
                <th style="width: 13%;">Ref No</th>
                <th>Project Description</th>
                <th style="width: 9%;">ND State</th>
                @role('admin|officer')
                  <th style="width: 14%;">Company</th>
                @endrole
                <th style="width: 10%;">Status</th>
                <th style="width: 11%;">Registered</th>
                <th style="width: 10%; text-align: center;"></th>
              </tr>
            </thead>
            <tbody>
              @forelse ($projects as $project)
                <tr>
                  <td>{{ $project->ref_no ?? '—' }}</td>
                  <td style="max-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $project->project_desc }}</td>
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
                  <td style="white-space: nowrap;">{{ $project->created_at->format('d M Y') }}</td>
                  <td style="text-align: center;">
                    <a href="{{ route('projects.show', $project) }}" class="badge" style="background-color: #064089; color: #E0E1DD; padding: calc(0.4em + 5px) 0.75em; font-size: 0.75rem; font-weight: 500; text-decoration: none; border-radius: 0;">
                      View Project
                    </a>
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
