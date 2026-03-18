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
          @role('contractor')
            <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
              + Register New Project
            </a>
          @endrole
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
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed"   {{ request('status') === 'completed'   ? 'selected' : '' }}>Completed</option>
                <option value="cancelled"   {{ request('status') === 'cancelled'   ? 'selected' : '' }}>Cancelled</option>
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
          <table class="table table-hover w-100" style="font-size: 0.875rem;">
            <thead>
              <tr>
                <th style="width: 10%;">Ref No</th>
                <th style="width: 18%;">Description</th>
                @role('admin|officer')
                  <th style="width: 11%;">Company</th>
                @endrole
                <th style="width: 9%;">Status</th>
                <th style="width: 14%;">Progress</th>
                <th>Next Step</th>
                <th style="width: 8%; text-align: center;">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($projects as $project)
                @php
                  $tl    = $timelineData[$project->id];
                  $count = $tl['count'];
                  $pct   = round(($count / 13) * 100);

                  // Derive the single display status from the two DB columns.
                  if ($project->application_status === 'cancelled') {
                      $displayStatus = 'cancelled';
                  } elseif ($project->status === 'completed') {
                      $displayStatus = 'completed';
                  } else {
                      $displayStatus = 'in_progress';
                  }

                  // Colour of the progress bar based on display status.
                  $barClass = match($displayStatus) {
                      'completed'  => 'bg-success',
                      'cancelled'  => 'bg-danger',
                      default      => 'bg-primary',
                  };
                @endphp
                <tr>
                  {{-- Ref No --}}
                  <td style="white-space: nowrap;">{{ $project->ref_no ?? '—' }}</td>

                  {{-- Description — truncated with tooltip --}}
                  <td style="max-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                      title="{{ $project->project_desc }}">
                    {{ $project->project_desc }}
                  </td>

                  {{-- Company (admin/officer only) --}}
                  @role('admin|officer')
                    <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 0;">
                      {{ $project->company->name ?? '—' }}
                    </td>
                  @endrole

                  {{-- Status badge --}}
                  <td>
                    @if ($displayStatus === 'completed')
                      <span class="badge bg-success">Completed</span>
                    @elseif ($displayStatus === 'cancelled')
                      <span class="badge bg-danger">Cancelled</span>
                    @else
                      <span class="badge bg-primary">In Progress</span>
                    @endif
                  </td>

                  {{-- Progress bar + count --}}
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="progress flex-grow-1" style="height: 8px; min-width: 60px;">
                        <div class="progress-bar {{ $barClass }}"
                             role="progressbar"
                             style="width: {{ $pct }}%"
                             aria-valuenow="{{ $count }}"
                             aria-valuemin="0"
                             aria-valuemax="13">
                        </div>
                      </div>
                      <span class="text-muted" style="white-space: nowrap; font-size: 0.78rem;">{{ $count }}/13</span>
                    </div>
                  </td>

                  {{-- Next step label --}}
                  <td>
                    @if ($displayStatus === 'cancelled')
                      <span class="text-danger" style="font-size: 0.78rem; font-style: italic;">— Project Cancelled —</span>
                    @elseif ($displayStatus === 'completed')
                      <span class="text-success" style="font-size: 0.78rem; font-style: italic;">— All Steps Completed —</span>
                    @else
                      <span style="font-size: 0.78rem; font-family: monospace;">{{ $tl['nextStep'] }}</span>
                    @endif
                  </td>

                  {{-- Action --}}
                  <td style="text-align: center;">
                    <a href="{{ route('projects.show', $project) }}"
                       class="badge"
                       style="background-color: #064089; color: #E0E1DD; padding: calc(0.4em + 5px) 0.75em; font-size: 0.75rem; font-weight: 500; text-decoration: none; border-radius: 0;">
                      View
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
