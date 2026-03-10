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
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Company / Unit</th>
                <th>ID Number</th>
                <th>Registered</th>
                <th>Change Role</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($users as $user)
                @php $role = $user->getRoleNames()->first() ?? '—'; @endphp
                <tr>
                  <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                  <td class="fw-semibold">{{ $user->name }}</td>
                  <td>{{ $user->email }}</td>
                  <td>
                    @if ($role === 'admin')
                      <span class="badge bg-dark">Admin</span>
                    @elseif ($role === 'officer')
                      <span class="badge bg-primary">Officer</span>
                    @elseif ($role === 'contractor')
                      <span class="badge bg-secondary">Contractor</span>
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
                    {{-- Only allow role changes for officers and admins — not contractors --}}
                    @if ($role !== 'contractor')
                      <form action="{{ route('admin.users.update-role', $user) }}" method="POST"
                            class="d-flex gap-2 align-items-center">
                        @csrf
                        <select name="role" class="form-select form-select-sm" style="width: auto;">
                          <option value="officer" {{ $role === 'officer' ? 'selected' : '' }}>Officer</option>
                          <option value="admin"   {{ $role === 'admin'   ? 'selected' : '' }}>Admin</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                      </form>
                    @else
                      <span class="text-muted small">Fixed</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">No users found.</td>
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
