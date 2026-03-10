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
{{-- STEP 1: Project Information                                  --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title"><span class="me-3">1</span> Project Information</h5>

        @can('update', $project)
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
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-action">Save Changes</button>
          </div>
        </form>
        @endcan

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 2: BQ/INV Upload                                        --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <span class="me-3">2</span> BQ/INV Upload
          @if ($project->bqInv?->bq_inv_file)
            <span class="badge bg-success ms-2">&#10003; Uploaded</span>
          @endif
        </h5>

        @if ($project->bqInv?->bq_inv_file)
          <div class="mb-3">
            <span class="text-muted small">Current file &mdash; uploaded by {{ $project->bqInv->uploadedBy?->name }}:</span>
            <a href="{{ route('projects.download', ['project' => $project, 'path' => $project->bqInv->bq_inv_file]) }}"
               class="btn-action ms-2">
              <i class="ti-download"></i> Download BQ/INV
            </a>
          </div>
        @endif

        @can('update', $project)
        <form action="{{ route('projects.bq-inv.store', $project) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="row align-items-end mb-2">
            <div class="col-md-6">
              <label class="form-label text-muted small">
                {{ $project->bqInv?->bq_inv_file ? 'Replace BQ/INV File (PDF)' : 'Upload BQ/INV File (PDF) *' }}
              </label>
              <input type="file" name="bq_inv_file" class="form-control" accept=".pdf"
                     {{ !$project->bqInv?->bq_inv_file ? 'required' : '' }}>
            </div>
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-action">
              {{ $project->bqInv?->bq_inv_file ? 'Replace' : 'Upload' }}
            </button>
          </div>
        </form>
        @endcan

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 3: Officer Endorsement + Invoice Payments               --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <span class="me-3">3</span> Officer Endorsement & Invoice Payment
          @if ($project->bqInv?->endorsed_file)
            <span class="badge bg-success ms-2">&#10003; Endorsed</span>
          @endif
        </h5>

        @role('officer|admin')

        <form action="{{ route('projects.bq-inv.endorse', $project) }}" method="POST" enctype="multipart/form-data" class="mb-4"
              x-data="{ paymentStatus: '{{ $project->bqInv?->payment_status ?? '' }}' }">
          @csrf

          @if ($project->bqInv?->endorsed_file)
            <div class="mb-2">
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $project->bqInv->endorsed_file]) }}"
                 class="btn-action"><i class="ti-download"></i> Current Endorsed File</a>
            </div>
          @endif

          {{-- SECTION 1: Endorsement row — half/half --}}
          <div class="d-flex align-items-end gap-3 mb-3">
            <div style="flex:1;">
              <label class="form-label text-muted small">Endorsed File (PDF)</label>
              <input type="file" name="endorsed_file" class="form-control" style="height:38px;" accept=".pdf">
            </div>
            <div style="flex:1;">
              <label class="form-label text-muted small">Payment Status</label>
              <select name="payment_status" class="form-control" style="height:38px;" x-model="paymentStatus">
                <option value="">-- Select --</option>
                <option value="waived"  {{ $project->bqInv?->payment_status === 'waived'  ? 'selected' : '' }}>Waived</option>
                <option value="charged" {{ $project->bqInv?->payment_status === 'charged' ? 'selected' : '' }}>Charged</option>
              </select>
            </div>
          </div>

          {{-- SECTION 2: Invoice Payments — only when Charged --}}
          <div x-show="paymentStatus === 'charged'" x-cloak>
            <hr class="my-3">
            <label class="form-label text-muted small">Invoice Payments</label>

            <table class="table table-borderless table-sm mb-0" style="table-layout:fixed;">
              <colgroup>
                <col style="width:60px;">
                <col>
                <col style="width:150px;">
                <col style="width:130px;">
                <col style="width:180px;">
              </colgroup>
              <thead>
                <tr class="text-muted small">
                  <th></th>
                  <th>EDS No</th>
                  <th class="text-center">Date</th>
                  <th class="text-center">Amount (RM)</th>
                  <th class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach (['INV1', 'INV2', 'INV3'] as $invNumber)
                  @php $inv = $project->invPayments->firstWhere('inv_number', $invNumber); @endphp
                  <tr>
                    <td style="vertical-align:middle; font-size:0.8rem; font-weight:600; color:#6c757d;">{{ $invNumber }}</td>
                    <td>
                      <input type="text" name="inv[{{ $invNumber }}][eds_no]" class="form-control form-control-sm" value="{{ $inv?->eds_no }}">
                    </td>
                    <td>
                      <input type="date" name="inv[{{ $invNumber }}][date]" class="form-control form-control-sm" value="{{ $inv?->date?->format('Y-m-d') }}">
                    </td>
                    <td>
                      <input type="number" name="inv[{{ $invNumber }}][amount]" step="0.01" class="form-control form-control-sm" value="{{ $inv?->amount }}">
                    </td>
                    <td>
                      <select name="inv[{{ $invNumber }}][payment_status]" class="form-control form-control-sm">
                        <option value="">-- Select --</option>
                        <option value="paid"        {{ $inv?->payment_status === 'paid'        ? 'selected' : '' }}>Paid</option>
                        <option value="outstanding" {{ $inv?->payment_status === 'outstanding' ? 'selected' : '' }}>Outstanding</option>
                      </select>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          {{-- Single Save button on the right --}}
          <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn-action">Save</button>
          </div>

        </form>

        @else
        {{-- Contractor read-only view of 3 --}}
        <div class="row">
          <div class="col-md-3">
            <div class="text-muted small">Payment Status</div>
            <div>{{ $project->bqInv?->payment_status ? ucfirst($project->bqInv->payment_status) : '—' }}</div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Endorsed File</div>
            @if ($project->bqInv?->endorsed_file)
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $project->bqInv->endorsed_file]) }}"
                 class="btn-action mt-1"><i class="ti-download"></i> Download</a>
            @else
              <div>—</div>
            @endif
          </div>
        </div>
        @endrole

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 4: Wayleave from KUTT/PBT                               --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <span class="me-3">4</span> Wayleave Received (KUTT/PBT)
          @if ($project->wayleavePhbts->count() > 0)
            <span class="badge bg-success ms-2">&#10003; {{ $project->wayleavePhbts->count() }} PBT(s) added</span>
          @endif
        </h5>

        {{-- List existing PBTs --}}
        @foreach ($project->wayleavePhbts as $pbt)
          <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>{{ $pbt->pbt_number }} &mdash; {{ $pbt->pbt_name === 'Others' ? $pbt->pbt_name_other : str_replace('_', ' ', $pbt->pbt_name) }}</strong>
                <div class="text-muted small mt-1">Received: {{ $pbt->wayleave_received_date?->format('d M Y') ?? '—' }}</div>
              </div>
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $pbt->wayleave_file]) }}"
                 class="btn-action"><i class="ti-download"></i> Wayleave File</a>
            </div>
          </div>
        @endforeach

        {{-- Add new PBT form --}}
        @can('update', $project)
          @php $nextPbt = ['PBT1','PBT2','PBT3'][$project->wayleavePhbts->count()] ?? null; @endphp
          @if ($nextPbt)
          <form action="{{ route('projects.wayleave-pbts.store', $project) }}" method="POST" enctype="multipart/form-data" x-data="{ pbtName: '' }">
            @csrf
            @if ($project->wayleavePhbts->count() > 0)
              <hr class="my-3">
            @endif
            <p class="form-label text-muted small mb-3">Add {{ $nextPbt }}</p>
            <input type="hidden" name="pbt_number" value="{{ $nextPbt }}">
            <div class="d-flex gap-3 mb-3" style="align-items: flex-end;">
              <div style="flex:1;">
                <label class="form-label text-muted small">PBT Name <span class="text-danger">*</span></label>
                <select name="pbt_name" class="form-control" style="height:38px;" x-model="pbtName" required>
                  <option value="">-- Select PBT --</option>
                  @foreach (['MBKT','MPK','MDS','MDB','MPD','JKR_HT','JKR_KN','JKR_DN','JKR_KT','JKR_KM','JKR_ST','Others'] as $opt)
                    <option value="{{ $opt }}">{{ str_replace('_', ' ', $opt) }}</option>
                  @endforeach
                </select>
                <div x-show="pbtName === 'Others'" x-cloak class="mt-2">
                  <label class="form-label text-muted small">Specify PBT Name <span class="text-danger">*</span></label>
                  <input type="text" name="pbt_name_other" class="form-control" :required="pbtName === 'Others'">
                </div>
              </div>
              <div style="flex:1;">
                <label class="form-label text-muted small">Wayleave Received Date <span class="text-danger">*</span></label>
                <input type="date" name="wayleave_received_date" class="form-control" style="height:38px;" required>
              </div>
              <div style="flex:1;">
                <label class="form-label text-muted small">Wayleave File (PDF) <span class="text-danger">*</span></label>
                <input type="file" name="wayleave_file" class="form-control" style="height:38px;" accept=".pdf" required>
              </div>
            </div>
            <div class="d-flex justify-content-center">
              <button type="submit" class="btn-action">Add {{ $nextPbt }}</button>
            </div>
          </form>
          @else
            <p class="text-muted small mb-0">Maximum of 3 PBTs reached.</p>
          @endif
        @endcan

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 5: Officer Endorsement per PBT                          --}}
{{-- ============================================================ --}}
@if ($project->wayleavePhbts->count() > 0)
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <span class="me-3">5</span> Officer Endorsement per PBT
        </h5>

        @foreach ($project->wayleavePhbts as $pbt)
          <div class="border rounded p-3 mb-3">
            <div class="d-flex align-items-center gap-2 mb-3">
              <label class="form-label text-muted small mb-0">{{ $pbt->pbt_number }} &mdash; {{ $pbt->pbt_name === 'Others' ? $pbt->pbt_name_other : str_replace('_', ' ', $pbt->pbt_name) }}</label>
            </div>

            @role('officer|admin')
            <form action="{{ route('projects.wayleave-pbts.endorse', [$project, $pbt]) }}" method="POST" enctype="multipart/form-data">
              @csrf
              {{-- Row 1 --}}
              <div class="row mb-2 align-items-end">
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">{{ $pbt->endorsed_file ? 'Replace Endorsed File (PDF)' : 'Endorsed File (PDF)' }}</label>
                  <input type="file" name="endorsed_file" class="form-control" style="height:38px;" accept=".pdf">
                </div>
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">FI Payment</label>
                  <select name="fi_payment" class="form-control" style="height:38px;">
                    <option value="">-- Select --</option>
                    @foreach (['required','not_required','waived'] as $opt)
                      <option value="{{ $opt }}" {{ $pbt->fi_payment === $opt ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$opt)) }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">FI EDS No</label>
                  <input type="text" name="fi_eds_no" class="form-control" style="height:38px;" value="{{ $pbt->fi_eds_no }}">
                </div>
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">FI Date</label>
                  <input type="date" name="fi_date" class="form-control" style="height:38px;" value="{{ $pbt->fi_date?->format('Y-m-d') }}">
                </div>
              </div>
              {{-- Row 2 --}}
              <div class="row mb-2 align-items-end">
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">Deposit Payment</label>
                  <select name="deposit_payment" class="form-control" style="height:38px;">
                    <option value="">-- Select --</option>
                    @foreach (['required','not_required','waived'] as $opt)
                      <option value="{{ $opt }}" {{ $pbt->deposit_payment === $opt ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$opt)) }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">Deposit EDS No</label>
                  <input type="text" name="deposit_eds_no" class="form-control" style="height:38px;" value="{{ $pbt->deposit_eds_no }}">
                </div>
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">Deposit Type</label>
                  <select name="deposit_payment_type" class="form-control" style="height:38px;">
                    <option value="">--</option>
                    <option value="BG" {{ $pbt->deposit_payment_type === 'BG' ? 'selected' : '' }}>BG</option>
                    <option value="BD" {{ $pbt->deposit_payment_type === 'BD' ? 'selected' : '' }}>BD</option>
                  </select>
                </div>
                <div class="col-md-3 mb-2">
                  <label class="form-label text-muted small">Deposit Date</label>
                  <input type="date" name="deposit_date" class="form-control" style="height:38px;" value="{{ $pbt->deposit_date?->format('Y-m-d') }}">
                </div>
              </div>
              {{-- Row 3: download endorsed file + save button --}}
              <div class="d-flex justify-content-between align-items-center mt-2">
                <div>
                  @if ($pbt->endorsed_file)
                    <a href="{{ route('projects.download', ['project' => $project, 'path' => $pbt->endorsed_file]) }}"
                       class="btn-action"><i class="ti-download"></i> Download Endorsed File</a>
                  @endif
                </div>
                <button type="submit" class="btn-action">Save</button>
              </div>
            </form>
            @else
            {{-- Contractor read-only view of 5 --}}
            <div class="row text-muted small">
              <div class="col-md-3">FI Payment: <strong>{{ $pbt->fi_payment ? ucfirst(str_replace('_',' ',$pbt->fi_payment)) : '—' }}</strong></div>
              <div class="col-md-3">Deposit: <strong>{{ $pbt->deposit_payment ? ucfirst(str_replace('_',' ',$pbt->deposit_payment)) : '—' }}</strong></div>
              <div class="col-md-3">
                @if ($pbt->endorsed_file)
                  <a href="{{ route('projects.download', ['project' => $project, 'path' => $pbt->endorsed_file]) }}"
                     class="btn-action"><i class="ti-download"></i> Endorsed File</a>
                @else
                  Endorsed File: —
                @endif
              </div>
            </div>
            @endrole
          </div>
        @endforeach

      </div>
    </div>
  </div>
</div>
@endif

{{-- ============================================================ --}}
{{-- STEP 6: Permit Submission                                    --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <span class="me-3">6</span> Permit Submission to KUTT
          @if ($project->permitSubmission)
            <span class="badge bg-success ms-2">&#10003; Submitted</span>
          @endif
        </h5>

        @if ($project->permitSubmission)
          <div class="row mb-3">
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
        @endif

        @can('update', $project)
        <form action="{{ route('projects.permit-submission.store', $project) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="row align-items-end mb-2">
            <div class="col-md-3">
              <label class="form-label text-muted small">Submit Date {{ !$project->permitSubmission ? '*' : '' }}</label>
              <input type="date" name="submit_date" class="form-control" style="height:38px;"
                     value="{{ old('submit_date', $project->permitSubmission?->submit_date?->format('Y-m-d')) }}"
                     {{ !$project->permitSubmission ? 'required' : '' }}>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted small">
                {{ $project->permitSubmission ? 'Replace Submission File (PDF)' : 'Submission File (PDF) *' }}
              </label>
              <input type="file" name="submission_file" class="form-control" style="height:38px;" accept=".pdf"
                     {{ !$project->permitSubmission ? 'required' : '' }}>
            </div>
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-action">
              {{ $project->permitSubmission ? 'Update' : 'Submit' }}
            </button>
          </div>
        </form>
        @endcan

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 7: Permit Received                                      --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <span class="me-3">7</span> Permit Received
          @if ($project->permitReceived)
            <span class="badge bg-success ms-2">&#10003; Recorded</span>
          @endif
        </h5>

        @if ($project->permitReceived)
          <div class="row mb-3">
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
        @endif

        @can('update', $project)
        <form action="{{ route('projects.permit-received.store', $project) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="row align-items-end mb-2">
            <div class="col-md-3">
              <label class="form-label text-muted small">Permit Received Date {{ !$project->permitReceived ? '*' : '' }}</label>
              <input type="date" name="permit_received_date" class="form-control" style="height:38px;"
                     value="{{ old('permit_received_date', $project->permitReceived?->permit_received_date?->format('Y-m-d')) }}"
                     {{ !$project->permitReceived ? 'required' : '' }}>
            </div>
            <div class="col-md-4">
              <label class="form-label text-muted small">
                {{ $project->permitReceived ? 'Replace Permit File (PDF)' : 'Permit File (PDF) *' }}
              </label>
              <input type="file" name="permit_file" class="form-control" style="height:38px;" accept=".pdf"
                     {{ !$project->permitReceived ? 'required' : '' }}>
            </div>
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-action">
              {{ $project->permitReceived ? 'Update' : 'Save' }}
            </button>
          </div>
        </form>
        @endcan

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 8: Work Notices + Site Photos                           --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <span class="me-3">8</span> Work Notices & Site Photos
          @if ($project->workNotice)
            <span class="badge bg-success ms-2">&#10003; Uploaded</span>
          @endif
        </h5>

        @if ($project->workNotice)
          <div class="row mb-3">
            @foreach (['notis_mula_file' => 'Notis Mula', 'notis_siap_file' => 'Notis Siap', 'gambar_file' => 'Gambar (combined PDF)'] as $field => $label)
              <div class="col-md-4">
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
        @endif

        @can('update', $project)
        <form action="{{ route('projects.work-notice.store', $project) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="row mb-2">
            @foreach (['notis_mula_file' => 'Notis Mula', 'notis_siap_file' => 'Notis Siap', 'gambar_file' => 'Gambar (combined PDF)'] as $field => $label)
              <div class="col-md-4 mb-2">
                <label class="form-label text-muted small">
                  {{ $project->workNotice ? 'Replace ' : '' }}{{ $label }} (PDF)
                </label>
                <input type="file" name="{{ $field }}" class="form-control" accept=".pdf">
              </div>
            @endforeach
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn-action">
              {{ $project->workNotice ? 'Update' : 'Upload' }}
            </button>
          </div>
        </form>
        @endcan

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 9: CPC Application                                      --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <span class="me-3">9</span> CPC Application
          @if ($project->cpcApplication)
            <span class="badge bg-success ms-2">&#10003; Submitted</span>
          @endif
        </h5>

        @if ($project->cpcApplication)
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="text-muted small">Date Submitted to KUTT</div>
              <div>{{ $project->cpcApplication->date_submit_to_kutt?->format('d M Y') ?? '—' }}</div>
            </div>
            @foreach (['surat_serahan_file' => 'Surat Serahan', 'laporan_bergambar_file' => 'Laporan Bergambar', 'salinan_coa_file' => 'Salinan COA', 'salinan_permit_file' => 'Salinan Permit'] as $field => $label)
              <div class="col-md-2">
                <div class="text-muted small">{{ $label }}</div>
                @if ($project->cpcApplication->$field)
                  <a href="{{ route('projects.download', ['project' => $project, 'path' => $project->cpcApplication->$field]) }}"
                     class="btn-action mt-1"><i class="ti-download"></i>Download</a>
                @else
                  <div>—</div>
                @endif
              </div>
            @endforeach
          </div>
        @endif

        @can('update', $project)
        <form action="{{ route('projects.cpc-application.store', $project) }}" method="POST" enctype="multipart/form-data">
          @csrf
          {{-- Row 1: Date + Surat Serahan + Laporan Bergambar --}}
          <div class="row mb-2 align-items-end">
            <div class="col-md-4 mb-2">
              <label class="form-label text-muted small">Date Submitted to KUTT</label>
              <input type="date" name="date_submit_to_kutt" class="form-control" style="height:38px;"
                     value="{{ old('date_submit_to_kutt', $project->cpcApplication?->date_submit_to_kutt?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label text-muted small">Surat Serahan (PDF)</label>
              <input type="file" name="surat_serahan_file" class="form-control" style="height:38px;" accept=".pdf">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label text-muted small">Laporan Bergambar (PDF)</label>
              <input type="file" name="laporan_bergambar_file" class="form-control" style="height:38px;" accept=".pdf">
            </div>
          </div>
          {{-- Row 2: Salinan COA + Salinan Permit + Save button --}}
          <div class="row mb-2 align-items-end">
            <div class="col-md-4 mb-2">
              <label class="form-label text-muted small">Salinan COA (PDF)</label>
              <input type="file" name="salinan_coa_file" class="form-control" style="height:38px;" accept=".pdf">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label text-muted small">Salinan Permit (PDF)</label>
              <input type="file" name="salinan_permit_file" class="form-control" style="height:38px;" accept=".pdf">
            </div>
            <div class="col-md-4 mb-2 d-flex justify-content-end align-items-end">
              <button type="submit" class="btn-action">
                {{ $project->cpcApplication ? 'Update' : 'Submit' }}
              </button>
            </div>
          </div>
        </form>
        @endcan

      </div>
    </div>
  </div>
</div>

{{-- ============================================================ --}}
{{-- STEP 10: CPC Received → Project Completed                    --}}
{{-- ============================================================ --}}
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card {{ $project->cpcReceived ? 'border-success' : '' }}">
      <div class="card-body">
        <h5 class="card-title">
          <span class="me-3">10</span> CPC Received
          @if ($project->cpcReceived)
            <span class="badge bg-success ms-2">&#10003; Project Completed</span>
          @endif
        </h5>

        @if ($project->cpcReceived)
          <div class="mb-3">
            <div class="text-muted small">CPC File</div>
            <a href="{{ route('projects.download', ['project' => $project, 'path' => $project->cpcReceived->cpc_file]) }}"
               class="btn-action mt-1"><i class="ti-download"></i> Download CPC</a>
          </div>
        @endif

        @can('update', $project)
          @if (!$project->cpcReceived)
            <div class="alert alert-info py-2 mb-3">
              Uploading the CPC will mark this project as <strong>Completed</strong>.
            </div>
          @endif
          <form action="{{ route('projects.cpc-received.store', $project) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row align-items-end mb-2">
              <div class="col-md-4">
                <label class="form-label text-muted small">
                  {{ $project->cpcReceived ? 'Replace CPC File (PDF)' : 'CPC File (PDF) *' }}
                </label>
                <input type="file" name="cpc_file" class="form-control" style="height:38px;" accept=".pdf"
                       {{ !$project->cpcReceived ? 'required' : '' }}>
              </div>
            </div>
            <div class="d-flex justify-content-end">
              <button type="submit" class="btn-action">
                {{ $project->cpcReceived ? 'Update' : 'Upload CPC' }}
              </button>
            </div>
          </form>
        @endcan

      </div>
    </div>
  </div>
</div>

@endsection