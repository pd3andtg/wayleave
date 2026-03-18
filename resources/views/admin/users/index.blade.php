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

    {{-- Register new user form --}}
    <div class="card mb-4" x-data="{ open: false, role: 'officer' }">
      <div class="card-header d-flex justify-content-between align-items-center" style="cursor:pointer;" x-on:click="open = !open">
        <h5 class="fw-semibold mb-0">Register New User</h5>
        <span x-text="open ? '▲' : '▼'" class="text-muted small"></span>
      </div>
      <div x-show="open" x-cloak class="card-body">
        <form action="{{ route('admin.users.store') }}" method="POST">
          @csrf
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Password <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Role <span class="text-danger">*</span></label>
              <select name="role" class="form-control" x-model="role" required>
                <option value="officer">Officer</option>
                <option value="contractor">Contractor</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small">ID Number (Staff ID / IC No)</label>
              <input type="text" name="id_number" class="form-control" value="{{ old('id_number') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Contact Number</label>
              <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number') }}">
            </div>
            <div class="col-md-3" x-show="role === 'officer'" x-cloak>
              <label class="form-label small">Unit</label>
              <select name="unit_id" class="form-control">
                <option value="">— Select Unit —</option>
                @foreach(\App\Models\Unit::orderBy('name')->get() as $unit)
                  <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3" x-show="role === 'contractor'" x-cloak>
              <label class="form-label small">Company</label>
              <select name="company_id" class="form-control">
                <option value="">— Select Company —</option>
                @foreach(\App\Models\Company::where('status','approved')->orderBy('name')->get() as $company)
                  <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="mt-3">
            <button type="submit" class="btn-action">Register User</button>
          </div>
        </form>
      </div>
    </div>

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
                <th>Contact No</th>
                <th>Registered</th>
                <th>Actions</th>
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
                    @if ($role === 'admin')      Admin
                    @elseif ($role === 'officer') Officer
                    @elseif ($role === 'contractor') Contractor
                    @else <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    @if ($user->company)    {{ $user->company->name }}
                    @elseif ($user->unit)   {{ $user->unit->name }}
                    @else <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>{{ $user->id_number ?? '—' }}</td>
                  <td>{{ $user->contact_number ?? '—' }}</td>
                  <td>{{ $user->created_at->format('d M Y') }}</td>
                  <td>
                    <button type="button" class="btn-action btn-action-sm"
                            x-data x-on:click="$dispatch('open-edit-user-{{ $user->id }}')">Edit</button>
                    @if ($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline ms-1"
                          onsubmit="return confirm('Delete user {{ addslashes($user->name) }}? This cannot be undone.')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-action btn-action-red btn-action-sm">Delete</button>
                    </form>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">No users found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Edit user modals — no password field (admin edits profile info only) --}}
    @foreach($users as $editUser)
    @php $editRole = $editUser->getRoleNames()->first() ?? 'officer'; @endphp
    <div x-data="{ open: false, role: '{{ $editRole }}' }"
         x-on:open-edit-user-{{ $editUser->id }}.window="open = true">
      <div x-show="open" x-cloak
           style="position:fixed; inset:0; z-index:9999;"
           x-on:click.self="open = false">
        <div class="card" style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); max-width:560px; width:calc(100% - 2rem); z-index:10000; max-height:90vh; overflow-y:auto;" @click.stop>
          <div class="card-body">
            <h5 class="fw-bold mb-3">Edit User: {{ $editUser->name }}</h5>
            <form action="{{ route('admin.users.update', $editUser) }}" method="POST">
              @csrf @method('PATCH')
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label small">Full Name</label>
                  <input type="text" name="name" class="form-control form-control-sm" value="{{ $editUser->name }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Email</label>
                  <input type="email" name="email" class="form-control form-control-sm" value="{{ $editUser->email }}" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small">ID Number</label>
                  <input type="text" name="id_number" class="form-control form-control-sm" value="{{ $editUser->id_number }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Contact Number</label>
                  <input type="text" name="contact_number" class="form-control form-control-sm" value="{{ $editUser->contact_number }}">
                </div>
                @if ($editRole !== 'contractor')
                <div class="col-md-6">
                  <label class="form-label small">Role</label>
                  <select name="role" x-model="role" class="form-control form-control-sm">
                    <option value="officer" {{ $editRole === 'officer' ? 'selected' : '' }}>Officer</option>
                    <option value="admin"   {{ $editRole === 'admin'   ? 'selected' : '' }}>Admin</option>
                  </select>
                </div>
                @endif
                <div class="col-md-6" x-show="role === 'officer'" x-cloak>
                  <label class="form-label small">Unit</label>
                  <select name="unit_id" class="form-control form-control-sm">
                    <option value="">— None —</option>
                    @foreach(\App\Models\Unit::orderBy('name')->get() as $unit)
                      <option value="{{ $unit->id }}" {{ $editUser->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6" x-show="role === 'contractor'" x-cloak>
                  <label class="form-label small">Company</label>
                  <select name="company_id" class="form-control form-control-sm">
                    <option value="">— None —</option>
                    @foreach(\App\Models\Company::where('status','approved')->orderBy('name')->get() as $company)
                      <option value="{{ $company->id }}" {{ $editUser->company_id == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                        x-on:click="open = false">Cancel</button>
                <button type="submit" class="btn-action">Save Changes</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    @endforeach

    {{-- Pagination --}}
    @if ($users->hasPages())
      <div class="mt-3">
        {{ $users->links() }}
      </div>
    @endif

  </div>
</div>
@endsection
