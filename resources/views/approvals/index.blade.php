@extends('layouts.dashboard')

@section('title', 'Pending Account Approvals')

@section('content')

<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Pending Account Approvals</h4>
          <span class="badge" style="background:#07326A; color:#fff; font-size:0.85rem; padding:6px 14px;">
            {{ $pendingUsers->count() }} pending
          </span>
        </div>

        @if (session('success'))
          <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif

        @if ($pendingUsers->isEmpty())
          <p class="text-muted">No pending registrations at this time.</p>
        @else
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role Type</th>
                  <th>Company / Unit</th>
                  <th>ID Number</th>
                  <th>Registered</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($pendingUsers as $pendingUser)
                  <tr>
                    <td>{{ $pendingUser->name }}</td>
                    <td>{{ $pendingUser->email }}</td>
                    <td>
                      @if ($pendingUser->unit_id)
                        Officer
                      @else
                        Contractor
                      @endif
                    </td>
                    <td>
                      @if ($pendingUser->unit)
                        {{ $pendingUser->unit->name }}
                      @elseif ($pendingUser->company)
                        {{ $pendingUser->company->name }}
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td>{{ $pendingUser->id_number ?? '—' }}</td>
                    <td>{{ $pendingUser->created_at->format('d M Y') }}</td>
                    <td>
                      <form action="{{ route('approvals.approve', $pendingUser) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-action btn-action-green">
                          Approve
                        </button>
                      </form>
                      <form action="{{ route('approvals.reject', $pendingUser) }}" method="POST" class="d-inline ms-1">
                        @csrf
                        <button type="submit" class="btn-action btn-action-red"
                                onclick="return confirm('Reject account for {{ addslashes($pendingUser->name) }}?')">
                          Reject
                        </button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif

      </div>
    </div>
  </div>
</div>

@endsection
