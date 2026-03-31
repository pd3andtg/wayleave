@extends('layouts.dashboard')

@section('title', 'Deposit Management')

@section('content')

<div class="row">
  <div class="col-12" style="padding: 2rem;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="card-title mb-0" style="font-weight:600;">Deposit Management</h2>
    </div>


    {{-- Method summary cards --}}
    @php
      $activeMethod = $filters['method'] ?? '';
      $methodMeta = [
          'BG'      => ['label' => 'BG',       'color' => '#07326A'],
          'BD_DAP'  => ['label' => 'BD (DAP)',  'color' => '#145a32'],
          'EFT_DAP' => ['label' => 'EFT (DAP)', 'color' => '#6c3483'],
      ];
    @endphp
    <div class="d-flex gap-3 mb-4">
      @foreach ($methodMeta as $key => $meta)
        @php
          $cardQuery = array_merge($filters, ['method' => ($activeMethod === $key) ? '' : $key]);
          $cardUrl   = route('deposit-management.index', array_filter($cardQuery, fn($v) => $v !== ''));
        @endphp
        <a href="{{ $cardUrl }}" class="text-decoration-none flex-fill">
          <div class="card mb-0" style="border-left: 4px solid {{ $meta['color'] }}; {{ $activeMethod === $key ? 'background:#f8f9fa;' : '' }}">
            <div class="card-body py-3">
              <div class="text-muted small">{{ $meta['label'] }}</div>
              <div style="font-size:1.6rem; font-weight:700; color:{{ $meta['color'] }}; line-height:1.2;">{{ $totals[$key]['count'] }}</div>
              <div style="font-size:1rem; color:{{ $meta['color'] }}; font-weight:500;">RM {{ number_format($totals[$key]['amount'], 2) }}</div>
            </div>
          </div>
        </a>
      @endforeach
    </div>

    {{-- Search + Filter --}}
    <form method="GET" action="{{ route('deposit-management.index') }}" id="filter-form">
      <div class="row g-2 mb-4">
        <div class="col">
          <input type="text" autocomplete="off"
                 name="search"
                 class="form-control"
                 placeholder="Search by Ref No or Project Description"
                 value="{{ $filters['search'] ?? '' }}"
                 style="height:52px;"
                 onchange="document.getElementById('filter-form').submit()">
        </div>
        <div class="col-auto">
          <select name="project_status" class="form-select" style="height:52px; min-width:160px;"
                  onchange="document.getElementById('filter-form').submit()">
            <option value="">All Statuses</option>
            <option value="in_progress" {{ ($filters['project_status'] ?? '') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="completed"   {{ ($filters['project_status'] ?? '') === 'completed'   ? 'selected' : '' }}>Completed</option>
            <option value="cancelled"   {{ ($filters['project_status'] ?? '') === 'cancelled'   ? 'selected' : '' }}>Cancelled</option>
          </select>
        </div>
        {{-- Method filter preserved from card click --}}
        @if (!empty($activeMethod))
          <input type="hidden" name="method" value="{{ $activeMethod }}">
        @endif
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

    {{-- Results + Export + Clear --}}
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="text-muted small">
        @if ($deposits->total() > 0)
          Showing {{ $deposits->firstItem() }} to {{ $deposits->lastItem() }} of {{ $deposits->total() }} results
        @else
          No deposit records found.
        @endif
      </div>
      <div class="d-flex align-items-center gap-3">
        @if ($deposits->total() > 0)
          <a href="{{ route('deposit-management.export', array_filter($filters, fn($v) => $v !== '')) }}"
             class="btn-action btn-action-sm" style="white-space:nowrap;">
            Export CSV
          </a>
        @endif
        @if (!empty($filters['search']) || !empty($filters['nd_state']) || !empty($filters['method']) || !empty($filters['project_status']))
          <a href="{{ route('deposit-management.index') }}" class="text-muted small">Show All</a>
        @endif
      </div>
    </div>

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
                <th style="font-weight:600; color:#07326A; white-space:nowrap;">Project Status</th>
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
                  <td class="px-3 py-3" style="white-space:nowrap;">
                    @php
                      $proj = $deposit->project;
                      if (!$proj) {
                          $pStatus = null;
                      } elseif ($proj->application_status === 'cancelled') {
                          $pStatus = 'cancelled';
                      } elseif ($proj->status === 'completed') {
                          $pStatus = 'completed';
                      } else {
                          $pStatus = 'in_progress';
                      }
                    @endphp
                    @if ($pStatus === 'completed')
                      <span class="badge bg-success">Completed</span>
                    @elseif ($pStatus === 'cancelled')
                      <span class="badge bg-danger">Cancelled</span>
                    @elseif ($pStatus === 'in_progress')
                      <span class="badge bg-primary">In Progress</span>
                    @else
                      —
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="10" class="text-center px-3 py-5" style="color:#6c757d;">
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
