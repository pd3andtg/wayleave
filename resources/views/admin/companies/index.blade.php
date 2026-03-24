@extends('layouts.dashboard')

@section('title', 'Company Requests')

@section('content')
<div class="row">
  <div class="col-12">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Company Registration and Management</h4>
    </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    {{-- Admin directly registers a company (auto-approved) --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="fw-semibold mb-0">Register New Company</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('admin.companies.store') }}" method="POST">
          @csrf
          <div class="mb-3" style="max-width:360px;">
            <label class="form-label small mb-1">Company Name <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. ABC Sdn Bhd" required>
          </div>
          <button type="submit" class="btn-action" style="font-weight:400; text-transform:none; color:#ffffff;">Register Company</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <form method="GET" action="{{ route('admin.companies.index') }}">
          <div class="d-flex align-items-center gap-2" style="max-width:400px;">
            <input type="text" autocomplete="off" name="search" class="form-control form-control-sm" placeholder="Search company name…" value="{{ $search ?? '' }}">
            @if($search)
              <a href="{{ route('admin.companies.index') }}" class="btn-action btn-action-sm" style="font-weight:400; text-transform:none; color:#ffffff; white-space:nowrap;">Clear</a>
            @endif
          </div>
        </form>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th style="padding-left: 1.25rem;">#</th>
                <th>Company Name</th>
                <th>Status</th>
                <th>Requested By</th>
                <th>Approved / Rejected By</th>
                <th>Submitted</th>
                <th>Approval</th>
                <th style="padding-left: 1.25rem; padding-right: 1.25rem;">Manage</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($companies as $company)
                <tr x-data="{ editing: false }">
                  <td style="padding-left: 1.25rem;">{{ $loop->iteration + ($companies->currentPage() - 1) * $companies->perPage() }}</td>
                  <td class="fw-semibold">
                    <span x-show="!editing">{{ $company->name }}</span>
                    <form x-show="editing" x-cloak
                          action="{{ route('admin.companies.update', $company) }}" method="POST">
                      @csrf
                      @method('PATCH')
                      <input type="text" autocomplete="off" name="name" value="{{ $company->name }}"
                             class="form-control form-control-sm d-inline"
                             style="width: 14rem; height: 31px; display: inline-block !important;"
                             required>
                      <button type="submit" class="btn-action btn-action-sm ms-1">Save</button>
                      <button type="button" class="btn-action btn-action-sm ms-1"
                              x-on:click="editing = false">Cancel</button>
                    </form>
                  </td>
                  <td>
                    @if ($company->status === 'approved')
                      <span class="badge bg-success">Approved</span>
                    @elseif ($company->status === 'rejected')
                      <span class="badge bg-danger">Rejected</span>
                    @else
                      <span class="badge bg-warning text-dark">Pending</span>
                    @endif
                  </td>
                  <td>{{ $company->requestedBy?->name ?? '—' }}</td>
                  <td>{{ $company->approvedBy?->name ?? '—' }}</td>
                  <td>{{ $company->created_at->format('d M Y') }}</td>
                  <td>
                    @if ($company->status === 'pending')
                      <form action="{{ route('admin.companies.approve', $company) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-action btn-action-green"
                                onclick="return confirm('Approve {{ addslashes($company->name) }}?')">
                          Approve
                        </button>
                      </form>
                      <form action="{{ route('admin.companies.reject', $company) }}" method="POST" class="d-inline ms-1">
                        @csrf
                        <button type="submit" class="btn-action btn-action-red"
                                onclick="return confirm('Reject {{ addslashes($company->name) }}?')">
                          Reject
                        </button>
                      </form>
                    @else
                      <span class="text-muted small">No actions</span>
                    @endif
                  </td>
                  <td style="padding-left: 1.25rem; padding-right: 1.25rem;">
                    <button type="button" class="btn-action btn-action-sm"
                            x-on:click="editing = !editing"
                            x-text="editing ? 'Cancel' : 'Edit'">
                    </button>
                    <form action="{{ route('admin.companies.destroy', $company) }}" method="POST"
                          class="d-inline ms-1"
                          onsubmit="return confirm('Delete {{ addslashes($company->name) }}? This cannot be undone.')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn-action btn-action-red btn-action-sm">Delete</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">No company requests found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Pagination --}}
    @if ($companies->hasPages())
      <div class="mt-3">
        {{ $companies->links() }}
      </div>
    @endif

  </div>
</div>
@endsection
