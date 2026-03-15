@extends('layouts.dashboard')

@section('title', 'User Management')

@section('content')
<div class="row">
  <div class="col-12">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">User Management</h4>
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

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th style="padding-left: 1.25rem;">#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Company / Unit</th>
                <th>ID Number</th>
                <th>Registered</th>
                <th>Status</th>
                <th>Account</th>
                <th style="padding-left: 1.25rem; padding-right: 1.25rem;">Change Role</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($users as $user)
                @php $role = $user->getRoleNames()->first() ?? '—'; @endphp
                <tr>
                  <td style="padding-left: 1.25rem;">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                  <td class="fw-semibold">{{ $user->name }}</td>
                  <td>{{ $user->email }}</td>
                  <td>
                    @if ($role === 'admin')
                      Admin
                    @elseif ($role === 'officer')
                      Officer
                    @elseif ($role === 'contractor')
                      Contractor
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if ($user->company)
                      {{ $user->company->name }}
                    @elseif ($user->unit)
                      {{ $user->unit->name }}
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>{{ $user->id_number ?? '—' }}</td>
                  <td>{{ $user->created_at->format('d M Y') }}</td>
                  <td>
                    @if ($user->is_suspended)
                      <span class="text-danger small fw-semibold">Suspended</span>
                    @else
                      <span class="text-success small fw-semibold">Active</span>
                    @endif
                  </td>
                  <td>
                    @if ($user->id !== auth()->id())
                      @if ($user->is_suspended)
                        <form action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="d-inline">
                          @csrf
                          <button type="submit" class="btn-action btn-action-green btn-action-sm">Reactivate</button>
                        </form>
                      @else
                        <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Suspend {{ addslashes($user->name) }}? They will be logged out immediately.')">
                          @csrf
                          <button type="submit" class="btn-action btn-action-red btn-action-sm">Suspend</button>
                        </form>
                      @endif
                    @else
                      <span class="text-muted small">—</span>
                    @endif
                  </td>
                  @if ($role !== 'contractor')
                    <td style="padding-left: 1.25rem; padding-right: 1.25rem;">
                      {{-- d-inline (display:inline) makes the form inline-level so the
                           td's vertical-align:middle centres it — same pattern used on
                           the approvals page. --}}
                      <form action="{{ route('admin.users.update-role', $user) }}" method="POST"
                            class="d-inline" style="margin: 0;">
                        @csrf
                        <select name="role" class="form-select form-select-sm"
                                style="width: 7rem; vertical-align: middle;">
                          <option value="officer" {{ $role === 'officer' ? 'selected' : '' }}>Officer</option>
                          <option value="admin"   {{ $role === 'admin'   ? 'selected' : '' }}>Admin</option>
                        </select>
                        <button type="submit" class="btn-action btn-action-sm"
                                style="width: 7rem; vertical-align: middle;">Save</button>
                      </form>
                    </td>
                  @else
                    <td style="padding-left: 1.25rem; padding-right: 1.25rem;"><span class="text-muted small">Fixed</span></td>
                  @endif
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="text-center text-muted py-4">No users found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Pagination --}}
    @if ($users->hasPages())
      <div class="mt-3">
        {{ $users->links() }}
      </div>
    @endif

  </div>
</div>
@endsection
