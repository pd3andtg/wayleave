@extends('layouts.dashboard')

@section('title', 'Project Detail')

@section('content')

@php
  $cancelled = $project->isCancelled();
  $boqHidden = $project->isBoqHidden();
@endphp

{{-- ================================================================ --}}
{{-- PROJECT HEADER                                                    --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <h4 class="mb-1">{{ $project->project_desc }}</h4>
            <div class="text-muted" style="font-size:0.85rem;">
              <span class="me-3">Ref No: <strong>{{ $project->ref_no ?? '—' }}</strong></span>
              <span class="me-3">LOR: <strong>{{ $project->lor_no ?? '—' }}</strong></span>
              <span class="me-3">Project No: <strong>{{ $project->project_no ?? '—' }}</strong></span>
              <span class="me-3">ND State: <strong>{{ str_replace('_', ' ', $project->nd_state) }}</strong></span>
              @role('officer|admin')
                <span>Company: <strong>{{ $project->company->name }}</strong></span>
              @endrole
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            @if ($cancelled)
              <span class="badge bg-danger" style="font-size:0.75rem; padding:0.4rem 0.75rem;">Cancelled</span>
            @elseif ($project->status === 'completed')
              <span class="badge bg-success" style="font-size:0.75rem; padding:0.4rem 0.75rem;">Completed</span>
            @else
              <span class="badge bg-warning text-dark" style="font-size:0.75rem; padding:0.4rem 0.75rem;">Outstanding</span>
            @endif
            <a href="{{ route('projects.index') }}" class="btn-action">&larr; Back to Dashboard</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Flash messages --}}
@if (session('success'))
  <div class="alert alert-success py-2">{{ session('success') }}</div>
@endif
@if (session('error'))
  <div class="alert alert-danger py-2">{{ session('error') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-danger py-2">
    @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
  </div>
@endif

{{-- ================================================================ --}}
{{-- SECTION 1: PROJECT INFORMATION (Editable)                        --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">1</span> Project Information</h3>

        @if ($cancelled)
          <div class="alert alert-danger mb-3">
            <strong>Project Cancelled.</strong> {{ $project->cancellation_reason }}
          </div>
        @endif

        <div x-data="{ editing: false }">

          {{-- Read-only view --}}
          <div x-show="!editing">
            <div class="row">
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">KUTT Ref No / PBT Ref No</div>
                <div>{{ $project->ref_no ?? '—' }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">LOR No</div>
                <div>{{ $project->lor_no ?? '—' }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">Project No</div>
                <div>{{ $project->project_no ?? '—' }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">Project Description</div>
                <div>{{ $project->project_desc }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">ND State</div>
                <div>{{ str_replace('_', ' ', $project->nd_state) }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">TM Node</div>
                <div>{{ $project->node ? $project->node->acronym . ' — ' . $project->node->full_name : '—' }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">PIC Name</div>
                <div>{{ $project->pic_name }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">Payment to KUTT</div>
                <div>{{ $project->payment_to_kutt ? ucfirst(str_replace('_', ' ', $project->payment_to_kutt)) : '—' }}</div>
              </div>
              @role('officer|admin')
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">Company</div>
                <div>{{ $project->company->name }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">Self Applied by TM</div>
                <div>{{ $project->self_applied_by_tm ? 'Yes' : 'No' }}</div>
              </div>
              @endrole
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">Remarks</div>
                <div>{{ $project->remarks ?? '—' }}</div>
              </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-2">
              @can('update', $project)
                <button type="button" class="btn-action" x-on:click="editing = true">Edit</button>
              @endcan
              {{-- Cancel button — anyone can cancel --}}
              @unless($cancelled)
                <button type="button" class="btn-action btn-action-red"
                        x-data x-on:click="$dispatch('open-cancel-modal')">Cancel Project</button>
              @endunless
              {{-- Reopen — admin only --}}
              @if($cancelled)
                @can('reopen', $project)
                  <form action="{{ route('projects.reopen', $project) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn-action btn-action-green">Reopen Project</button>
                  </form>
                @endcan
              @endif
            </div>
          </div>

          {{-- Edit form --}}
          @can('update', $project)
          <div x-show="editing" x-cloak
               x-data="{
                 selfApplied: {{ $project->self_applied_by_tm ? 'true' : 'false' }}
               }">
            <form action="{{ route('projects.update', $project) }}" method="POST">
              @csrf @method('PUT')
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">KUTT Ref No / PBT Ref No</label>
                  <input type="text" name="ref_no" class="form-control" value="{{ old('ref_no', $project->ref_no) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">LOR No</label>
                  <input type="text" name="lor_no" class="form-control" value="{{ old('lor_no', $project->lor_no) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Project No</label>
                  <input type="text" name="project_no" class="form-control" value="{{ old('project_no', $project->project_no) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Project Description <span class="text-danger">*</span></label>
                  <textarea name="project_desc" class="form-control" rows="1" required>{{ old('project_desc', $project->project_desc) }}</textarea>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">ND State <span class="text-danger">*</span></label>
                  <select name="nd_state" class="form-control" required>
                    <option value="ND_TRG" {{ old('nd_state', $project->nd_state) === 'ND_TRG' ? 'selected' : '' }}>ND TRG</option>
                    <option value="ND_PHG" {{ old('nd_state', $project->nd_state) === 'ND_PHG' ? 'selected' : '' }}>ND PHG</option>
                    <option value="ND_KEL" {{ old('nd_state', $project->nd_state) === 'ND_KEL' ? 'selected' : '' }}>ND KEL</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">TM Node</label>
                  <select name="node_id" class="form-control">
                    <option value="">-- None --</option>
                    @foreach($nodes as $node)
                      <option value="{{ $node->id }}" {{ old('node_id', $project->node_id) == $node->id ? 'selected' : '' }}>
                        {{ $node->acronym }} — {{ $node->full_name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Payment to KUTT</label>
                  <select name="payment_to_kutt" class="form-control">
                    <option value="">-- Select --</option>
                    <option value="charged"      {{ old('payment_to_kutt', $project->payment_to_kutt) === 'charged'      ? 'selected' : '' }}>Charged</option>
                    <option value="waived"       {{ old('payment_to_kutt', $project->payment_to_kutt) === 'waived'       ? 'selected' : '' }}>Waived</option>
                    <option value="not_required" {{ old('payment_to_kutt', $project->payment_to_kutt) === 'not_required' ? 'selected' : '' }}>Not Required</option>
                  </select>
                </div>
                @role('officer|admin')
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Self Applied by TM</label>
                  <select name="self_applied_by_tm" class="form-control"
                          @change="selfApplied = $event.target.value === '1'">
                    <option value="0" {{ !$project->self_applied_by_tm ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $project->self_applied_by_tm  ? 'selected' : '' }}>Yes</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3" x-show="!selfApplied" x-cloak>
                  <label class="form-label text-muted small">Company</label>
                  <select name="company_id" class="form-control">
                    @foreach($companies as $company)
                      <option value="{{ $company->id }}" {{ old('company_id', $project->company_id) == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                @endrole
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Remarks</label>
                  <textarea name="remarks" class="form-control" rows="1">{{ old('remarks', $project->remarks) }}</textarea>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                        x-on:click="editing = false">Cancel</button>
                <button type="submit" class="btn-action">Save Changes</button>
              </div>
            </form>
          </div>
          @endcan

        </div>
      </div>
    </div>
  </div>
</div>

{{-- Cancel modal --}}
@unless($cancelled)
<div x-data="{ open: false }" x-on:open-cancel-modal.window="open = true">
  <div x-show="open" x-cloak
       style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;">
    <div class="card" style="max-width:480px; width:100%; margin:1rem;" @click.outside="open = false">
      <div class="card-body">
        <h5 class="fw-bold mb-3">Cancel Project</h5>
        <form action="{{ route('projects.cancel', $project) }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
            <textarea name="cancellation_reason" class="form-control" rows="3" required minlength="5"
                      placeholder="State the reason for cancellation"></textarea>
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                    x-on:click="open = false">Cancel</button>
            <button type="submit" class="btn-action btn-action-red">Confirm Cancellation</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endunless

{{-- ================================================================ --}}
{{-- PROJECT TIMELINE (Read-Only)                                      --}}
{{-- ================================================================ --}}
@php
$tl = $timelineStatus;
$tlLabels = [
    1 => 'Project Info', 2 => 'BOQ/INV Filed', 3 => 'BOQ/INV Endorsed',
    4 => 'Wayleave', 5 => 'Wayleave Endorsed', 6 => 'WL Payment',
    7 => 'BG/BD Received', 8 => 'Permit Submitted', 9 => 'Permit Received',
    10 => 'Notis Mula', 11 => 'Notis Siap', 12 => 'CPC Application', 13 => 'CPC Received',
];
@endphp
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">Project Timeline</h3>
        <div class="d-flex align-items-start" style="overflow-x:auto; padding-bottom:0.5rem;">
          @foreach (range(1, 13) as $i)
            <div class="d-flex flex-column align-items-center" style="min-width:64px; flex:1;">
              <div class="d-flex align-items-center w-100">
                @if ($i > 1)
                  <div style="flex:1; height:2px; background:{{ $tl[$i-1] ? '#28a745' : '#dee2e6' }};"></div>
                @else
                  <div style="flex:1;"></div>
                @endif
                <div style="width:28px; height:28px; border-radius:50%; flex-shrink:0;
                            background:{{ $tl[$i] ? '#28a745' : '#dee2e6' }};
                            color:{{ $tl[$i] ? '#fff' : '#6c757d' }};
                            display:flex; align-items:center; justify-content:center;
                            font-size:0.65rem; font-weight:700;">
                  @if ($tl[$i]) &#10003; @else {{ $i }} @endif
                </div>
                @if ($i < 13)
                  <div style="flex:1; height:2px; background:{{ $tl[$i] ? '#28a745' : '#dee2e6' }};"></div>
                @else
                  <div style="flex:1;"></div>
                @endif
              </div>
              <div class="text-center mt-1" style="font-size:0.6rem; color:#6c757d; line-height:1.2;">
                {{ $tlLabels[$i] }}
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

@if($cancelled)
  <div class="alert alert-warning">This project is cancelled. All sections are locked.</div>
@else

{{-- ================================================================ --}}
{{-- SECTION 2: BOQ/INV FILES (Contractor view — no eds_no/status)    --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">2</span> BOQ/INV Files</h3>
        @if($boqHidden)
          <p class="text-muted mb-0"><em>Payment to KUTT is {{ ucfirst(str_replace('_', ' ', $project->payment_to_kutt)) }} — BOQ/INV section not applicable.</em></p>
        @else
          <div class="table-responsive">
            <table class="table table-borderless mb-0">
              <thead>
                <tr>
                  <th style="font-weight:600; color:#07326A;">#</th>
                  <th style="font-weight:600; color:#07326A;">Document Info</th>
                  <th style="font-weight:600; color:#07326A;">Type</th>
                  <th style="font-weight:600; color:#07326A;">Date Received</th>
                  <th style="font-weight:600; color:#07326A;">Amount (RM)</th>
                  <th style="font-weight:600; color:#07326A;">Remarks</th>
                  <th style="font-weight:600; color:#07326A;">Last Updated By</th>
                  <th style="font-weight:600; color:#07326A;">Date Updated</th>
                  <th style="font-weight:600; color:#07326A;">File</th>
                  @can('update', $project) <th style="font-weight:600; color:#07326A;">Action</th> @endcan
                </tr>
              </thead>
              @forelse ($project->boqInvItems as $item)
              <tbody x-data="{ editing: false }">
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $item->document_info }}</td>
                  <td>{{ $item->type }}</td>
                  <td>{{ $item->date_received?->format('d/m/Y') }}</td>
                  <td>{{ $item->amount ? number_format($item->amount, 2) : '—' }}</td>
                  <td>{{ $item->remarks ?? '—' }}</td>
                  <td>{{ $item->updatedBy?->name ?? '—' }}</td>
                  <td>{{ $item->updated_at->format('d/m/Y') }}</td>
                  <td>
                    @if($item->file_path)
                      <a href="{{ route('projects.download', ['project' => $project, 'path' => $item->file_path]) }}"
                         class="btn-action btn-action-sm">Download</a>
                    @else
                      —
                    @endif
                  </td>
                  @can('update', $project)
                  <td>
                    <button type="button" class="btn-action btn-action-sm"
                            x-on:click="editing = !editing">Edit</button>
                    <form action="{{ route('projects.boq-inv-items.destroy', [$project, $item]) }}" method="POST" class="d-inline ms-1"
                          onsubmit="return confirm('Delete this BOQ/INV row? This cannot be undone.')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-action btn-action-red btn-action-sm">Delete</button>
                    </form>
                  </td>
                  @endcan
                </tr>
                @can('update', $project)
                <tr x-show="editing" x-cloak style="background:#f8f9fa;">
                  <td colspan="10" class="py-3 px-3">
                    <form action="{{ route('projects.boq-inv-items.update', [$project, $item]) }}" method="POST" enctype="multipart/form-data">
                      @csrf
                      <div class="row g-2">
                        <div class="col-md-3">
                          <label class="form-label small">Document Info <span class="text-danger">*</span></label>
                          <input type="text" name="document_info" class="form-control form-control-sm" value="{{ $item->document_info }}" required>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small">Type <span class="text-danger">*</span></label>
                          <select name="type" class="form-control form-control-sm" required>
                            <option value="BQ"  {{ $item->type === 'BQ'  ? 'selected' : '' }}>BQ</option>
                            <option value="INV" {{ $item->type === 'INV' ? 'selected' : '' }}>INV</option>
                          </select>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small">Date Received <span class="text-danger">*</span></label>
                          <input type="date" name="date_received" class="form-control form-control-sm" value="{{ $item->date_received?->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small">Amount (RM)</label>
                          <input type="number" name="amount" step="0.01" class="form-control form-control-sm" value="{{ $item->amount }}">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small">Remarks</label>
                          <input type="text" name="remarks" class="form-control form-control-sm" value="{{ $item->remarks }}" placeholder="e.g. BOQ/INV No">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label small">Replace File (PDF, max 10MB)</label>
                          <input type="file" name="file" class="form-control form-control-sm" accept="application/pdf">
                        </div>
                        <div class="col-md-8 d-flex align-items-end gap-2">
                          <button type="submit" class="btn-action btn-action-sm">Save</button>
                          <button type="button" class="btn-action btn-action-sm" style="background:#6c757d; border-color:#6c757d;"
                                  x-on:click="editing = false">Cancel</button>
                        </div>
                      </div>
                    </form>
                  </td>
                </tr>
                @endcan
              </tbody>
              @empty
              <tbody>
                <tr><td colspan="10" class="text-center text-muted py-3">No BOQ/INV items yet.</td></tr>
              </tbody>
              @endforelse
            </table>
          </div>

          {{-- Add New BOQ/INV — visible to contractor, officer, and admin --}}
          @can('update', $project)
          <div class="mt-3" x-data="{ open: false }">
            <button type="button" class="btn-action" x-on:click="open = !open">+ Add New BOQ/INV</button>
            <div x-show="open" x-cloak class="mt-3 p-3 border rounded bg-light">
              <form action="{{ route('projects.boq-inv-items.store', $project) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-2">
                  <div class="col-md-3">
                    <label class="form-label small">Document Info <span class="text-danger">*</span></label>
                    <input type="text" name="document_info" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-control form-control-sm" required>
                      <option value="BQ">BQ</option>
                      <option value="INV">INV</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small">Date Received <span class="text-danger">*</span></label>
                    <input type="date" name="date_received" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small">Amount (RM)</label>
                    <input type="number" name="amount" step="0.01" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small">Remarks</label>
                    <input type="text" name="remarks" class="form-control form-control-sm" placeholder="BOQ/Invoice No">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small">File (PDF, max 10MB)</label>
                    <input type="file" name="file" class="form-control form-control-sm" accept="application/pdf">
                  </div>
                </div>
                <div class="mt-2">
                  <button type="submit" class="btn-action btn-action-sm">Add Row</button>
                </div>
              </form>
            </div>
          </div>
          @endcan
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 3: TM BOQ/INVOICE ENDORSEMENT                            --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">3</span> TM BOQ/Invoice Endorsement</h3>
        @if($boqHidden)
          <p class="text-muted mb-0"><em>Payment to KUTT is {{ ucfirst(str_replace('_', ' ', $project->payment_to_kutt)) }} — BOQ/INV section not applicable.</em></p>
        @else
          <div class="table-responsive">
            <table class="table table-borderless mb-0">
              <thead>
                <tr>
                  <th style="font-weight:600; color:#07326A;">#</th>
                  <th style="font-weight:600; color:#07326A;">Document Info</th>
                  <th style="font-weight:600; color:#07326A;">Type</th>
                  <th style="font-weight:600; color:#07326A;">Date Received</th>
                  <th style="font-weight:600; color:#07326A;">Amount (RM)</th>
                  <th style="font-weight:600; color:#07326A;">EDS No</th>
                  <th style="font-weight:600; color:#07326A;">Payment Status</th>
                  <th style="font-weight:600; color:#07326A;">Endorsed By</th>
                  <th style="font-weight:600; color:#07326A;">Remarks</th>
                  <th style="font-weight:600; color:#07326A;">File</th>
                  @role('officer|admin') <th style="font-weight:600; color:#07326A;">Action</th> @endrole
                </tr>
              </thead>
              @forelse ($project->boqInvItems as $item)
              <tbody x-data="{ editing: false, endorsing: false }">
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $item->document_info }}</td>
                  <td>{{ $item->type }}</td>
                  <td>{{ $item->date_received?->format('d/m/Y') }}</td>
                  <td>{{ $item->amount ? number_format($item->amount, 2) : '—' }}</td>
                  <td>{{ $item->eds_no ?? '—' }}</td>
                  <td>
                    {{ $item->payment_status ? ucfirst(str_replace('_', ' ', $item->payment_status)) : '—' }}
                  </td>
                  <td>{{ $item->endorsedBy?->name ?? '—' }}</td>
                  <td>{{ $item->remarks ?? '—' }}</td>
                  <td>
                    @if($item->file_path)
                      <a href="{{ route('projects.download', ['project' => $project, 'path' => $item->file_path]) }}"
                         class="btn-action btn-action-sm">Download</a>
                    @else
                      —
                    @endif
                  </td>
                  @role('officer|admin')
                  <td>
                    <button type="button" class="btn-action btn-action-sm"
                            x-on:click="editing = !editing; endorsing = false">Edit</button>
                    <button type="button" class="btn-action btn-action-green btn-action-sm ms-1"
                            x-on:click="endorsing = !endorsing; editing = false">Endorse</button>
                    <form action="{{ route('projects.boq-inv-items.destroy', [$project, $item]) }}" method="POST" class="d-inline ms-1"
                          onsubmit="return confirm('Delete this BOQ/INV row? This cannot be undone.')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn-action btn-action-red btn-action-sm">Delete</button>
                    </form>
                  </td>
                  @endrole
                </tr>
                @role('officer|admin')
                {{-- Inline edit row: all Section 3 fields --}}
                <tr x-show="editing" x-cloak style="background:#f8f9fa;">
                  <td colspan="11" class="py-3 px-3">
                    <form action="{{ route('projects.boq-inv-items.update', [$project, $item]) }}" method="POST" enctype="multipart/form-data">
                      @csrf
                      <div class="row g-2">
                        <div class="col-md-4">
                          <label class="form-label small">Document Info</label>
                          <input type="text" name="document_info" class="form-control form-control-sm" value="{{ $item->document_info }}">
                        </div>
                        <div class="col-md-2">
                          <label class="form-label small">Type</label>
                          <select name="type" class="form-control form-control-sm">
                            <option value="BQ"  {{ $item->type === 'BQ'  ? 'selected' : '' }}>BQ</option>
                            <option value="INV" {{ $item->type === 'INV' ? 'selected' : '' }}>INV</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small">Date Received</label>
                          <input type="date" name="date_received" class="form-control form-control-sm" value="{{ $item->date_received?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small">Amount (RM)</label>
                          <input type="number" name="amount" step="0.01" class="form-control form-control-sm" value="{{ $item->amount }}">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label small">EDS No</label>
                          <input type="text" name="eds_no" class="form-control form-control-sm" value="{{ $item->eds_no }}">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label small">Payment Status</label>
                          <select name="payment_status" class="form-control form-control-sm">
                            <option value="">— Select —</option>
                            <option value="pending_endorsement" {{ $item->payment_status === 'pending_endorsement' ? 'selected' : '' }}>Pending Endorsement</option>
                            <option value="endorsed"            {{ $item->payment_status === 'endorsed'            ? 'selected' : '' }}>Endorsed</option>
                            <option value="endorsed_and_paid"   {{ $item->payment_status === 'endorsed_and_paid'   ? 'selected' : '' }}>Endorsed and Paid</option>
                            <option value="waived"              {{ $item->payment_status === 'waived'              ? 'selected' : '' }}>Waived</option>
                            <option value="cancelled"           {{ $item->payment_status === 'cancelled'           ? 'selected' : '' }}>Cancelled</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label small">Remarks</label>
                          <input type="text" name="remarks" class="form-control form-control-sm" value="{{ $item->remarks }}">
                        </div>
                        <div class="col-12">
                          <label class="form-label small">Replace File (overwrites original)</label>
                          <input type="file" name="file" class="form-control form-control-sm" accept="application/pdf">
                        </div>
                      </div>
                      <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn-action">Save</button>
                        <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                                x-on:click="editing = false">Cancel</button>
                      </div>
                    </form>
                  </td>
                </tr>
                {{-- Inline endorse row: file upload only --}}
                <tr x-show="endorsing" x-cloak style="background:#f0fff4;">
                  <td colspan="11" class="py-3 px-3">
                    <form action="{{ route('projects.boq-inv-items.update', [$project, $item]) }}" method="POST" enctype="multipart/form-data">
                      @csrf
                      <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                          <label class="form-label small fw-semibold">Upload Endorsed File <span class="text-danger">*</span></label>
                          <input type="file" name="file" class="form-control form-control-sm" accept="application/pdf" required>
                        </div>
                        <div class="col-md-7 d-flex gap-2">
                          <button type="submit" class="btn-action btn-action-green">Endorse</button>
                          <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                                  x-on:click="endorsing = false">Cancel</button>
                        </div>
                      </div>
                    </form>
                  </td>
                </tr>
                @endrole
              </tbody>
              @empty
              <tbody>
                <tr><td colspan="11" class="text-center text-muted py-3">No BOQ/INV items yet.</td></tr>
              </tbody>
              @endforelse
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 4: WAYLEAVE RECEIVED (Contractor uploads per PBT)        --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">4</span> Wayleave Received</h3>

        @forelse ($project->wayleavePhbts as $pbt)
        <div class="border rounded p-3 mb-3">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <strong>{{ $pbt->pbt_number }} — {{ str_replace('_', ' ', $pbt->pbt_name === 'Others' ? $pbt->pbt_name_other : $pbt->pbt_name) }}</strong>
            @if($pbt->endorsed_by)
              <span class="badge bg-success">Endorsed by {{ $pbt->endorsedBy?->name }}</span>
            @endif
          </div>
          <div class="row">
            <div class="col-md-3 mb-2">
              <div class="text-muted small">Date Received</div>
              <div>{{ $pbt->wayleave_received_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="col-md-3 mb-2">
              <div class="text-muted small">File</div>
              @if($pbt->wayleave_file)
                <a href="{{ route('projects.download', ['project' => $project, 'path' => $pbt->wayleave_file]) }}"
                   class="btn-action btn-action-sm">Download</a>
              @else
                <span class="text-muted">—</span>
              @endif
            </div>
          </div>
          {{-- Contractor: replace file --}}
          @role('contractor')
          <div class="mt-2" x-data="{ open: false }">
            <button type="button" class="btn-action btn-action-sm" x-on:click="open = !open">Replace File</button>
            <div x-show="open" x-cloak class="mt-2">
              <form action="{{ route('projects.wayleave-pbts.replace', [$project, $pbt]) }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-end">
                @csrf
                <input type="file" name="wayleave_file" class="form-control form-control-sm" accept="application/pdf" required>
                <button type="submit" class="btn-action btn-action-sm">Upload</button>
              </form>
            </div>
          </div>
          @endrole
        </div>
        @empty
          <p class="text-muted">No wayleave PBT records yet.</p>
        @endforelse

        {{-- Add new PBT --}}
        @can('update', $project)
        @if($project->wayleavePhbts->count() < 3)
        <div x-data="{ open: false }">
          <button type="button" class="btn-action" x-on:click="open = !open">+ Add Wayleave PBT</button>
          <div x-show="open" x-cloak class="mt-3 p-3 border rounded bg-light">
            <form action="{{ route('projects.wayleave-pbts.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row g-2">
                <div class="col-md-2">
                  <label class="form-label small">PBT Number <span class="text-danger">*</span></label>
                  <select name="pbt_number" class="form-control form-control-sm" required>
                    @php $usedPbts = $project->wayleavePhbts->pluck('pbt_number')->toArray(); @endphp
                    @foreach(['PBT1','PBT2','PBT3'] as $p)
                      @if(!in_array($p, $usedPbts))
                        <option value="{{ $p }}">{{ $p }}</option>
                      @endif
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3" x-data="{ showOther: false }">
                  <label class="form-label small">PBT Name <span class="text-danger">*</span></label>
                  <select name="pbt_name" class="form-control form-control-sm" required
                          @change="showOther = $event.target.value === 'Others'">
                    @foreach(['MBKT','MPK','MDS','MDB','MPD','JKR_HT','JKR_KN','JKR_DN','JKR_KT','JKR_KM','JKR_ST','Others'] as $pn)
                      <option value="{{ $pn }}">{{ $pn }}</option>
                    @endforeach
                  </select>
                  <input type="text" name="pbt_name_other" class="form-control form-control-sm mt-1"
                         placeholder="Specify other PBT name" x-show="showOther" x-cloak>
                </div>
                <div class="col-md-2">
                  <label class="form-label small">Date Received</label>
                  <input type="date" name="wayleave_received_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-4">
                  <label class="form-label small">Wayleave File (PDF)</label>
                  <input type="file" name="wayleave_file" class="form-control form-control-sm" accept="application/pdf">
                </div>
              </div>
              <div class="mt-2">
                <button type="submit" class="btn-action btn-action-sm">Add PBT</button>
              </div>
            </form>
          </div>
        </div>
        @endif
        @endcan
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 5: TM WAYLEAVE ENDORSEMENT (Officer file upload only)    --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">5</span> TM Wayleave Endorsement</h3>
        @if($project->wayleavePhbts->isEmpty())
          <p class="text-muted">No wayleave PBT records yet (Section 4 must be filled first).</p>
        @else
          @foreach($project->wayleavePhbts as $pbt)
          <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <strong>{{ $pbt->pbt_number }} — {{ str_replace('_', ' ', $pbt->pbt_name === 'Others' ? $pbt->pbt_name_other : $pbt->pbt_name) }}</strong>
              @if($pbt->endorsed_by)
                <span class="badge bg-success">Endorsed by {{ $pbt->endorsedBy?->name }}</span>
              @else
                <span class="badge bg-warning text-dark">Pending Endorsement</span>
              @endif
            </div>
            @if($pbt->wayleave_file)
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $pbt->wayleave_file]) }}"
                 class="btn-action btn-action-sm">Download Current File</a>
            @endif
            {{-- Officer/admin: upload endorsed file --}}
            @role('officer|admin')
            <div class="mt-2" x-data="{ open: false }">
              <button type="button" class="btn-action btn-action-sm" x-on:click="open = !open">Upload Endorsed File</button>
              <div x-show="open" x-cloak class="mt-2">
                <form action="{{ route('projects.wayleave-pbts.endorse', [$project, $pbt]) }}" method="POST" enctype="multipart/form-data"
                      class="d-flex gap-2 align-items-end">
                  @csrf
                  <input type="file" name="wayleave_file" class="form-control form-control-sm" accept="application/pdf" required>
                  <button type="submit" class="btn-action btn-action-sm">Endorse</button>
                </form>
              </div>
            </div>
            @endrole
          </div>
          @endforeach
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 6: TM WAYLEAVE PAYMENT DETAILS (FI & DEPOSIT)            --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">6</span> TM: Wayleave Payment Details (FI &amp; Deposit)</h3>
        @if($project->wayleavePhbts->isEmpty())
          <p class="text-muted">No PBT records yet.</p>
        @else
          <div class="table-responsive">
            <table class="table table-borderless mb-0">
              <thead>
                <tr>
                  <th style="font-weight:600; color:#07326A;">PBT</th>
                  <th style="font-weight:600; color:#07326A;">Payment Type</th>
                  <th style="font-weight:600; color:#07326A;">Status</th>
                  <th style="font-weight:600; color:#07326A;">Amount (RM)</th>
                  <th style="font-weight:600; color:#07326A;">EDS No</th>
                  <th style="font-weight:600; color:#07326A;">Method of Payment</th>
                  <th style="font-weight:600; color:#07326A;">Application Date</th>
                  @role('officer|admin') <th style="font-weight:600; color:#07326A;">Action</th> @endrole
                </tr>
              </thead>
              @foreach($project->wayleavePhbts as $pbt)
                @php
                  $pbtLabel   = str_replace('_', ' ', $pbt->pbt_name === 'Others' ? $pbt->pbt_name_other : $pbt->pbt_name);
                  $paymentFI  = $pbt->payments->firstWhere('payment_type', 'FI');
                  $paymentDep = $pbt->payments->firstWhere('payment_type', 'Deposit');
                @endphp
                @php
                  $fiShowDetails  = $paymentFI?->status  === 'required';
                  $depShowDetails = $paymentDep?->status === 'required';
                  $methodLabels   = [
                      'BG'      => 'BG - Bank Guarantee',
                      'BD_DAP'  => 'BD - DAP - Bank Draft',
                      'EFT_DAP' => 'EFT - DAP - Electronic Fund Transfer',
                  ];
                @endphp
                <tbody x-data="{ editing: false, fiStatus: '{{ $paymentFI?->status ?? '' }}', depStatus: '{{ $paymentDep?->status ?? '' }}' }">
                  {{-- FI row --}}
                  <tr>
                    <td rowspan="2" style="vertical-align:middle;">{{ $pbtLabel }}</td>
                    <td>FI</td>
                    <td>{{ $paymentFI?->status ? ucfirst(str_replace('_', ' ', $paymentFI->status)) : '—' }}</td>
                    <td>{{ $fiShowDetails && $paymentFI?->amount ? number_format($paymentFI->amount, 2) : '—' }}</td>
                    <td>{{ $fiShowDetails ? ($paymentFI?->eds_no ?? '—') : '—' }}</td>
                    <td>{{ $fiShowDetails ? ($methodLabels[$paymentFI?->method_of_payment] ?? '—') : '—' }}</td>
                    <td>{{ $fiShowDetails ? ($paymentFI?->application_date?->format('d/m/Y') ?? '—') : '—' }}</td>
                    @role('officer|admin')
                    <td rowspan="2" style="vertical-align:middle;">
                      <button type="button" class="btn-action btn-action-sm"
                              x-on:click="editing = !editing">Edit</button>
                    </td>
                    @endrole
                  </tr>
                  {{-- Deposit row --}}
                  <tr>
                    <td>Deposit</td>
                    <td>{{ $paymentDep?->status ? ucfirst(str_replace('_', ' ', $paymentDep->status)) : '—' }}</td>
                    <td>{{ $depShowDetails && $paymentDep?->amount ? number_format($paymentDep->amount, 2) : '—' }}</td>
                    <td>{{ $depShowDetails ? ($paymentDep?->eds_no ?? '—') : '—' }}</td>
                    <td>{{ $depShowDetails ? ($methodLabels[$paymentDep?->method_of_payment] ?? '—') : '—' }}</td>
                    <td>{{ $depShowDetails ? ($paymentDep?->application_date?->format('d/m/Y') ?? '—') : '—' }}</td>
                  </tr>
                  @role('officer|admin')
                  {{-- Combined FI + Deposit edit row (single form, single Save) --}}
                  <tr x-show="editing" x-cloak style="background:#f8f9fa;">
                    <td colspan="8" class="py-3 px-3">
                      <form action="{{ route('projects.wayleave-payments.store-pbt', $project) }}" method="POST">
                        @csrf
                        <input type="hidden" name="wayleave_pbt_id" value="{{ $pbt->id }}">
                        {{-- FI fields --}}
                        <div class="fw-semibold small mb-2" style="color:#07326A;">FI Payment — {{ $pbtLabel }}</div>
                        <div class="row g-2 mb-3">
                          <div class="col-md-3">
                            <label class="form-label small">Status</label>
                            <select name="fi[status]" x-model="fiStatus" class="form-control form-control-sm">
                              <option value="">— Select —</option>
                              <option value="required"     {{ $paymentFI?->status === 'required'     ? 'selected' : '' }}>Required</option>
                              <option value="not_required" {{ $paymentFI?->status === 'not_required' ? 'selected' : '' }}>Not Required</option>
                              <option value="waived"       {{ $paymentFI?->status === 'waived'       ? 'selected' : '' }}>Waived</option>
                            </select>
                          </div>
                          <template x-if="fiStatus === 'required'">
                            <div class="col-md-9 row g-2 m-0 p-0">
                              <div class="col-md-3">
                                <label class="form-label small">Amount (RM)</label>
                                <input type="number" name="fi[amount]" step="0.01" class="form-control form-control-sm" value="{{ $paymentFI?->amount }}">
                              </div>
                              <div class="col-md-3">
                                <label class="form-label small">EDS No</label>
                                <input type="text" name="fi[eds_no]" class="form-control form-control-sm" value="{{ $paymentFI?->eds_no }}">
                              </div>
                              <div class="col-md-3">
                                <label class="form-label small">Method of Payment</label>
                                <select name="fi[method_of_payment]" class="form-control form-control-sm">
                                  <option value="">— Select —</option>
                                  <option value="BG"      {{ $paymentFI?->method_of_payment === 'BG'      ? 'selected' : '' }}>BG - Bank Guarantee</option>
                                  <option value="BD_DAP"  {{ $paymentFI?->method_of_payment === 'BD_DAP'  ? 'selected' : '' }}>BD - DAP - Bank Draft</option>
                                  <option value="EFT_DAP" {{ $paymentFI?->method_of_payment === 'EFT_DAP' ? 'selected' : '' }}>EFT - DAP - Electronic Fund Transfer</option>
                                </select>
                              </div>
                              <div class="col-md-3">
                                <label class="form-label small">Application Date</label>
                                <input type="date" name="fi[application_date]" class="form-control form-control-sm" value="{{ $paymentFI?->application_date?->format('Y-m-d') }}">
                              </div>
                            </div>
                          </template>
                        </div>
                        {{-- Deposit fields --}}
                        <div class="fw-semibold small mb-2" style="color:#07326A; border-top:1px dashed #dee2e6; padding-top:0.75rem;">Deposit — {{ $pbtLabel }}</div>
                        <div class="row g-2">
                          <div class="col-md-3">
                            <label class="form-label small">Status</label>
                            <select name="deposit[status]" x-model="depStatus" class="form-control form-control-sm">
                              <option value="">— Select —</option>
                              <option value="required"     {{ $paymentDep?->status === 'required'     ? 'selected' : '' }}>Required</option>
                              <option value="not_required" {{ $paymentDep?->status === 'not_required' ? 'selected' : '' }}>Not Required</option>
                              <option value="waived"       {{ $paymentDep?->status === 'waived'       ? 'selected' : '' }}>Waived</option>
                            </select>
                          </div>
                          <template x-if="depStatus === 'required'">
                            <div class="col-md-9 row g-2 m-0 p-0">
                              <div class="col-md-3">
                                <label class="form-label small">Amount (RM)</label>
                                <input type="number" name="deposit[amount]" step="0.01" class="form-control form-control-sm" value="{{ $paymentDep?->amount }}">
                              </div>
                              <div class="col-md-3">
                                <label class="form-label small">EDS No</label>
                                <input type="text" name="deposit[eds_no]" class="form-control form-control-sm" value="{{ $paymentDep?->eds_no }}">
                              </div>
                              <div class="col-md-3">
                                <label class="form-label small">Method of Payment</label>
                                <select name="deposit[method_of_payment]" class="form-control form-control-sm">
                                  <option value="">— Select —</option>
                                  <option value="BG"      {{ $paymentDep?->method_of_payment === 'BG'      ? 'selected' : '' }}>BG - Bank Guarantee</option>
                                  <option value="BD_DAP"  {{ $paymentDep?->method_of_payment === 'BD_DAP'  ? 'selected' : '' }}>BD - DAP - Bank Draft</option>
                                  <option value="EFT_DAP" {{ $paymentDep?->method_of_payment === 'EFT_DAP' ? 'selected' : '' }}>EFT - DAP - Electronic Fund Transfer</option>
                                </select>
                              </div>
                              <div class="col-md-3">
                                <label class="form-label small">Application Date</label>
                                <input type="date" name="deposit[application_date]" class="form-control form-control-sm" value="{{ $paymentDep?->application_date?->format('Y-m-d') }}">
                              </div>
                            </div>
                          </template>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                          <button type="submit" class="btn-action btn-action-sm">Save</button>
                          <button type="button" class="btn-action btn-action-sm" style="background:#6c757d; border-color:#6c757d;"
                                  x-on:click="editing = false">Cancel</button>
                        </div>
                      </form>
                    </td>
                  </tr>
                  @endrole
                </tbody>
              @endforeach
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 7: TM BG & BD RECEIVED FROM FINSSO                       --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">7</span> TM: BG &amp; BD Received from FINSSO</h3>
        @php
          $requiredPayments = $project->wayleavePayments->where('status', 'required');
        @endphp
        @if($requiredPayments->isEmpty())
          <p class="text-muted">No required payments (only rows where status = Required appear here).</p>
        @else
          <div class="table-responsive">
            <table class="table table-borderless mb-0">
              <thead>
                <tr>
                  <th style="font-weight:600; color:#07326A;">PBT</th>
                  <th style="font-weight:600; color:#07326A;">Payment Type</th>
                  <th style="font-weight:600; color:#07326A;">Amount (RM)</th>
                  <th style="font-weight:600; color:#07326A;">EDS No</th>
                  <th style="font-weight:600; color:#07326A;">Method</th>
                  <th style="font-weight:600; color:#07326A;">Application Date</th>
                  <th style="font-weight:600; color:#07326A;">Received / Posted Date</th>
                  <th style="font-weight:600; color:#07326A;">BG/BD Document</th>
                  @role('officer|admin') <th style="font-weight:600; color:#07326A;">Action</th> @endrole
                </tr>
              </thead>
              @foreach($requiredPayments as $payment)
              <tbody x-data="{ editing: false }">
                <tr>
                  <td>{{ str_replace('_', ' ', $payment->wayleavePhbt?->pbt_name === 'Others' ? $payment->wayleavePhbt?->pbt_name_other : $payment->wayleavePhbt?->pbt_name) }}</td>
                  <td>{{ $payment->payment_type }}</td>
                  <td>{{ $payment->amount ? number_format($payment->amount, 2) : '—' }}</td>
                  <td>{{ $payment->eds_no ?? '—' }}</td>
                  <td>{{ ['BG' => 'BG - Bank Guarantee', 'BD_DAP' => 'BD - DAP - Bank Draft', 'EFT_DAP' => 'EFT - DAP - Electronic Fund Transfer'][$payment->method_of_payment] ?? '—' }}</td>
                  <td>{{ $payment->application_date?->format('d/m/Y') ?? '—' }}</td>
                  <td>{{ $payment->received_posted_date?->format('d/m/Y') ?? '—' }}</td>
                  <td>
                    @if($payment->bg_bd_file_path)
                      <a href="{{ route('projects.download', ['project' => $project, 'path' => $payment->bg_bd_file_path]) }}"
                         class="btn-action btn-action-sm">Download</a>
                    @else
                      —
                    @endif
                  </td>
                  @role('officer|admin')
                  <td>
                    <button type="button" class="btn-action btn-action-sm"
                            x-on:click="editing = !editing">Edit</button>
                  </td>
                  @endrole
                </tr>
                @role('officer|admin')
                <tr x-show="editing" x-cloak style="background:#f8f9fa;">
                  <td colspan="9" class="py-3 px-3">
                    <form action="{{ route('projects.wayleave-payments.received', [$project, $payment]) }}" method="POST" enctype="multipart/form-data">
                      @csrf
                      <div class="row g-2">
                        <div class="col-md-3">
                          <label class="form-label small">Received / Posted Date</label>
                          <input type="date" name="received_posted_date" class="form-control form-control-sm"
                                 value="{{ $payment->received_posted_date?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-5">
                          <label class="form-label small">BG/BD Document (PDF)</label>
                          <input type="file" name="bg_bd_file" class="form-control form-control-sm" accept="application/pdf">
                        </div>
                      </div>
                      <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn-action">Save</button>
                        <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                                x-on:click="editing = false">Cancel</button>
                      </div>
                    </form>
                  </td>
                </tr>
                @endrole
              </tbody>
              @endforeach
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 8: DOC PERMIT APPLICATION SUBMISSION TO KUTT             --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">8</span> Doc Permit Application Submission to KUTT</h3>
        @php $permitSub = $project->permitSubmission; @endphp
        @if($permitSub)
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="text-muted small">Submit Date</div>
              <div>{{ $permitSub->submit_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="col-md-3">
              <div class="text-muted small">File</div>
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $permitSub->submission_file]) }}"
                 class="btn-action btn-action-sm">Download</a>
            </div>
            <div class="col-md-3">
              <div class="text-muted small">Submitted By</div>
              <div>{{ $permitSub->submittedBy?->name ?? '—' }}</div>
            </div>
          </div>
        @endif
        @can('update', $project)
        <div x-data="{ open: {{ $permitSub ? 'false' : 'true' }} }">
          <button type="button" class="btn-action btn-action-sm" x-on:click="open = !open">
            {{ $permitSub ? 'Update Submission' : 'Record Submission' }}
          </button>
          <div x-show="open" x-cloak class="mt-3 p-3 border rounded bg-light">
            <form action="{{ route('projects.permit-submission.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row g-2">
                <div class="col-md-3">
                  <label class="form-label small">Submit Date <span class="text-danger">*</span></label>
                  <input type="date" name="submit_date" class="form-control form-control-sm"
                         value="{{ $permitSub?->submit_date?->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-5">
                  <label class="form-label small">Submission File (PDF)</label>
                  <input type="file" name="submission_file" class="form-control form-control-sm" accept="application/pdf"
                         {{ $permitSub ? '' : 'required' }}>
                </div>
              </div>
              <div class="mt-2"><button type="submit" class="btn-action btn-action-sm">Save</button></div>
            </form>
          </div>
        </div>
        @endcan
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 9: PERMIT RECEIVED                                        --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">9</span> Permit Received</h3>
        @php $permitRec = $project->permitReceived; @endphp
        @if($permitRec)
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="text-muted small">Permit Received Date</div>
              <div>{{ $permitRec->permit_received_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="col-md-3">
              <div class="text-muted small">Permit File</div>
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $permitRec->permit_file]) }}"
                 class="btn-action btn-action-sm">Download</a>
            </div>
          </div>
        @endif
        @can('update', $project)
        <div x-data="{ open: {{ $permitRec ? 'false' : 'true' }} }">
          <button type="button" class="btn-action btn-action-sm" x-on:click="open = !open">
            {{ $permitRec ? 'Update Permit Received' : 'Record Permit Received' }}
          </button>
          <div x-show="open" x-cloak class="mt-3 p-3 border rounded bg-light">
            <form action="{{ route('projects.permit-received.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row g-2">
                <div class="col-md-3">
                  <label class="form-label small">Date Received <span class="text-danger">*</span></label>
                  <input type="date" name="permit_received_date" class="form-control form-control-sm"
                         value="{{ $permitRec?->permit_received_date?->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-5">
                  <label class="form-label small">Permit File (PDF)</label>
                  <input type="file" name="permit_file" class="form-control form-control-sm" accept="application/pdf"
                         {{ $permitRec ? '' : 'required' }}>
                </div>
              </div>
              <div class="mt-2"><button type="submit" class="btn-action btn-action-sm">Save</button></div>
            </form>
          </div>
        </div>
        @endcan
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 10: NOTIS MULA KERJA                                      --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">10</span> Notis Mula Kerja</h3>
        @php $workNotice = $project->workNotice; @endphp
        @if($workNotice?->notis_mula_file)
          <div class="mb-3">
            <a href="{{ route('projects.download', ['project' => $project, 'path' => $workNotice->notis_mula_file]) }}"
               class="btn-action btn-action-sm">Download Notis Mula</a>
          </div>
        @endif
        @can('update', $project)
        <div x-data="{ open: {{ $workNotice?->notis_mula_file ? 'false' : 'true' }} }">
          <button type="button" class="btn-action btn-action-sm" x-on:click="open = !open">
            {{ $workNotice?->notis_mula_file ? 'Replace Notis Mula' : 'Upload Notis Mula Kerja' }}
          </button>
          <div x-show="open" x-cloak class="mt-3 p-3 border rounded bg-light">
            <form action="{{ route('projects.notis-mula.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <input type="file" name="notis_mula_file" class="form-control form-control-sm mb-2" accept="application/pdf" required>
              <button type="submit" class="btn-action btn-action-sm">Upload</button>
            </form>
          </div>
        </div>
        @endcan
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 11: NOTIS SIAP KERJA                                      --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">11</span> Notis Siap Kerja</h3>
        @if($workNotice?->notis_siap_file)
          <div class="mb-3">
            <a href="{{ route('projects.download', ['project' => $project, 'path' => $workNotice->notis_siap_file]) }}"
               class="btn-action btn-action-sm">Download Notis Siap</a>
          </div>
        @endif
        @can('update', $project)
        <div x-data="{ open: {{ $workNotice?->notis_siap_file ? 'false' : 'true' }} }">
          <button type="button" class="btn-action btn-action-sm" x-on:click="open = !open">
            {{ $workNotice?->notis_siap_file ? 'Replace Notis Siap' : 'Upload Notis Siap Kerja' }}
          </button>
          <div x-show="open" x-cloak class="mt-3 p-3 border rounded bg-light">
            <form action="{{ route('projects.notis-siap.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <input type="file" name="notis_siap_file" class="form-control form-control-sm mb-2" accept="application/pdf" required>
              <button type="submit" class="btn-action btn-action-sm">Upload</button>
            </form>
          </div>
        </div>
        @endcan
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 12: PERMOHONAN SIJIL PERAKUAN SIAP KERJA (CPC)            --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">12</span> Permohonan Sijil Perakuan Siap Kerja (CPC)</h3>
        @php $cpcApp = $project->cpcApplication; @endphp
        @if($cpcApp)
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="text-muted small">Date Submitted to KUTT</div>
              <div>{{ $cpcApp->date_submit_to_kutt?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="col-md-9">
              <div class="text-muted small">Documents</div>
              <div class="d-flex flex-wrap gap-2 mt-1">
                @if($cpcApp->surat_serahan_file)
                  <a href="{{ route('projects.download', ['project' => $project, 'path' => $cpcApp->surat_serahan_file]) }}" class="btn-action btn-action-sm">Surat Serahan</a>
                @endif
                @if($cpcApp->laporan_bergambar_file)
                  <a href="{{ route('projects.download', ['project' => $project, 'path' => $cpcApp->laporan_bergambar_file]) }}" class="btn-action btn-action-sm">Laporan Bergambar</a>
                @endif
                @if($cpcApp->salinan_coa_file)
                  <a href="{{ route('projects.download', ['project' => $project, 'path' => $cpcApp->salinan_coa_file]) }}" class="btn-action btn-action-sm">Salinan COA</a>
                @endif
                @if($cpcApp->salinan_permit_file)
                  <a href="{{ route('projects.download', ['project' => $project, 'path' => $cpcApp->salinan_permit_file]) }}" class="btn-action btn-action-sm">Salinan Permit</a>
                @endif
              </div>
            </div>
          </div>
        @endif
        @can('update', $project)
        <div x-data="{ open: {{ $cpcApp ? 'false' : 'true' }} }">
          <button type="button" class="btn-action btn-action-sm" x-on:click="open = !open">
            {{ $cpcApp ? 'Update CPC Application' : 'Submit CPC Application' }}
          </button>
          <div x-show="open" x-cloak class="mt-3 p-3 border rounded bg-light">
            <form action="{{ route('projects.cpc-application.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row g-2">
                <div class="col-md-3">
                  <label class="form-label small">Date Submitted to KUTT <span class="text-danger">*</span></label>
                  <input type="date" name="date_submit_to_kutt" class="form-control form-control-sm"
                         value="{{ $cpcApp?->date_submit_to_kutt?->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label small">Surat Serahan</label>
                  <input type="file" name="surat_serahan_file" class="form-control form-control-sm" accept="application/pdf">
                </div>
                <div class="col-md-4">
                  <label class="form-label small">Laporan Bergambar</label>
                  <input type="file" name="laporan_bergambar_file" class="form-control form-control-sm" accept="application/pdf">
                </div>
                <div class="col-md-4">
                  <label class="form-label small">Salinan COA</label>
                  <input type="file" name="salinan_coa_file" class="form-control form-control-sm" accept="application/pdf">
                </div>
                <div class="col-md-4">
                  <label class="form-label small">Salinan Permit</label>
                  <input type="file" name="salinan_permit_file" class="form-control form-control-sm" accept="application/pdf">
                </div>
              </div>
              <div class="mt-2"><button type="submit" class="btn-action btn-action-sm">Save</button></div>
            </form>
          </div>
        </div>
        @endcan
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 13: SIJIL PERAKUAN SIAP KERJA                             --}}
{{-- Uploading CPC sets project status to Completed                    --}}
{{-- ================================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">13</span> Sijil Perakuan Siap Kerja</h3>
        @php $cpcRec = $project->cpcReceived; @endphp
        @if($cpcRec)
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="text-muted small">CPC Date</div>
              <div>{{ $cpcRec->cpc_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="col-md-3">
              <div class="text-muted small">CPC File</div>
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $cpcRec->cpc_file]) }}"
                 class="btn-action btn-action-sm">Download CPC</a>
            </div>
            <div class="col-md-3">
              <div class="text-muted small">Uploaded By</div>
              <div>{{ $cpcRec->uploadedBy?->name ?? '—' }}</div>
            </div>
          </div>
          <div class="alert alert-success py-2">Project marked as <strong>Completed</strong>.</div>
        @endif
        @can('update', $project)
        <div x-data="{ open: {{ $cpcRec ? 'false' : 'true' }} }">
          <button type="button" class="btn-action btn-action-sm" x-on:click="open = !open">
            {{ $cpcRec ? 'Update CPC' : 'Upload Received CPC' }}
          </button>
          <div x-show="open" x-cloak class="mt-3 p-3 border rounded bg-light">
            <form action="{{ route('projects.cpc-received.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row g-2">
                <div class="col-md-3">
                  <label class="form-label small">CPC Date <span class="text-danger">*</span></label>
                  <input type="date" name="cpc_date" class="form-control form-control-sm"
                         value="{{ $cpcRec?->cpc_date?->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-5">
                  <label class="form-label small">CPC File (PDF)</label>
                  <input type="file" name="cpc_file" class="form-control form-control-sm" accept="application/pdf"
                         {{ $cpcRec ? '' : 'required' }}>
                </div>
              </div>
              <div class="mt-2">
                <button type="submit" class="btn-action btn-action-sm btn-action-green">
                  Upload &amp; Mark Completed
                </button>
              </div>
            </form>
          </div>
        </div>
        @endcan
      </div>
    </div>
  </div>
</div>

@endif {{-- end if not cancelled --}}

@endsection
