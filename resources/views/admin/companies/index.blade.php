@extends('layouts.dashboard')

@section('title', 'Company Requests')

@section('content')
<div class="row">
  <div class="col-12">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Company Registration Requests</h4>
    </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
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
                <th>Company Name</th>
                <th>Status</th>
                <th>Requested By</th>
                <th>Approved / Rejected By</th>
                <th>Submitted</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($companies as $company)
                <tr>
                  <td style="padding-left: 1.25rem;">{{ $loop->iteration + ($companies->currentPage() - 1) * $companies->perPage() }}</td>
                  <td class="fw-semibold">{{ $company->name }}</td>
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
                        <button type="submit" class="btn btn-sm btn-success"
                                onclick="return confirm('Approve {{ $company->name }}?')">
                          Approve
                        </button>
                      </form>
                      <form action="{{ route('admin.companies.reject', $company) }}" method="POST" class="d-inline ms-1">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Reject {{ $company->name }}?')">
                          Reject
                        </button>
                      </form>
                    @else
                      <span class="text-muted small">No actions</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">No company requests found.</td>
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
