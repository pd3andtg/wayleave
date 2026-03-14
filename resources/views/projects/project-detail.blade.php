@extends('layouts.dashboard')

@section('title', 'Project Detail')

@section('content')

{{-- ============================================================ --}}
{{-- PROJECT HEADER                                               --}}
{{-- ============================================================ --}}
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
          <div style="display: flex; align-items: center; gap: 8px;">
            @if ($project->status === 'completed')
              <span class="badge bg-success" style="font-size: 0.75rem; padding: 0.4rem 0.75rem; line-height: 1;">Completed</span>
            @else
              <span class="badge bg-warning text-dark" style="font-size: 0.75rem; padding: 0.4rem 0.75rem; line-height: 1;">Outstanding</span>
            @endif
            <a href="{{ route('projects.index') }}" class="btn-action">
              &larr; Back to Dashboard
            </a>
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
    @foreach ($errors->all() as $error)
      <div>{{ $error }}</div>
    @endforeach
  </div>
@endif

{{-- ============================================================ --}}
{{-- STEP 2: Project Information (Editable)                       --}}
{{-- Step 1 was on the create-project page. This shows the same  --}}
{{-- data as an editable form so details can be corrected later.  --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">1</span> Project Information</h3>

        <div x-data="{ editing: false }">

          {{-- Read-only summary --}}
          <div x-show="!editing" x-cloak>
            <div class="row">
              <div class="col-md-4 mb-2">
                <div class="text-muted small fw-bold">KUTT Ref No / PBT Ref No</div>
                <div>{{ $project->ref_no ?? '—' }}</div>
              </div>
              <div class="col-md-4 mb-2">
                <div class="text-muted small fw-bold">LOR No</div>
                <div>{{ $project->lor_no ?? '—' }}</div>
              </div>
              <div class="col-md-4 mb-2">
                <div class="text-muted small fw-bold">Project No</div>
                <div>{{ $project->project_no ?? '—' }}</div>
              </div>
              <div class="col-md-4 mb-2">
                <div class="text-muted small fw-bold">Project Description</div>
                <div>{{ $project->project_desc }}</div>
              </div>
              <div class="col-md-4 mb-2">
                <div class="text-muted small fw-bold">ND State</div>
                <div>{{ str_replace('_', ' ', $project->nd_state) }}</div>
              </div>
              <div class="col-md-4 mb-2">
                <div class="text-muted small fw-bold">Remarks</div>
                <div>{{ $project->remarks ?? '—' }}</div>
              </div>
            </div>
            @can('update', $project)
            <div class="d-flex justify-content-end mt-2">
              <button type="button" class="btn-action" x-on:click="editing = true">Edit</button>
            </div>
            @endcan
          </div>

          {{-- Edit form --}}
          @can('update', $project)
          <div x-show="editing" x-cloak>
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
                <div class="col-md-8 mb-3 d-flex flex-column">
                  <label class="form-label text-muted small">Project Description <span class="text-danger">*</span></label>
                  <textarea name="project_desc" class="form-control flex-grow-1" rows="2" required>{{ old('project_desc', $project->project_desc) }}</textarea>
                </div>
                <div class="col-md-4 mb-3 d-flex flex-column">
                  <label class="form-label text-muted small">ND State <span class="text-danger">*</span></label>
                  <select name="nd_state" class="form-control flex-grow-1" required>
                    <option value="ND_TRG" {{ old('nd_state', $project->nd_state) === 'ND_TRG' ? 'selected' : '' }}>ND TRG</option>
                    <option value="ND_PHG" {{ old('nd_state', $project->nd_state) === 'ND_PHG' ? 'selected' : '' }}>ND PHG</option>
                    <option value="ND_KEL" {{ old('nd_state', $project->nd_state) === 'ND_KEL' ? 'selected' : '' }}>ND KEL</option>
                  </select>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label text-muted small">Remarks</label>
                  <textarea name="remarks" class="form-control" rows="2" placeholder="Optional">{{ old('remarks', $project->remarks) }}</textarea>
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

{{-- ============================================================ --}}
{{-- STEP 3: Project Timeline (Read-Only Visual Indicator)        --}}
{{-- No database table — reads completion state from all records.  --}}
{{-- ============================================================ --}}
@php
$timelineStages = [
    ['label' => 'Project Info',      'step' => 1,  'done' => true],
    ['label' => 'BQ/INV Upload',     'step' => 2,  'done' => $project->bqInvFiles->isNotEmpty()],
    ['label' => 'BQ/INV Endorsed',   'step' => 3,  'done' => $project->bqInvFiles->filter(fn($f) => $f->bqEndorsement || $f->invEndorsement)->isNotEmpty()],
    ['label' => 'Wayleave',          'step' => 4,  'done' => $project->wayleavePhbts->isNotEmpty()],
    ['label' => 'Wayleave Payment',  'step' => 5,  'done' => $project->wayleavePayments->isNotEmpty()],
    ['label' => 'Permit Submitted',  'step' => 6,  'done' => $project->permitSubmission !== null],
    ['label' => 'Permit Received',   'step' => 7,  'done' => $project->permitReceived !== null],
    ['label' => 'Work Notices',      'step' => 8,  'done' => $project->workNotice !== null],
    ['label' => 'CPC Application',   'step' => 9,  'done' => $project->cpcApplication !== null],
    ['label' => 'CPC Received',      'step' => 10, 'done' => $project->cpcReceived !== null],
];
@endphp
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">Project Timeline</h3>
        <div class="d-flex align-items-start" style="overflow-x:auto; padding-bottom:0.5rem;">
          @foreach ($timelineStages as $i => $stage)
            <div class="d-flex flex-column align-items-center" style="min-width:72px; flex:1;">
              <div class="d-flex align-items-center w-100">
                {{-- Left connector --}}
                @if ($i > 0)
                  <div style="flex:1; height:2px; background:{{ $timelineStages[$i-1]['done'] ? '#28a745' : '#dee2e6' }};"></div>
                @else
                  <div style="flex:1;"></div>
                @endif
                {{-- Circle --}}
                <div style="
                  width:30px; height:30px; border-radius:50%; flex-shrink:0;
                  background:{{ $stage['done'] ? '#28a745' : '#dee2e6' }};
                  color:{{ $stage['done'] ? '#fff' : '#6c757d' }};
                  display:flex; align-items:center; justify-content:center;
                  font-size:0.7rem; font-weight:700;
                ">
                  @if ($stage['done']) &#10003; @else {{ $stage['step'] }} @endif
                </div>
                {{-- Right connector --}}
                @if (!$loop->last)
                  <div style="flex:1; height:2px; background:{{ $stage['done'] ? '#28a745' : '#dee2e6' }};"></div>
                @else
                  <div style="flex:1;"></div>
                @endif
              </div>
              <div class="text-center mt-1" style="font-size:0.65rem; color:#6c757d; line-height:1.2;">{{ $stage['label'] }}</div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 4: BOQ/INV File Upload (up to 6 files per project)     --}}
{{-- Contractor uploads each file with full metadata.             --}}
{{-- Use the file_number dropdown to add or replace any slot.     --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">
          <span class="me-3">2</span> BOQ / Invoice Files
          @if ($project->bqInvFiles->isNotEmpty())
            <span class="badge bg-success ms-2">&#10003; {{ $project->bqInvFiles->count() }} file(s) uploaded</span>
          @endif
        </h3>

        @php
          $usedNumbers  = $project->bqInvFiles->pluck('file_number')->toArray();
          $nextNumber   = collect(range(1, 6))->first(fn($n) => !in_array($n, $usedNumbers)) ?? null;
          $allSlotsFull = count($usedNumbers) >= 6;
        @endphp

        {{-- Table of uploaded files, each row has an inline Replace toggle --}}
        @if ($project->bqInvFiles->isNotEmpty())
          <div class="table-responsive mb-3">
            <table class="table table-sm table-borderless align-middle">
              <thead>
                <tr class="text-muted small">
                  <th>#</th>
                  <th>Document Info</th>
                  <th>Type</th>
                  <th>Date</th>
                  <th>Amount (RM)</th>
                  <th>EDS No</th>
                  <th>Remarks</th>
                  <th></th>
                </tr>
              </thead>

              {{-- Each file gets its own tbody so Alpine x-data wraps both the data row and the replace row --}}
              @foreach ($project->bqInvFiles->sortBy('file_number') as $file)
                <tbody x-data="{ editing: false, fileName: 'No file chosen' }">
                  <tr>
                    <td class="text-muted small fw-bold">{{ $file->file_number }}</td>
                    <td>{{ $file->document_info }}</td>
                    <td>{{ $file->payment_type }}</td>
                    <td>{{ $file->date?->format('d M Y') }}</td>
                    <td>{{ number_format($file->amount, 2) }}</td>
                    <td>{{ $file->eds_no }}</td>
                    <td>{{ $file->remarks ?? '—' }}</td>
                    <td style="white-space:nowrap;">
                      <div class="d-flex gap-1 justify-content-end align-items-center">
                        <a href="{{ route('projects.download', ['project' => $project, 'path' => $file->file_path]) }}"
                           class="btn-action" style="font-size:0.8rem; padding:0.3rem 0.6rem; line-height:1;">
                          <i class="ti-download"></i>
                        </a>
                        @can('update', $project)
                          <button type="button" class="btn-action"
                                  style="font-size:0.8rem; padding:0.3rem 0.6rem; line-height:1;"
                                  x-on:click="editing = !editing"
                                  x-text="editing ? 'Cancel' : 'Edit'">Edit</button>
                        @endcan
                      </div>
                    </td>
                  </tr>

                  {{-- Inline edit form — revealed by clicking Edit --}}
                  @can('update', $project)
                    <tr x-show="editing" x-cloak style="background:#f8f9fa;">
                      <td colspan="8" class="py-3 px-3">
                        <form action="{{ route('projects.bq-inv-files.store', $project) }}" method="POST" enctype="multipart/form-data">
                          @csrf
                          <input type="hidden" name="file_number" value="{{ $file->file_number }}">
                          <div class="row mb-2 align-items-start">
                            <div class="col-md-4 mb-2">
                              <label class="form-label text-muted small">Replace File (PDF) <span class="text-muted">— leave blank to keep existing</span></label>
                              <div class="d-flex align-items-center" style="gap:8px;">
                                <input type="file" name="file_path" id="bq_file_{{ $file->id }}" accept=".pdf" style="display:none;"
                                       x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                                <label for="bq_file_{{ $file->id }}" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                                  <i class="ti-upload"></i> Choose File
                                </label>
                                <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                              </div>
                            </div>
                            <div class="col-md-4 mb-2">
                              <label class="form-label text-muted small">Document Info <span class="text-danger">*</span></label>
                              <input type="text" name="document_info" class="form-control" style="height:38px;"
                                     value="{{ $file->document_info }}" required>
                            </div>
                            <div class="col-md-4 mb-2">
                              <label class="form-label text-muted small">Type <span class="text-danger">*</span></label>
                              <select name="payment_type" class="form-control" style="height:38px;" required>
                                <option value="BQ" {{ $file->payment_type === 'BQ' ? 'selected' : '' }}>BQ</option>
                                <option value="INV" {{ $file->payment_type === 'INV' ? 'selected' : '' }}>INV</option>
                              </select>
                            </div>
                          </div>
                          <div class="row mb-2 align-items-end">
                            <div class="col-md-3 mb-2">
                              <label class="form-label text-muted small">Date <span class="text-danger">*</span></label>
                              <input type="date" name="date" class="form-control" style="height:38px;"
                                     value="{{ $file->date?->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                              <label class="form-label text-muted small">Amount (RM) <span class="text-danger">*</span></label>
                              <input type="number" name="amount" step="0.01" min="0" class="form-control" style="height:38px;"
                                     value="{{ $file->amount }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                              <label class="form-label text-muted small">EDS No <span class="text-danger">*</span></label>
                              <input type="text" name="eds_no" class="form-control" style="height:38px;"
                                     value="{{ $file->eds_no }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                              <label class="form-label text-muted small">Remarks</label>
                              <input type="text" name="remarks" class="form-control" style="height:38px;"
                                     value="{{ $file->remarks }}">
                            </div>
                          </div>
                          <div class="d-flex justify-content-end">
                            <button type="submit" class="btn-action">Save</button>
                          </div>
                        </form>
                      </td>
                    </tr>
                  @endcan
                </tbody>
              @endforeach

            </table>
          </div>
        @endif

        {{-- Add new file — toggle button when files already exist, direct form when first upload --}}
        @can('update', $project)
          @if ($allSlotsFull)
            <p class="text-muted small mb-0 mt-2">All 6 slots used — use the Edit button on any row to update a file.</p>
          @elseif ($project->bqInvFiles->isNotEmpty())
            {{-- Existing files present: hide form behind a toggle button --}}
            <div x-data="{ adding: false, fileName: 'No file chosen' }">
              <div x-show="!adding" x-cloak>
                <button type="button" class="btn-action" x-on:click="adding = true">+ Add New BOQ/Invoice File</button>
              </div>
              <div x-show="adding" x-cloak>
                <form action="{{ route('projects.bq-inv-files.store', $project) }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <input type="hidden" name="file_number" value="{{ $nextNumber }}">
                  <div class="row mb-2 align-items-start">
                    <div class="col-md-4 mb-2">
                      <label class="form-label text-muted small">File (PDF) <span class="text-danger">*</span></label>
                      <div class="d-flex align-items-center" style="gap:8px;">
                        <input type="file" name="file_path" id="bq_file_add" accept=".pdf" style="display:none;" required
                               x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                        <label for="bq_file_add" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                          <i class="ti-upload"></i> Choose File
                        </label>
                        <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                      </div>
                    </div>
                    <div class="col-md-4 mb-2">
                      <label class="form-label text-muted small">Document Info <span class="text-danger">*</span></label>
                      <input type="text" name="document_info" class="form-control" style="height:38px;" required>
                    </div>
                    <div class="col-md-4 mb-2">
                      <label class="form-label text-muted small">Type <span class="text-danger">*</span></label>
                      <select name="payment_type" class="form-control" style="height:38px;" required>
                        <option value="">--</option>
                        <option value="BQ">BQ</option>
                        <option value="INV">INV</option>
                      </select>
                    </div>
                  </div>
                  <div class="row mb-2 align-items-end">
                    <div class="col-md-3 mb-2">
                      <label class="form-label text-muted small">Date <span class="text-danger">*</span></label>
                      <input type="date" name="date" class="form-control" style="height:38px;" required>
                    </div>
                    <div class="col-md-3 mb-2">
                      <label class="form-label text-muted small">Amount (RM) <span class="text-danger">*</span></label>
                      <input type="number" name="amount" step="0.01" min="0" class="form-control" style="height:38px;" required>
                    </div>
                    <div class="col-md-3 mb-2">
                      <label class="form-label text-muted small">EDS No <span class="text-danger">*</span></label>
                      <input type="text" name="eds_no" class="form-control" style="height:38px;" required>
                    </div>
                    <div class="col-md-3 mb-2">
                      <label class="form-label text-muted small">Remarks</label>
                      <input type="text" name="remarks" class="form-control" style="height:38px;">
                    </div>
                  </div>
                  <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                            x-on:click="adding = false">Cancel</button>
                    <button type="submit" class="btn-action">Upload File</button>
                  </div>
                </form>
              </div>
            </div>
          @else
            {{-- No files yet: show form directly --}}
            <form action="{{ route('projects.bq-inv-files.store', $project) }}" method="POST" enctype="multipart/form-data"
                  x-data="{ fileName: 'No file chosen' }">
              @csrf
              <input type="hidden" name="file_number" value="{{ $nextNumber }}">
              <div class="row mb-2 align-items-start">
                <div class="col-md-4 mb-2">
                  <label class="form-label text-muted small">File (PDF) <span class="text-danger">*</span></label>
                  <div class="d-flex align-items-center" style="gap:8px;">
                    <input type="file" name="file_path" id="bq_file_first" accept=".pdf" style="display:none;" required
                           x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                    <label for="bq_file_first" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                      <i class="ti-upload"></i> Choose File
                    </label>
                    <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                  </div>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label text-muted small">Document Info <span class="text-danger">*</span></label>
                  <input type="text" name="document_info" class="form-control" style="height:38px;" required>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label text-muted small">Type <span class="text-danger">*</span></label>
                  <select name="payment_type" class="form-control" style="height:38px;" required>
                    <option value="">--</option>
                    <option value="BQ">BQ</option>
                    <option value="INV">INV</option>
                  </select>
                </div>
              </div>
              <div class="row mb-2 align-items-end">
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">Date <span class="text-danger">*</span></label>
                  <input type="date" name="date" class="form-control" style="height:38px;" required>
                </div>
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">Amount (RM) <span class="text-danger">*</span></label>
                  <input type="number" name="amount" step="0.01" min="0" class="form-control" style="height:38px;" required>
                </div>
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">EDS No <span class="text-danger">*</span></label>
                  <input type="text" name="eds_no" class="form-control" style="height:38px;" required>
                </div>
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">Remarks</label>
                  <input type="text" name="remarks" class="form-control" style="height:38px;">
                </div>
              </div>
              <div class="d-flex justify-content-end">
                <button type="submit" class="btn-action">Upload File</button>
              </div>
            </form>
          @endif
        @endcan

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 5: Officer Endorsement for each BQ/INV File            --}}
{{-- BQ endorsement: document_info, date, remarks.               --}}
{{-- INV endorsement: above + amount, eds_no, payment_status.    --}}
{{-- Only shown when at least one BQ/INV file exists.            --}}
{{-- ============================================================ --}}
@if ($project->bqInvFiles->isNotEmpty())
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">
          <span class="me-3">3</span> TM: BQ/INVOICE Endorsement
        </h3>

        @php
          $bqFiles  = $project->bqInvFiles->where('payment_type', 'BQ')->sortBy('file_number');
          $invFiles = $project->bqInvFiles->where('payment_type', 'INV')->sortBy('file_number');
        @endphp

        {{-- BQ Section --}}
        <h4 class="fw-semibold mb-2 mt-1">BQ</h4>
        @if ($bqFiles->isEmpty())
          <p class="text-muted small mb-3">—</p>
        @else
          @foreach ($bqFiles as $bqInvFile)
          @php $endorsement = $bqInvFile->bqEndorsement; @endphp
          <div class="border rounded p-3 mb-3" x-data="{ editing: false }">
            {{-- File header: title, badge, download + edit buttons --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <strong>File {{ $bqInvFile->file_number }} &mdash; {{ $bqInvFile->document_info }}</strong>
                <span class="text-muted ms-2">({{ $bqInvFile->payment_type }})</span>
                @if ($endorsement)
                  <span class="badge bg-success ms-1">&#10003; Endorsed</span>
                @endif
              </div>
              <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('projects.download', ['project' => $project, 'path' => $bqInvFile->file_path]) }}"
                   class="btn-action" style="font-size:0.8rem; padding:0.3rem 0.7rem; line-height:1; display:inline-flex; align-items:center; gap:4px;">
                  <i class="ti-download"></i> Download
                </a>
                @role('officer|admin')
                <button type="button" class="btn-action"
                        style="font-size:0.8rem; padding:0.3rem 0.7rem; line-height:1; display:inline-flex; align-items:center;"
                        x-on:click="editing = !editing"
                        x-text="editing ? 'Cancel' : 'Edit'">
                </button>
                @endrole
              </div>
            </div>

            @role('officer|admin')
            {{-- Read-only BQ endorsement summary --}}
            @if ($endorsement)
            <div x-show="!editing" x-cloak>
              <div class="row">
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Document Info</div>
                  <div class="fw-semibold">{{ $endorsement->document_info }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Date</div>
                  <div class="fw-semibold">{{ $endorsement->date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Remarks</div>
                  <div class="fw-semibold">{{ $endorsement->remarks ?? '—' }}</div>
                </div>
              </div>
            </div>
            @endif
            {{-- BQ edit form --}}
            <div @if ($endorsement) x-show="editing" x-cloak @endif>
              <form action="{{ route('projects.bq-inv-files.endorse', [$project, $bqInvFile]) }}" method="POST">
                @csrf
                <div class="row mb-2 align-items-end">
                  <div class="col-md-4 mb-2">
                    <label class="form-label text-muted small">Document Info <span class="text-danger">*</span></label>
                    <input type="text" name="document_info" class="form-control" style="height:38px;"
                           value="{{ old('document_info', $endorsement?->document_info) }}" required>
                  </div>
                  <div class="col-md-4 mb-2">
                    <label class="form-label text-muted small">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" style="height:38px;"
                           value="{{ old('date', $endorsement?->date?->format('Y-m-d')) }}" required>
                  </div>
                  <div class="col-md-4 mb-2">
                    <label class="form-label text-muted small">Remarks</label>
                    <input type="text" name="remarks" class="form-control" style="height:38px;"
                           value="{{ old('remarks', $endorsement?->remarks) }}">
                  </div>
                </div>
                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn-action">
                    {{ $endorsement ? 'Update' : 'Save Endorsement' }}
                  </button>
                </div>
              </form>
            </div>
            @else
            {{-- Contractor: read-only BQ endorsement --}}
            @if ($endorsement)
              <div class="row">
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Document Info</div>
                  <div class="fw-semibold">{{ $endorsement->document_info }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Date</div>
                  <div class="fw-semibold">{{ $endorsement->date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Remarks</div>
                  <div class="fw-semibold">{{ $endorsement->remarks ?? '—' }}</div>
                </div>
              </div>
            @else
              <p class="text-muted small mb-0">Pending officer endorsement.</p>
            @endif
            @endrole
          </div>
          @endforeach
        @endif

        {{-- INV Section --}}
        <h4 class="fw-semibold mb-2 mt-3">INV</h4>
        @if ($invFiles->isEmpty())
          <p class="text-muted small mb-3">—</p>
        @else
          @foreach ($invFiles as $bqInvFile)
          @php $endorsement = $bqInvFile->invEndorsement; @endphp
          <div class="border rounded p-3 mb-3" x-data="{ editing: false }">
            {{-- File header --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <strong>File {{ $bqInvFile->file_number }} &mdash; {{ $bqInvFile->document_info }}</strong>
                <span class="text-muted ms-2">({{ $bqInvFile->payment_type }})</span>
                @if ($endorsement)
                  <span class="badge bg-success ms-1">&#10003; Endorsed</span>
                @endif
              </div>
              <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('projects.download', ['project' => $project, 'path' => $bqInvFile->file_path]) }}"
                   class="btn-action" style="font-size:0.8rem; padding:0.3rem 0.7rem; line-height:1; display:inline-flex; align-items:center; gap:4px;">
                  <i class="ti-download"></i> Download
                </a>
                @role('officer|admin')
                <button type="button" class="btn-action"
                        style="font-size:0.8rem; padding:0.3rem 0.7rem; line-height:1; display:inline-flex; align-items:center;"
                        x-on:click="editing = !editing"
                        x-text="editing ? 'Cancel' : 'Edit'">
                </button>
                @endrole
              </div>
            </div>

            @role('officer|admin')
            @if ($endorsement)
            <div x-show="!editing" x-cloak>
              <div class="row">
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Document Info</div>
                  <div class="fw-semibold">{{ $endorsement->document_info }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Date</div>
                  <div class="fw-semibold">{{ $endorsement->date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Amount (RM)</div>
                  <div class="fw-semibold">{{ $endorsement->amount ? number_format($endorsement->amount, 2) : '—' }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">EDS No</div>
                  <div class="fw-semibold">{{ $endorsement->eds_no ?? '—' }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Payment Status</div>
                  <div class="fw-semibold">{{ ucfirst($endorsement->payment_status ?? '—') }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Remarks</div>
                  <div class="fw-semibold">{{ $endorsement->remarks ?? '—' }}</div>
                </div>
              </div>
            </div>
            @endif
            <div @if ($endorsement) x-show="editing" x-cloak @endif>
              <form action="{{ route('projects.bq-inv-files.endorse', [$project, $bqInvFile]) }}" method="POST">
                @csrf
                <div class="row mb-2 align-items-end">
                  <div class="col-md-4 mb-2">
                    <label class="form-label text-muted small">Document Info <span class="text-danger">*</span></label>
                    <input type="text" name="document_info" class="form-control" style="height:38px;"
                           value="{{ old('document_info', $endorsement?->document_info) }}" required>
                  </div>
                  <div class="col-md-4 mb-2">
                    <label class="form-label text-muted small">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" style="height:38px;"
                           value="{{ old('date', $endorsement?->date?->format('Y-m-d')) }}" required>
                  </div>
                  <div class="col-md-4 mb-2">
                    <label class="form-label text-muted small">Amount (RM)</label>
                    <input type="number" name="amount" step="0.01" min="0" class="form-control" style="height:38px;"
                           value="{{ old('amount', $endorsement?->amount) }}">
                  </div>
                </div>
                <div class="row mb-2 align-items-end">
                  <div class="col-md-4 mb-2">
                    <label class="form-label text-muted small">EDS No</label>
                    <input type="text" name="eds_no" class="form-control" style="height:38px;"
                           value="{{ old('eds_no', $endorsement?->eds_no) }}">
                  </div>
                  <div class="col-md-4 mb-2">
                    <label class="form-label text-muted small">Payment Status</label>
                    <select name="payment_status" class="form-control" style="height:38px;">
                      <option value="">-- Select --</option>
                      @foreach (['paid', 'outstanding', 'waived'] as $opt)
                        <option value="{{ $opt }}" {{ ($endorsement?->payment_status ?? '') === $opt ? 'selected' : '' }}>
                          {{ ucfirst($opt) }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4 mb-2">
                    <label class="form-label text-muted small">Remarks</label>
                    <input type="text" name="remarks" class="form-control" style="height:38px;"
                           value="{{ old('remarks', $endorsement?->remarks) }}">
                  </div>
                </div>
                <div class="d-flex justify-content-end">
                  <button type="submit" class="btn-action">
                    {{ $endorsement ? 'Update' : 'Save Endorsement' }}
                  </button>
                </div>
              </form>
            </div>
            @else
            {{-- Contractor read-only --}}
            @if ($endorsement)
              <div class="row">
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Document Info</div>
                  <div class="fw-semibold">{{ $endorsement->document_info }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Date</div>
                  <div class="fw-semibold">{{ $endorsement->date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Amount (RM)</div>
                  <div class="fw-semibold">{{ $endorsement->amount ? number_format($endorsement->amount, 2) : '—' }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">EDS No</div>
                  <div class="fw-semibold">{{ $endorsement->eds_no ?? '—' }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Payment Status</div>
                  <div class="fw-semibold">{{ ucfirst($endorsement->payment_status ?? '—') }}</div>
                </div>
                <div class="col-md-4 mb-2">
                  <div class="text-muted small">Remarks</div>
                  <div class="fw-semibold">{{ $endorsement->remarks ?? '—' }}</div>
                </div>
              </div>
            @else
              <p class="text-muted small mb-0">Pending officer endorsement.</p>
            @endif
            @endrole
          </div>
          @endforeach
        @endif

      </div>
    </div>
  </div>
</div>
@endif

{{-- ============================================================ --}}
{{-- STEP 6: Wayleave Received (Contractor) + Officer Endorsement --}}
{{-- Contractor uploads wayleave file per PBT (up to 3 PBTs).   --}}
{{-- Officer overwrites the file — endorsement_remarks is        --}}
{{-- auto-set to "Endorsed" in the service layer, not manually.  --}}
{{-- Both actions are in this same step section.                 --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">
          <span class="me-3">4</span> Wayleave Received (KUTT/PBT)
          @if ($project->wayleavePhbts->count() > 0)
            <span class="badge bg-success ms-2">&#10003; {{ $project->wayleavePhbts->count() }} PBT(s) added</span>
          @endif
        </h3>

        {{-- List existing PBTs with officer endorsement sub-form --}}
        @foreach ($project->wayleavePhbts as $pbt)
          <div class="border rounded p-3 mb-3" x-data="{ endorsing: false, fileName: 'No file chosen', replacing: false, replaceFileName: 'No file chosen' }">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <strong>{{ $pbt->pbt_number }} &mdash; {{ $pbt->pbt_name === 'Others' ? $pbt->pbt_name_other : str_replace('_', ' ', $pbt->pbt_name) }}</strong>
                <div class="text-muted small mt-1">Received: {{ $pbt->wayleave_received_date?->format('d M Y') ?? '—' }}</div>
                @if ($pbt->endorsed_by)
                  <div class="text-success small">&#10003; Endorsed by {{ $pbt->endorsedBy?->name }}</div>
                @endif
              </div>
              <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('projects.download', ['project' => $project, 'path' => $pbt->wayleave_file]) }}"
                   class="btn-action" style="font-size:0.8rem; padding:0.3rem 0.7rem; line-height:1; display:inline-flex; align-items:center; gap:4px;">
                  <i class="ti-download"></i> Wayleave File
                </a>
                @role('officer|admin')
                <button type="button" class="btn-action"
                        style="font-size:0.8rem; padding:0.3rem 0.7rem; line-height:1; display:inline-flex; align-items:center;"
                        x-on:click="endorsing = !endorsing"
                        x-text="endorsing ? 'Cancel' : '{{ $pbt->endorsed_by ? 'Replace Endorsed' : 'Add Endorsed' }}'">
                </button>
                @endrole
                @role('contractor')
                @if (!$pbt->endorsed_by)
                <button type="button" class="btn-action"
                        style="font-size:0.8rem; padding:0.3rem 0.7rem; line-height:1; display:inline-flex; align-items:center;"
                        x-on:click="replacing = !replacing"
                        x-text="replacing ? 'Cancel' : 'Replace File'">
                </button>
                @endif
                @endrole
              </div>
            </div>

            {{-- Officer section (embedded in Step 6): replace file with endorsed version --}}
            @role('officer|admin')
            <div x-show="endorsing" x-cloak class="mt-2 pt-2 border-top">
              <form action="{{ route('projects.wayleave-pbts.endorse', [$project, $pbt]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="form-label text-muted small mb-1">
                  {{ $pbt->endorsed_by ? 'Replace with Updated Endorsed File (PDF)' : 'Upload Endorsed Wayleave File (PDF) *' }}
                </label>
                <div class="d-flex align-items-center" style="gap:8px;">
                  <input type="file" name="wayleave_file" id="wayleave_file_{{ $pbt->id }}" accept=".pdf" style="display:none;"
                         x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'"
                         {{ !$pbt->endorsed_by ? 'required' : '' }}>
                  <label for="wayleave_file_{{ $pbt->id }}" class="btn-action mb-0"
                         style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                    <i class="ti-upload"></i> Choose File
                  </label>
                  <span x-text="fileName" class="text-muted" style="font-size:0.8rem; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                  <button type="submit" class="btn-action" style="display:inline-flex; align-items:center;">
                    {{ $pbt->endorsed_by ? 'Replace File' : 'Upload & Endorse' }}
                  </button>
                </div>
                <div class="text-muted" style="font-size:0.7rem; margin-top:0.1rem;">
                  Uploading replaces the wayleave file and marks it as "Endorsed" automatically.
                </div>
              </form>
            </div>
            @endrole

            {{-- Contractor: replace file before endorsement --}}
            @role('contractor')
            @if (!$pbt->endorsed_by)
            <div x-show="replacing" x-cloak class="mt-2 pt-2 border-top">
              <form action="{{ route('projects.wayleave-pbts.replace', [$project, $pbt]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="form-label text-muted small mb-1">Replace Wayleave File (PDF) *</label>
                <div class="d-flex align-items-center" style="gap:8px;">
                  <input type="file" name="wayleave_file" id="replace_wayleave_file_{{ $pbt->id }}" accept=".pdf" style="display:none;" required
                         x-on:change="replaceFileName = $event.target.files[0]?.name ?? 'No file chosen'">
                  <label for="replace_wayleave_file_{{ $pbt->id }}" class="btn-action mb-0"
                         style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                    <i class="ti-upload"></i> Choose File
                  </label>
                  <span x-text="replaceFileName" class="text-muted" style="font-size:0.8rem; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                  <button type="submit" class="btn-action" style="display:inline-flex; align-items:center;">Replace File</button>
                </div>
              </form>
            </div>
            @endif
            @endrole

          </div>
        @endforeach

        {{-- Add new PBT form (up to 3) — available to anyone with update permission --}}
        @can('update', $project)
          @php $nextPbt = ['PBT1','PBT2','PBT3'][$project->wayleavePhbts->count()] ?? null; @endphp
          @if ($nextPbt)
            <form action="{{ route('projects.wayleave-pbts.store', $project) }}" method="POST" enctype="multipart/form-data"
                  x-data="{ pbtName: '', wayleaveFileName: 'No file chosen' }">
              @csrf
              @if ($project->wayleavePhbts->count() > 0)
                <hr class="my-3">
              @endif
              <p class="text-muted small mb-3">Add {{ $nextPbt }}</p>
              <input type="hidden" name="pbt_number" value="{{ $nextPbt }}">
              <div class="row mb-2 align-items-start">
                <div class="col-md-4 mb-2">
                  <label class="form-label text-muted small">PBT Name <span class="text-danger">*</span></label>
                  <select name="pbt_name" class="form-control" style="height:38px;" x-model="pbtName" required>
                    <option value="">-- Select PBT --</option>
                    @foreach (['MBKT','MPK','MDS','MDB','MPD','JKR_HT','JKR_KN','JKR_DN','JKR_KT','JKR_KM','JKR_ST','Others'] as $opt)
                      <option value="{{ $opt }}">{{ str_replace('_', ' ', $opt) }}</option>
                    @endforeach
                  </select>
                  <div x-show="pbtName === 'Others'" x-cloak class="mt-2">
                    <label class="form-label text-muted small">Specify PBT Name <span class="text-danger">*</span></label>
                    <input type="text" name="pbt_name_other" class="form-control" style="height:38px;" :required="pbtName === 'Others'">
                  </div>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label text-muted small">Wayleave Received Date <span class="text-danger">*</span></label>
                  <input type="date" name="wayleave_received_date" class="form-control" style="height:38px;" required>
                </div>
                <div class="col-md-4 mb-2">
                  <label class="form-label text-muted small">Wayleave File (PDF) <span class="text-danger">*</span></label>
                  <div class="d-flex align-items-center" style="gap:8px;">
                    <input type="file" name="wayleave_file" id="wayleave_file_{{ $nextPbt }}" accept=".pdf" style="display:none;" required
                           x-on:change="wayleaveFileName = $event.target.files[0]?.name ?? 'No file chosen'">
                    <label for="wayleave_file_{{ $nextPbt }}" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                      <i class="ti-upload"></i> Choose File
                    </label>
                    <span x-text="wayleaveFileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-center">
                <button type="submit" class="btn-action">Add {{ $nextPbt }}</button>
              </div>
            </form>
          @elseif ($project->wayleavePhbts->count() >= 3)
            <p class="text-muted small mb-0 mt-2">Maximum of 3 PBTs reached.</p>
          @endif
        @endcan

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 7: Officer Records FI and Deposit Payment per PBT      --}}
{{-- Separate from Step 6 so timeline can detect completion       --}}
{{-- independently. Uses the wayleave_payments table.            --}}
{{-- Only shown when at least one PBT exists.                    --}}
{{-- ============================================================ --}}
@if ($project->wayleavePhbts->count() > 0)
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">
          <span class="me-3">5</span> TM: Wayleave Payment Details (FI &amp; Deposit)
        </h3>

        @foreach ($project->wayleavePhbts as $pbt)
          @php $payment = $pbt->payment; @endphp
          <div class="border rounded p-3 mb-3">

            {{-- PBT header --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <strong>{{ $pbt->pbt_number }} &mdash; {{ $pbt->pbt_name === 'Others' ? $pbt->pbt_name_other : str_replace('_', ' ', $pbt->pbt_name) }}</strong>
                @if ($payment)
                  <span class="badge bg-success ms-2">&#10003; Recorded</span>
                @endif
              </div>
              @role('officer|admin')
                @if ($payment)
                  {{-- Edit button rendered via Alpine below --}}
                @endif
              @endrole
            </div>

            @role('officer|admin')
            <div x-data="{ editing: false }">

              {{-- Read-only summary (shown when payment exists and not editing) --}}
              @if ($payment)
                <div x-show="!editing">
                  <div class="row mb-1">
                    <div class="col-md-3 mb-2">
                      <div class="text-muted small">FI Payment</div>
                      <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $payment->fi_payment ?? '—')) }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                      <div class="text-muted small">FI EDS No</div>
                      <div class="fw-semibold">{{ $payment->fi_payment === 'required' ? ($payment->fi_eds_no ?? '—') : '—' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                      <div class="text-muted small">FI Application Date</div>
                      <div class="fw-semibold">{{ $payment->fi_payment === 'required' ? ($payment->fi_application_date?->format('d M Y') ?? '—') : '—' }}</div>
                    </div>
                    <div class="col-md-3 mb-2"></div>
                    <div class="col-md-3 mb-2">
                      <div class="text-muted small">Deposit Payment</div>
                      <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $payment->deposit_payment ?? '—')) }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                      <div class="text-muted small">Deposit EDS No</div>
                      <div class="fw-semibold">{{ $payment->deposit_payment === 'required' ? ($payment->deposit_eds_no ?? '—') : '—' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                      <div class="text-muted small">Deposit Type</div>
                      <div class="fw-semibold">{{ $payment->deposit_payment === 'required' ? ($payment->deposit_payment_type ?? '—') : '—' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                      <div class="text-muted small">Deposit Application Date</div>
                      <div class="fw-semibold">{{ $payment->deposit_payment === 'required' ? ($payment->deposit_application_date?->format('d M Y') ?? '—') : '—' }}</div>
                    </div>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button type="button" class="btn-action" x-on:click="editing = true">Edit</button>
                  </div>
                </div>
              @endif

              {{-- Form (always visible when no payment yet; toggled by Edit when payment exists) --}}
              <div @if($payment) x-show="editing" x-cloak @endif>
                <form action="{{ route('projects.wayleave-payments.store', $project) }}" method="POST"
                      x-data="{
                        fiPayment: '{{ old('fi_payment', $payment?->fi_payment ?? '') }}',
                        depositPayment: '{{ old('deposit_payment', $payment?->deposit_payment ?? '') }}'
                      }">
                  @csrf
                  <input type="hidden" name="wayleave_pbt_id" value="{{ $pbt->id }}">
                  {{-- FI row --}}
                  <div class="row mb-2 align-items-end">
                    <div class="col-md-3 mb-2">
                      <label class="form-label text-muted small">FI Payment</label>
                      <select name="fi_payment" class="form-control" style="height:38px;" x-model="fiPayment">
                        <option value="">-- Select --</option>
                        @foreach (['required', 'not_required', 'waived'] as $opt)
                          <option value="{{ $opt }}" {{ ($payment?->fi_payment ?? '') === $opt ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $opt)) }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-3 mb-2" x-show="fiPayment === 'required'" x-cloak>
                      <label class="form-label text-muted small">FI EDS No</label>
                      <input type="text" name="fi_eds_no" class="form-control" style="height:38px;"
                             value="{{ old('fi_eds_no', $payment?->fi_eds_no) }}">
                    </div>
                    <div class="col-md-3 mb-2" x-show="fiPayment === 'required'" x-cloak>
                      <label class="form-label text-muted small">FI Application Date</label>
                      <input type="date" name="fi_application_date" class="form-control" style="height:38px;"
                             value="{{ old('fi_application_date', $payment?->fi_application_date?->format('Y-m-d')) }}">
                    </div>
                  </div>
                  {{-- Deposit row --}}
                  <div class="row mb-2 align-items-end">
                    <div class="col-md-3 mb-2">
                      <label class="form-label text-muted small">Deposit Payment</label>
                      <select name="deposit_payment" class="form-control" style="height:38px;" x-model="depositPayment">
                        <option value="">-- Select --</option>
                        @foreach (['required', 'not_required', 'waived'] as $opt)
                          <option value="{{ $opt }}" {{ ($payment?->deposit_payment ?? '') === $opt ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $opt)) }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-3 mb-2" x-show="depositPayment === 'required'" x-cloak>
                      <label class="form-label text-muted small">Deposit EDS No</label>
                      <input type="text" name="deposit_eds_no" class="form-control" style="height:38px;"
                             value="{{ old('deposit_eds_no', $payment?->deposit_eds_no) }}">
                    </div>
                    <div class="col-md-2 mb-2" x-show="depositPayment === 'required'" x-cloak>
                      <label class="form-label text-muted small">Deposit Type</label>
                      <select name="deposit_payment_type" class="form-control" style="height:38px;">
                        <option value="">--</option>
                        <option value="BG" {{ ($payment?->deposit_payment_type ?? '') === 'BG' ? 'selected' : '' }}>BG</option>
                        <option value="BD" {{ ($payment?->deposit_payment_type ?? '') === 'BD' ? 'selected' : '' }}>BD</option>
                      </select>
                    </div>
                    <div class="col-md-4 mb-2" x-show="depositPayment === 'required'" x-cloak>
                      <label class="form-label text-muted small">Deposit Application Date</label>
                      <input type="date" name="deposit_application_date" class="form-control" style="height:38px;"
                             value="{{ old('deposit_application_date', $payment?->deposit_application_date?->format('Y-m-d')) }}">
                    </div>
                  </div>
                  <div class="d-flex justify-content-end gap-2">
                    @if ($payment)
                      <button type="button" class="btn-action" style="background:#6c757d;"
                              x-on:click="editing = false">Cancel</button>
                    @endif
                    <button type="submit" class="btn-action">{{ $payment ? 'Update' : 'Save' }}</button>
                  </div>
                </form>
              </div>

            </div>
            @else
            {{-- Contractor: read-only view --}}
            @if ($payment)
              <div class="row mb-1">
                <div class="col-md-3 mb-2">
                  <div class="text-muted small">FI Payment</div>
                  <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $payment->fi_payment ?? '—')) }}</div>
                </div>
                <div class="col-md-3 mb-2">
                  <div class="text-muted small">FI EDS No</div>
                  <div class="fw-semibold">{{ $payment->fi_payment === 'required' ? ($payment->fi_eds_no ?? '—') : '—' }}</div>
                </div>
                <div class="col-md-3 mb-2">
                  <div class="text-muted small">FI Application Date</div>
                  <div class="fw-semibold">{{ $payment->fi_payment === 'required' ? ($payment->fi_application_date?->format('d M Y') ?? '—') : '—' }}</div>
                </div>
                <div class="col-md-3 mb-2"></div>
                <div class="col-md-3 mb-2">
                  <div class="text-muted small">Deposit Payment</div>
                  <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $payment->deposit_payment ?? '—')) }}</div>
                </div>
                <div class="col-md-3 mb-2">
                  <div class="text-muted small">Deposit EDS No</div>
                  <div class="fw-semibold">{{ $payment->deposit_payment === 'required' ? ($payment->deposit_eds_no ?? '—') : '—' }}</div>
                </div>
                <div class="col-md-3 mb-2">
                  <div class="text-muted small">Deposit Type</div>
                  <div class="fw-semibold">{{ $payment->deposit_payment === 'required' ? ($payment->deposit_payment_type ?? '—') : '—' }}</div>
                </div>
                <div class="col-md-3 mb-2">
                  <div class="text-muted small">Deposit Application Date</div>
                  <div class="fw-semibold">{{ $payment->deposit_payment === 'required' ? ($payment->deposit_application_date?->format('d M Y') ?? '—') : '—' }}</div>
                </div>
              </div>
            @else
              <p class="text-muted small mb-0">Pending officer payment details.</p>
            @endif
            @endrole
          </div>
        @endforeach

      </div>
    </div>
  </div>
</div>
@endif

{{-- ============================================================ --}}
{{-- STEP 8: Permit Submission to KUTT                           --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">
          <span class="me-3">6</span> Permit Submission to KUTT
          @if ($project->permitSubmission)
            <span class="badge bg-success ms-2">&#10003; Submitted</span>
          @endif
        </h3>

        @if ($project->permitSubmission)
        <div x-data="{ editing: false, fileName: 'No file chosen' }">
          <div x-show="!editing" x-cloak>
            <div class="row mb-2">
              <div class="col-md-3">
                <div class="text-muted small">Submit Date</div>
                <div>{{ $project->permitSubmission->submit_date?->format('d M Y') }}</div>
              </div>
              <div class="col-md-4">
                <div class="text-muted small">Submission File</div>
                <a href="{{ route('projects.download', ['project' => $project, 'path' => $project->permitSubmission->submission_file]) }}"
                   class="btn-action mt-1"><i class="ti-download"></i> Download</a>
              </div>
            </div>
            @can('update', $project)
            <div class="d-flex justify-content-end mt-2">
              <button type="button" class="btn-action" x-on:click="editing = true">Edit</button>
            </div>
            @endcan
          </div>
          @can('update', $project)
          <div x-show="editing" x-cloak>
            <form action="{{ route('projects.permit-submission.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row align-items-start mb-2">
                <div class="col-md-3">
                  <label class="form-label text-muted small">Submit Date</label>
                  <input type="date" name="submit_date" class="form-control" style="height:38px;"
                         value="{{ old('submit_date', $project->permitSubmission->submit_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                  <label class="form-label text-muted small">Replace Submission File (PDF)</label>
                  <div class="d-flex align-items-center" style="gap:8px;">
                    <input type="file" name="submission_file" id="submission_file_edit" accept=".pdf" style="display:none;"
                           x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                    <label for="submission_file_edit" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                      <i class="ti-upload"></i> Choose File
                    </label>
                    <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                        x-on:click="editing = false">Cancel</button>
                <button type="submit" class="btn-action">Update</button>
              </div>
            </form>
          </div>
          @endcan
        </div>
        @else
        @can('update', $project)
        <form action="{{ route('projects.permit-submission.store', $project) }}" method="POST" enctype="multipart/form-data"
              x-data="{ fileName: 'No file chosen' }">
          @csrf
          <div class="row align-items-start mb-2">
            <div class="col-md-3">
              <label class="form-label text-muted small">Submit Date *</label>
              <input type="date" name="submit_date" class="form-control" style="height:38px;"
                     value="{{ old('submit_date') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted small">Submission File (PDF) *</label>
              <div class="d-flex align-items-center" style="gap:8px;">
                <input type="file" name="submission_file" id="submission_file_new" accept=".pdf" style="display:none;" required
                       x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                <label for="submission_file_new" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                  <i class="ti-upload"></i> Choose File
                </label>
                <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-action">Submit</button>
          </div>
        </form>
        @endcan
        @endif

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 9: Permit Received                                      --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">
          <span class="me-3">7</span> Permit Received
          @if ($project->permitReceived)
            <span class="badge bg-success ms-2">&#10003; Recorded</span>
          @endif
        </h3>

        @if ($project->permitReceived)
        <div x-data="{ editing: false, fileName: 'No file chosen' }">
          <div x-show="!editing" x-cloak>
            <div class="row mb-2">
              <div class="col-md-3">
                <div class="text-muted small">Permit Received Date</div>
                <div>{{ $project->permitReceived->permit_received_date?->format('d M Y') }}</div>
              </div>
              <div class="col-md-4">
                <div class="text-muted small">Permit File</div>
                <a href="{{ route('projects.download', ['project' => $project, 'path' => $project->permitReceived->permit_file]) }}"
                   class="btn-action mt-1"><i class="ti-download"></i> Download</a>
              </div>
            </div>
            @can('update', $project)
            <div class="d-flex justify-content-end mt-2">
              <button type="button" class="btn-action" x-on:click="editing = true">Edit</button>
            </div>
            @endcan
          </div>
          @can('update', $project)
          <div x-show="editing" x-cloak>
            <form action="{{ route('projects.permit-received.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row align-items-start mb-2">
                <div class="col-md-3">
                  <label class="form-label text-muted small">Permit Received Date</label>
                  <input type="date" name="permit_received_date" class="form-control" style="height:38px;"
                         value="{{ old('permit_received_date', $project->permitReceived->permit_received_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                  <label class="form-label text-muted small">Replace Permit File (PDF)</label>
                  <div class="d-flex align-items-center" style="gap:8px;">
                    <input type="file" name="permit_file" id="permit_file_edit" accept=".pdf" style="display:none;"
                           x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                    <label for="permit_file_edit" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                      <i class="ti-upload"></i> Choose File
                    </label>
                    <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                        x-on:click="editing = false">Cancel</button>
                <button type="submit" class="btn-action">Update</button>
              </div>
            </form>
          </div>
          @endcan
        </div>
        @else
        @can('update', $project)
        <form action="{{ route('projects.permit-received.store', $project) }}" method="POST" enctype="multipart/form-data"
              x-data="{ fileName: 'No file chosen' }">
          @csrf
          <div class="row align-items-start mb-2">
            <div class="col-md-3">
              <label class="form-label text-muted small">Permit Received Date *</label>
              <input type="date" name="permit_received_date" class="form-control" style="height:38px;"
                     value="{{ old('permit_received_date') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted small">Permit File (PDF) *</label>
              <div class="d-flex align-items-center" style="gap:8px;">
                <input type="file" name="permit_file" id="permit_file_new" accept=".pdf" style="display:none;" required
                       x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                <label for="permit_file_new" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                  <i class="ti-upload"></i> Choose File
                </label>
                <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-action">Save</button>
          </div>
        </form>
        @endcan
        @endif

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 10: Work Notices                                        --}}
{{-- Gambar (site photos) removed from system entirely.           --}}
{{-- Only Notis Mula Kerja and Notis Siap Kerja.                  --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">
          <span class="me-3">8</span> Work Notices
          @if ($project->workNotice)
            <span class="badge bg-success ms-2">&#10003; Uploaded</span>
          @endif
        </h3>

        @if ($project->workNotice)
        <div x-data="{ editing: false }">
          <div x-show="!editing" x-cloak>
            <div class="row mb-2">
              @foreach (['notis_mula_file' => 'Notis Mula Kerja', 'notis_siap_file' => 'Notis Siap Kerja'] as $field => $label)
                <div class="col-md-3">
                  <div class="text-muted small">{{ $label }}</div>
                  @if ($project->workNotice->$field)
                    <a href="{{ route('projects.download', ['project' => $project, 'path' => $project->workNotice->$field]) }}"
                       class="btn-action mt-1"><i class="ti-download"></i> Download</a>
                  @else
                    <div>—</div>
                  @endif
                </div>
              @endforeach
            </div>
            @can('update', $project)
            <div class="d-flex justify-content-end mt-2">
              <button type="button" class="btn-action" x-on:click="editing = true">Edit</button>
            </div>
            @endcan
          </div>
          @can('update', $project)
          <div x-show="editing" x-cloak>
            <form action="{{ route('projects.work-notice.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row mb-2">
                @foreach (['notis_mula_file' => 'Notis Mula Kerja', 'notis_siap_file' => 'Notis Siap Kerja'] as $field => $label)
                  <div class="col-md-4 mb-2" x-data="{ fileName: 'No file chosen' }">
                    <label class="form-label text-muted small">
                      {{ $project->workNotice->$field ? 'Replace ' : 'Upload ' }}{{ $label }} (PDF)
                      @if (!$project->workNotice->$field)<span class="text-danger">*</span>@endif
                    </label>
                    <div class="d-flex align-items-center" style="gap:8px;">
                      <input type="file" name="{{ $field }}" id="wn_edit_{{ $field }}" accept=".pdf" style="display:none;"
                             {{ !$project->workNotice->$field ? 'required' : '' }}
                             x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                      <label for="wn_edit_{{ $field }}" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                        <i class="ti-upload"></i> Choose File
                      </label>
                      <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                    </div>
                  </div>
                @endforeach
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                        x-on:click="editing = false">Cancel</button>
                <button type="submit" class="btn-action">Update</button>
              </div>
            </form>
          </div>
          @endcan
        </div>
        @else
        @can('update', $project)
        <form action="{{ route('projects.work-notice.store', $project) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="row mb-2">
            @foreach (['notis_mula_file' => 'Notis Mula Kerja', 'notis_siap_file' => 'Notis Siap Kerja'] as $field => $label)
              <div class="col-md-4 mb-2" x-data="{ fileName: 'No file chosen' }">
                <label class="form-label text-muted small">{{ $label }} (PDF) *</label>
                <div class="d-flex align-items-center" style="gap:8px;">
                  <input type="file" name="{{ $field }}" id="wn_new_{{ $field }}" accept=".pdf" style="display:none;" required
                         x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                  <label for="wn_new_{{ $field }}" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                    <i class="ti-upload"></i> Choose File
                  </label>
                  <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                </div>
              </div>
            @endforeach
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-action">Upload</button>
          </div>
        </form>
        @endcan
        @endif

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 11: CPC Application                                     --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title">
          <span class="me-3">9</span> CPC Application
          @if ($project->cpcApplication)
            <span class="badge bg-success ms-2">&#10003; Submitted</span>
          @endif
        </h3>

        @if ($project->cpcApplication)
        {{-- Read-only summary + edit toggle --}}
        <div x-data="{ editing: false }">

          {{-- Summary row (hidden when editing) --}}
          <div x-show="!editing" x-cloak>
            <div class="row mb-2">
              <div class="col-md-3">
                <div class="text-muted small">Date Submitted to KUTT</div>
                <div>{{ $project->cpcApplication->date_submit_to_kutt?->format('d M Y') ?? '—' }}</div>
              </div>
              @foreach (['surat_serahan_file' => 'Surat Serahan', 'laporan_bergambar_file' => 'Laporan Bergambar', 'salinan_coa_file' => 'Salinan COA', 'salinan_permit_file' => 'Salinan Permit'] as $field => $label)
                <div class="col-md-2">
                  <div class="text-muted small">{{ $label }}</div>
                  @if ($project->cpcApplication->$field)
                    <a href="{{ route('projects.download', ['project' => $project, 'path' => $project->cpcApplication->$field]) }}"
                       class="btn-action mt-1"><i class="ti-download"></i> Download</a>
                  @else
                    <div>—</div>
                  @endif
                </div>
              @endforeach
            </div>
            @can('update', $project)
            <div class="d-flex justify-content-end mt-2">
              <button type="button" class="btn-action" x-on:click="editing = true">Edit</button>
            </div>
            @endcan
          </div>

          {{-- Edit form (shown when editing) --}}
          @can('update', $project)
          <div x-show="editing" x-cloak>
            <form action="{{ route('projects.cpc-application.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row mb-2">
                <div class="col-md-4 mb-2">
                  <label class="form-label text-muted small">Date Submitted to KUTT</label>
                  <input type="date" name="date_submit_to_kutt" class="form-control" style="height:38px;"
                         value="{{ old('date_submit_to_kutt', $project->cpcApplication->date_submit_to_kutt?->format('Y-m-d')) }}">
                </div>
                @foreach (['surat_serahan_file' => 'Surat Serahan', 'laporan_bergambar_file' => 'Laporan Bergambar', 'salinan_coa_file' => 'Salinan COA', 'salinan_permit_file' => 'Salinan Permit'] as $field => $label)
                  <div class="col-md-4 mb-2" x-data="{ fileName: 'No file chosen' }">
                    <label class="form-label text-muted small">
                      {{ $project->cpcApplication->$field ? 'Replace ' : 'Upload ' }}{{ $label }} (PDF)
                      @if (!$project->cpcApplication->$field)<span class="text-danger">*</span>@endif
                    </label>
                    <div class="d-flex align-items-center" style="gap:8px;">
                      <input type="file" name="{{ $field }}" id="cpc_edit_{{ $field }}" accept=".pdf" style="display:none;"
                             {{ !$project->cpcApplication->$field ? 'required' : '' }}
                             x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                      <label for="cpc_edit_{{ $field }}" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                        <i class="ti-upload"></i> Choose File
                      </label>
                      <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                    </div>
                  </div>
                @endforeach
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                        x-on:click="editing = false">Cancel</button>
                <button type="submit" class="btn-action">Update</button>
              </div>
            </form>
          </div>
          @endcan

        </div>

        @else
        {{-- First-time submission form --}}
        @can('update', $project)
        <form action="{{ route('projects.cpc-application.store', $project) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="row mb-2">
            <div class="col-md-4 mb-2">
              <label class="form-label text-muted small">Date Submitted to KUTT *</label>
              <input type="date" name="date_submit_to_kutt" class="form-control" style="height:38px;"
                     value="{{ old('date_submit_to_kutt') }}" required>
            </div>
            @foreach (['surat_serahan_file' => 'Surat Serahan', 'laporan_bergambar_file' => 'Laporan Bergambar', 'salinan_coa_file' => 'Salinan COA', 'salinan_permit_file' => 'Salinan Permit'] as $field => $label)
              <div class="col-md-4 mb-2" x-data="{ fileName: 'No file chosen' }">
                <label class="form-label text-muted small">{{ $label }} (PDF) *</label>
                <div class="d-flex align-items-center" style="gap:8px;">
                  <input type="file" name="{{ $field }}" id="cpc_new_{{ $field }}" accept=".pdf" style="display:none;" required
                         x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                  <label for="cpc_new_{{ $field }}" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                    <i class="ti-upload"></i> Choose File
                  </label>
                  <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                </div>
              </div>
            @endforeach
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-action">Submit</button>
          </div>
        </form>
        @endcan
        @endif

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 12: CPC Received → Project Completed                   --}}
{{-- Uploading the CPC triggers status → completed.              --}}
{{-- cpc_date field added per CHANGELOG [8].                     --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card {{ $project->cpcReceived ? 'border-success' : '' }}">
      <div class="card-body">
        <h3 class="card-title">
          <span class="me-3">10</span> CPC Received
          @if ($project->cpcReceived)
            <span class="badge bg-success ms-2">&#10003; Project Completed</span>
          @endif
        </h3>

        @if ($project->cpcReceived)
        <div x-data="{ editing: false, fileName: 'No file chosen' }">
          <div x-show="!editing" x-cloak>
            <div class="row mb-2">
              <div class="col-md-3">
                <div class="text-muted small">CPC Date</div>
                <div>{{ $project->cpcReceived->cpc_date?->format('d M Y') ?? '—' }}</div>
              </div>
              <div class="col-md-4">
                <div class="text-muted small">CPC File</div>
                <a href="{{ route('projects.download', ['project' => $project, 'path' => $project->cpcReceived->cpc_file]) }}"
                   class="btn-action mt-1"><i class="ti-download"></i> Download</a>
              </div>
            </div>
            @can('update', $project)
            <div class="d-flex justify-content-end mt-2">
              <button type="button" class="btn-action" x-on:click="editing = true">Edit</button>
            </div>
            @endcan
          </div>
          @can('update', $project)
          <div x-show="editing" x-cloak>
            <form action="{{ route('projects.cpc-received.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row align-items-start mb-2">
                <div class="col-md-3">
                  <label class="form-label text-muted small">CPC Date</label>
                  <input type="date" name="cpc_date" class="form-control" style="height:38px;"
                         value="{{ old('cpc_date', $project->cpcReceived->cpc_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                  <label class="form-label text-muted small">Replace CPC File (PDF)</label>
                  <div class="d-flex align-items-center" style="gap:8px;">
                    <input type="file" name="cpc_file" id="cpc_file_edit" accept=".pdf" style="display:none;"
                           x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                    <label for="cpc_file_edit" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                      <i class="ti-upload"></i> Choose File
                    </label>
                    <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                        x-on:click="editing = false">Cancel</button>
                <button type="submit" class="btn-action">Update</button>
              </div>
            </form>
          </div>
          @endcan
        </div>
        @else
        @can('update', $project)
          <div class="alert alert-info py-2 mb-3">
            Uploading the CPC will mark this project as <strong>Completed</strong>.
          </div>
          <form action="{{ route('projects.cpc-received.store', $project) }}" method="POST" enctype="multipart/form-data"
                x-data="{ fileName: 'No file chosen' }">
            @csrf
            <div class="row align-items-start mb-2">
              <div class="col-md-3">
                <label class="form-label text-muted small">CPC Date *</label>
                <input type="date" name="cpc_date" class="form-control" style="height:38px;"
                       value="{{ old('cpc_date') }}" required>
              </div>
              <div class="col-md-4">
                <label class="form-label text-muted small">CPC File (PDF) *</label>
                <div class="d-flex align-items-center" style="gap:8px;">
                  <input type="file" name="cpc_file" id="cpc_file_new" accept=".pdf" style="display:none;" required
                         x-on:change="fileName = $event.target.files[0]?.name ?? 'No file chosen'">
                  <label for="cpc_file_new" class="btn-action mb-0" style="cursor:pointer; white-space:nowrap; height:38px !important; line-height:38px !important;">
                    <i class="ti-upload"></i> Choose File
                  </label>
                  <span x-text="fileName" class="text-muted" style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-end">
              <button type="submit" class="btn-action">Upload CPC</button>
            </div>
          </form>
        @endcan
        @endif

      </div>
    </div>
  </div>
</div>

@endsection
