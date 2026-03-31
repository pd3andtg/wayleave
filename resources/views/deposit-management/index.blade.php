@extends('layouts.dashboard')

@section('title', 'Deposit Management')

@section('content')

<div class="row">
  <div class="col-12" style="padding: 2rem;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="card-title mb-0" style="font-weight:600;">Deposit Management</h2>
    </div>

    {{-- Search + Filter --}}
    <form method="GET" action="{{ route('deposit-management.index') }}" id="filter-form">
      <div class="row g-2 mb-4">

        {{-- Search bar (all roles) --}}
        <div class="col">
          <input type="text" autocomplete="off"
                 name="search"
                 class="form-control"
                 placeholder="Search by Ref No or Project Description"
                 value="{{ $filters['search'] ?? '' }}"
                 style="height:52px;"
                 onchange="document.getElementById('filter-form').submit()">
        </div>

        {{-- ND State filter — admin only --}}
        @role('admin')
          <div class="col-auto">
            <select name="nd_state" class="form-select" style="height:52px; min-width:160px;"
                    onchange="document.getElementById('filter-form').submit()">
              <option value="">All ND States</option>
              <option value="ND_TRG" {{ ($filters['nd_state'] ?? '') === 'ND_TRG' ? 'selected' : '' }}>ND TRG</option>
              <option value="ND_PHG" {{ ($filters['nd_state'] ?? '') === 'ND_PHG' ? 'selected' : '' }}>ND PHG</option>
              <option value="ND_KEL" {{ ($filters['nd_state'] ?? '') === 'ND_KEL' ? 'selected' : '' }}>ND KEL</option>
            </select>
          </div>
        @endrole

      </div>
    </form>

    @if (!empty($filters['search']) || !empty($filters['nd_state']))
      <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('deposit-management.index') }}" class="text-muted small">Show All</a>
      </div>
    @endif

    {{-- Flash message --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    {{-- Table --}}
    <div class="table-responsive">
          <table class="table table-hover" style="font-size:0.875rem;">
            <thead>
              <tr>
                <th style="font-weight:600; color:#07326A;">#</th>
                <th style="font-weight:600; color:#07326A; white-space:nowrap;">Ref No</th>
                <th style="font-weight:600; color:#07326A;">Project Description</th>
                <th style="font-weight:600; color:#07326A; white-space:nowrap;">PBT</th>
                <th style="font-weight:600; color:#07326A; white-space:nowrap;">Method of Payment</th>
                <th style="font-weight:600; color:#07326A; white-space:nowrap;">Amount (RM)</th>
                <th style="font-weight:600; color:#07326A; white-space:nowrap;">EDS No</th>
                <th style="font-weight:600; color:#07326A; white-space:nowrap;">Application Date</th>
                <th style="font-weight:600; color:#07326A; white-space:nowrap;">Received / Posted Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($deposits as $deposit)
                <tr>
                  <td class="px-3 py-3" style="color:#6c757d;">
                    {{ $deposits->firstItem() + $loop->index }}
                  </td>
                  <td class="px-3 py-3" style="white-space:nowrap;">
                    @if ($deposit->project)
                      <a href="{{ route('projects.show', $deposit->project) }}#section-7"
                         style="color:#144e90; font-weight:600; text-decoration:none;">
                        {{ $deposit->project->ref_no ?? '—' }}
                      </a>
                    @else
                      —
                    @endif
                  </td>
                  <td class="px-3 py-3" style="max-width:220px; white-space:normal; word-wrap:break-word;">
                    {{ $deposit->project->project_desc ?? '—' }}
                  </td>
                  <td class="px-3 py-3" style="white-space:nowrap;">
                    @if ($deposit->wayleavePhbt)
                      {{ $deposit->wayleavePhbt->pbt_number }}
                      —
                      {{ $deposit->wayleavePhbt->pbt_name === 'Others'
                          ? ($deposit->wayleavePhbt->pbt_name_other ?? 'Others')
                          : $deposit->wayleavePhbt->pbt_name }}
                    @else
                      —
                    @endif
                  </td>
                  <td class="px-3 py-3" style="white-space:nowrap;">
                    @php
                      $methodLabels = ['BG' => 'BG', 'BD_DAP' => 'BD (DAP)', 'EFT_DAP' => 'EFT (DAP)'];
                    @endphp
                    {{ $methodLabels[$deposit->method_of_payment] ?? ($deposit->method_of_payment ?? '—') }}
                  </td>
                  <td class="px-3 py-3" style="white-space:nowrap;">
                    {{ $deposit->amount !== null ? number_format($deposit->amount, 2) : '—' }}
                  </td>
                  <td class="px-3 py-3" style="white-space:nowrap;">
                    {{ $deposit->eds_no ?? '—' }}
                  </td>
                  <td class="px-3 py-3" style="white-space:nowrap;">
                    {{ $deposit->application_date ? $deposit->application_date->format('d M Y') : '—' }}
                  </td>
                  <td class="px-3 py-3" style="white-space:nowrap;">
                    {{ $deposit->received_posted_date ? $deposit->received_posted_date->format('d M Y') : '—' }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center px-3 py-5" style="color:#6c757d;">
                    No deposit records found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
    </div>

    {{-- Pagination --}}
    @if ($deposits->hasPages())
      <div class="mt-3 d-flex justify-content-end">
        {{ $deposits->links() }}
      </div>
    @endif

  </div>
</div>

@endsection
