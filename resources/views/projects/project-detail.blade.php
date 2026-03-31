@extends('layouts.dashboard')

@section('title', 'Project Detail')

@push('page-styles')
<style>
  .content-wrapper, .footer { background-color: #f0f2f5 !important; }
</style>
@endpush

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
            <h4 class="mb-1 text-muted fw-semibold">{{ $project->project_desc }}</h4>
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
              <span class="badge bg-primary" style="font-size:0.75rem; padding:0.4rem 0.75rem;">In Progress</span>
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
<div class="row" id="section-1">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">1</span> NF: Project Information</h3>

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
                <div class="text-muted small fw-bold">PBT Ref No</div>
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
                <div class="text-muted small fw-bold">Application Date</div>
                <div class="text-muted" style="font-size:0.7rem;">(Registration Date at KUTT/BKI/KUP/PBT)</div>
                <div>{{ $project->application_date?->format('d/m/Y') ?? '—' }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">PIC Name</div>
                <div>{{ $project->pic_name ?: '—' }}</div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="text-muted small fw-bold">Payment to KUTT/BKI/KUP/PBT</div>
                <div>{{ $project->payment_to_pbt ? ucfirst(str_replace('_', ' ', $project->payment_to_pbt)) : '—' }}</div>
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
              @php
                $nodesJson = $nodes->map(fn($n) => [
                    'id'        => $n->id,
                    'acronym'   => $n->acronym,
                    'full_name' => $n->full_name,
                    'nd'        => $n->nd,
                ])->toJson();
              @endphp
              {{-- x-data on the row so ND State + TM Node share scope without breaking column flow --}}
              <div class="row"
                   x-data="{
                     ndState: '{{ old('nd_state', $project->nd_state) }}',
                     selectedNode: '{{ old('node_id', $project->node_id) }}',
                     allNodes: {{ $nodesJson }},
                     get ndKey() {
                       const ndMap = {
                         'ND_TRG': 'TRG', 'ND_PHG': 'PHG', 'ND_KEL': 'KEL',
                         'ND_JS': 'JS', 'ND_JU': 'JU', 'ND_KD_PL': 'KD/PL',
                         'ND_KL': 'KL', 'ND_MK': 'MK', 'ND_MSC': 'MSC',
                         'ND_NS': 'NS', 'ND_PG': 'PP', 'ND_PJ': 'PJ',
                         'ND_PRK': 'PRK', 'ND_SABAH': 'SABAH', 'ND_SARAWAK': 'SARAWAK',
                         'ND_SB': 'SB', 'ND_ST': 'ST',
                         'NO_TRG': 'TRG', 'NO_PHG': 'PHG', 'NO_KEL': 'KEL',
                         'NO_JS': 'JS', 'NO_JU': 'JU', 'NO_KD_PL': 'KD/PL',
                         'NO_KL': 'KL', 'NO_MK': 'MK', 'NO_MSC': 'MSC',
                         'NO_NS': 'NS', 'NO_PG': 'PP', 'NO_PJ': 'PJ',
                         'NO_PRK': 'PRK', 'NO_SABAH': 'SABAH', 'NO_SARAWAK': 'SARAWAK',
                         'NO_SB': 'SB', 'NO_ST': 'ST',
                       };
                       return ndMap[this.ndState] ?? null;
                     },
                     get filteredNodes() {
                       if (!this.ndState) return this.allNodes;
                       const nd = this.ndKey;
                       const filtered = nd ? this.allNodes.filter(n => n.nd === nd) : this.allNodes;
                       // Always keep the currently selected node in the list so x-model
                       // retains the saved value on load, even if nd column is null or mismatched.
                       if (this.selectedNode && !filtered.some(n => String(n.id) === String(this.selectedNode))) {
                         const current = this.allNodes.find(n => String(n.id) === String(this.selectedNode));
                         if (current) filtered.unshift(current);
                       }
                       return filtered;
                     },
                     onNdChange() {
                       // Only clear node when user explicitly changes ND state and node no longer matches.
                       const nd = this.ndKey;
                       const valid = nd ? this.allNodes.filter(n => n.nd === nd) : this.allNodes;
                       const stillValid = valid.some(n => String(n.id) === String(this.selectedNode));
                       if (!stillValid) this.selectedNode = '';
                     }
                   }">
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">PBT Ref No</label>
                  <input type="text" autocomplete="off" name="ref_no" class="form-control" style="height:38px;" value="{{ old('ref_no', $project->ref_no) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">LOR No</label>
                  <input type="text" autocomplete="off" name="lor_no" class="form-control" style="height:38px;" value="{{ old('lor_no', $project->lor_no) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Project No</label>
                  <input type="text" autocomplete="off" name="project_no" class="form-control" style="height:38px;" value="{{ old('project_no', $project->project_no) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Project Description <span class="text-danger">*</span></label>
                  <textarea autocomplete="off" name="project_desc" class="form-control" style="height:38px; resize:none;" rows="1" required>{{ old('project_desc', $project->project_desc) }}</textarea>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">ND State <span class="text-danger">*</span></label>
                  <select name="nd_state" class="form-control" style="height:38px;" required
                          x-model="ndState" @change="onNdChange()">
                    <optgroup label="ND">
                      <option value="ND_JS">ND JS</option>
                      <option value="ND_JU">ND JU</option>
                      <option value="ND_KD_PL">ND KD/PL</option>
                      <option value="ND_KEL">ND KEL</option>
                      <option value="ND_KL">ND KL</option>
                      <option value="ND_MK">ND MK</option>
                      <option value="ND_MSC">ND MSC</option>
                      <option value="ND_NS">ND NS</option>
                      <option value="ND_PG">ND PG</option>
                      <option value="ND_PHG">ND PHG</option>
                      <option value="ND_PJ">ND PJ</option>
                      <option value="ND_PRK">ND PRK</option>
                      <option value="ND_SABAH">ND SABAH</option>
                      <option value="ND_SARAWAK">ND SARAWAK</option>
                      <option value="ND_SB">ND SB</option>
                      <option value="ND_ST">ND ST</option>
                      <option value="ND_TRG">ND TRG</option>
                    </optgroup>
                    <optgroup label="NO">
                      <option value="NO_JS">NO JS</option>
                      <option value="NO_JU">NO JU</option>
                      <option value="NO_KD_PL">NO KD/PL</option>
                      <option value="NO_KEL">NO KEL</option>
                      <option value="NO_KL">NO KL</option>
                      <option value="NO_MK">NO MK</option>
                      <option value="NO_MSC">NO MSC</option>
                      <option value="NO_NS">NO NS</option>
                      <option value="NO_PG">NO PG</option>
                      <option value="NO_PHG">NO PHG</option>
                      <option value="NO_PJ">NO PJ</option>
                      <option value="NO_PRK">NO PRK</option>
                      <option value="NO_SABAH">NO SABAH</option>
                      <option value="NO_SARAWAK">NO SARAWAK</option>
                      <option value="NO_SB">NO SB</option>
                      <option value="NO_ST">NO ST</option>
                      <option value="NO_TRG">NO TRG</option>
                    </optgroup>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">TM Node</label>
                  <input type="hidden" name="node_id" :value="selectedNode">
                  {{-- Searchable typeahead for TM Node --}}
                  <div x-data="{
                         nodeSearch: filteredNodes.find(n => String(n.id) === String(selectedNode))
                                       ? (filteredNodes.find(n => String(n.id) === String(selectedNode)).acronym + ' — ' + filteredNodes.find(n => String(n.id) === String(selectedNode)).full_name)
                                       : (allNodes.find(n => String(n.id) === String(selectedNode))
                                           ? (allNodes.find(n => String(n.id) === String(selectedNode)).acronym + ' — ' + allNodes.find(n => String(n.id) === String(selectedNode)).full_name)
                                           : ''),
                         nodeOpen: false,
                         get nodeResults() {
                           if (!this.nodeSearch) return filteredNodes;
                           const q = this.nodeSearch.toLowerCase();
                           return filteredNodes.filter(n =>
                             n.acronym.toLowerCase().includes(q) || n.full_name.toLowerCase().includes(q)
                           );
                         },
                         selectNode(node) {
                           selectedNode = node.id;
                           this.nodeSearch = node.id ? node.acronym + ' — ' + node.full_name : '';
                           this.nodeOpen = false;
                         },
                         clearNode() {
                           selectedNode = '';
                           this.nodeSearch = '';
                         }
                       }"
                       style="position:relative;">
                    <input type="text" autocomplete="off" class="form-control" style="height:38px;"
                           placeholder="Search node..."
                           x-model="nodeSearch"
                           @focus="nodeOpen = true"
                           @input="nodeOpen = true; if(!nodeSearch) clearNode()"
                           @click.outside="nodeOpen = false">
                    <div x-show="nodeOpen" x-cloak
                         style="position:absolute; z-index:999; background:#fff; border:1px solid #ced4da;
                                border-radius:4px; width:100%; max-height:200px; overflow-y:auto; top:100%; left:0;">
                      <div @click="selectNode({ id: '', acronym: '', full_name: '' })"
                           style="padding:6px 12px; cursor:pointer; font-size:0.85rem; color:#6c757d;"
                           @mouseover="$el.style.background='#f8f9fa'" @mouseout="$el.style.background=''">
                        — None —
                      </div>
                      <template x-for="node in nodeResults" :key="node.id">
                        <div @click="selectNode(node)"
                             style="padding:6px 12px; cursor:pointer; font-size:0.85rem;"
                             @mouseover="$el.style.background='#f0f4ff'" @mouseout="$el.style.background=''">
                          <span x-text="node.acronym + ' — ' + node.full_name"></span>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Application Date <span class="text-muted" style="font-size:0.7rem;">(Registration Date at KUTT/BKI/KUP/PBT)</span></label>
                  <input type="date" name="application_date" class="form-control" style="height:38px;"
                         value="{{ old('application_date', $project->application_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Payment to KUTT/BKI/KUP/PBT</label>
                  <select name="payment_to_pbt" class="form-control" style="height:38px;">
                    <option value="">-- Select --</option>
                    <option value="charged"      {{ old('payment_to_pbt', $project->payment_to_pbt) === 'charged'      ? 'selected' : '' }}>Charged</option>
                    <option value="waived"       {{ old('payment_to_pbt', $project->payment_to_pbt) === 'waived'       ? 'selected' : '' }}>Waived</option>
                    <option value="not_required" {{ old('payment_to_pbt', $project->payment_to_pbt) === 'not_required' ? 'selected' : '' }}>Not Required</option>
                  </select>
                </div>
                @role('officer|admin')
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Self Applied by TM</label>
                  <select name="self_applied_by_tm" class="form-control" style="height:38px;"
                          @change="selfApplied = $event.target.value === '1'">
                    <option value="0" {{ !$project->self_applied_by_tm ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $project->self_applied_by_tm  ? 'selected' : '' }}>Yes</option>
                  </select>
                </div>
                @php
                  $companiesJson = $companies->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toJson();
                  $selectedCompanyName = $companies->firstWhere('id', old('company_id', $project->company_id))?->name ?? '';
                @endphp
                <div class="col-md-4 mb-3" x-show="!selfApplied" x-cloak>
                  <label class="form-label text-muted small">Company</label>
                  {{-- Searchable typeahead for Company --}}
                  <div x-data="{
                         allCompanies: {{ $companiesJson }},
                         companySearch: '{{ addslashes($selectedCompanyName) }}',
                         selectedCompany: '{{ old('company_id', $project->company_id) }}',
                         companyOpen: false,
                         get companyResults() {
                           if (!this.companySearch) return this.allCompanies;
                           const q = this.companySearch.toLowerCase();
                           return this.allCompanies.filter(c => c.name.toLowerCase().includes(q));
                         },
                         selectCompany(c) {
                           this.selectedCompany = c.id;
                           this.companySearch = c.name;
                           this.companyOpen = false;
                         }
                       }"
                       style="position:relative;">
                    <input type="hidden" name="company_id" :value="selectedCompany">
                    <input type="text" autocomplete="off" class="form-control" style="height:38px;"
                           placeholder="Search company..."
                           x-model="companySearch"
                           @focus="companyOpen = true"
                           @input="companyOpen = true"
                           @click.outside="companyOpen = false">
                    <div x-show="companyOpen && companyResults.length > 0" x-cloak
                         style="position:absolute; z-index:999; background:#fff; border:1px solid #ced4da;
                                border-radius:4px; width:100%; max-height:200px; overflow-y:auto; top:100%; left:0;">
                      <template x-for="c in companyResults" :key="c.id">
                        <div @click="selectCompany(c)"
                             style="padding:6px 12px; cursor:pointer; font-size:0.85rem;"
                             @mouseover="$el.style.background='#f0f4ff'" @mouseout="$el.style.background=''">
                          <span x-text="c.name"></span>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
                @endrole
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">PIC Name</label>
                  <input type="hidden" name="pic_name" value="{{ $project->pic_name }}">
                  <input type="text" class="form-control" style="height:38px;" value="{{ $project->pic_name ?: '—' }}" disabled>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label text-muted small">Remarks</label>
                  <textarea autocomplete="off" name="remarks" class="form-control" style="height:38px; resize:none;" rows="1">{{ old('remarks', $project->remarks) }}</textarea>
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
  <div x-show="open" x-cloak style="position:fixed; inset:0; z-index:9999;">
    <div style="display:flex; align-items:center; justify-content:center; height:100%; pointer-events:none;">
    <div class="card" style="max-width:480px; width:100%; margin:1rem; pointer-events:auto; box-shadow:0 8px 32px rgba(0,0,0,0.18);" @click.outside="open = false">
      <div class="card-body">
        <h5 class="fw-bold mb-3">Cancel Project</h5>
        <form action="{{ route('projects.cancel', $project) }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
            <textarea autocomplete="off" name="cancellation_reason" class="form-control" rows="3" required minlength="5"
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
</div>
@endunless

{{-- ================================================================ --}}
{{-- PROJECT TIMELINE (Read-Only)                                      --}}
{{-- ================================================================ --}}
@php
$tl = $timelineStatus;
$tlLabels = [
    1 => 'NF: Project Info', 2 => 'NF: Upload BOQ/Invoice', 3 => 'TM: BOQ/INV Endorsement and Payment',
    4 => 'NF: Upload Wayleave', 5 => 'TM: Wayleave Endorsement', 6 => 'TM: Update Deposit & FI  Payment (BG/BD) Application Date',
    7 => 'TM: Upload BG/BD Received Date from FINSSO', 8 => 'NF: Document Permit Submission to PBT', 9 => 'NF: Upload Permit Received from PBT',
    10 => 'NF: Notis Mula Kerja', 11 => 'NF: Notis Siap Kerja', 12 => 'NF: Upload Document CPC Submission to PBT', 13 => 'NF: Upload Approved CPC',
];

// Total days: Section 1 date to the last completed section that has a date.
$tlTotalDays = null;
$tlStartDate = $timelineDates[1] ?? null;
if ($tlStartDate) {
    $lastCompletedDate = null;
    foreach (range(13, 1) as $s) {
        if ($tl[$s] && !empty($timelineDates[$s])) {
            $lastCompletedDate = $timelineDates[$s];
            break;
        }
    }
    if ($lastCompletedDate && $lastCompletedDate != $tlStartDate) {
        $tlTotalDays = (int) \Carbon\Carbon::parse($tlStartDate)->diffInDays(\Carbon\Carbon::parse($lastCompletedDate), false);
    }
}
@endphp
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h3 class="card-title mb-0">Project Timeline</h3>
          @if ($tlTotalDays !== null)
            <span style="font-size:0.78rem; font-weight:700; color:#144e90;">
              Total: {{ $tlTotalDays }} day{{ $tlTotalDays === 1 ? '' : 's' }}
            </span>
          @endif
        </div>
        <div class="d-flex align-items-start" style="overflow-x:auto; padding-bottom:0.5rem;">
          @php
            // Build a look-back date map: for each node, find the nearest previous date.
            // Used so lines between waived sections (no date) still show a meaningful diff.
            $tlEffectiveDates = [];
            $lastKnownDate = null;
            for ($s = 1; $s <= 13; $s++) {
                if (!empty($timelineDates[$s])) {
                    $lastKnownDate = $timelineDates[$s];
                }
                $tlEffectiveDates[$s] = $lastKnownDate;
            }
          @endphp
          @foreach (range(1, 13) as $i)
            <div class="d-flex flex-column align-items-center" style="min-width:64px; flex:1;">
              {{-- Date above the node --}}
              <div class="text-center mb-1" style="font-size:0.55rem; color:#6c757d; line-height:1.2; min-height:2.4em; font-weight:600;">
                {{ $timelineDates[$i] ? \Carbon\Carbon::parse($timelineDates[$i])->format('d M Y') : '' }}
              </div>
              <div class="d-flex align-items-center w-100">
                @if ($i > 1)
                  @php
                    // Use effective (look-back) dates so waived gaps still show a count
                    // when the next real date is available.
                    $prevEffective = $tlEffectiveDates[$i - 1] ?? null;
                    $currDate      = $timelineDates[$i] ?? null;
                    $daysDiff = ($prevEffective && $currDate)
                        ? (int) \Carbon\Carbon::parse($prevEffective)->diffInDays(\Carbon\Carbon::parse($currDate), false)
                        : null;
                  @endphp
                  <div style="flex:1; position:relative; height:2px; background:{{ $tl[$i-1] ? '#28a745' : '#dee2e6' }};">
                    @if ($daysDiff !== null)
                      <span style="position:absolute; top:-13px; left:0; transform:translateX(-50%);
                                   font-size:0.5rem; color:#6c757d; white-space:nowrap; font-weight:700;">
                        {{ $daysDiff }} day{{ $daysDiff === 1 ? '' : 's' }}
                      </span>
                    @endif
                  </div>
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

        {{-- Stage brackets — proportional to node count each stage spans --}}
        {{-- Each unit = 1 node width (64px min). Stages share the same scroll container. --}}
        @php
        $dropHeight = '20px';
        $barHeight  = '2px';
        $stages = [
            ['units' => 1, 'sections' => [1],          'label' => 'Stage 1: Registration of Wayleave & Permit Application.'],
            ['units' => 2, 'sections' => [2, 3],        'label' => 'Stage 2: Payment to Koridor Utiliti - KUTT/KUP/BKI.'],
            ['units' => 5, 'sections' => [4, 5, 6, 7, 8], 'label' => 'Stage 3: Wayleave Acquisition, Document Preparation, Preparation of Deposit & FI, and Submission of Documents to PBT.'],
            ['units' => 2, 'sections' => [9, 10],       'label' => 'Stage 4: Project Implementation Upon Permit Approval.'],
            ['units' => 3, 'sections' => [11, 12, 13],  'label' => 'Stage 5: Preparation of Documents for CPC Application.'],
        ];

        // Determine each stage's color:
        // - If ALL stages are completed → all blue
        // - Otherwise → current stage (first incomplete) is blue, everything else is grey
        $allStagesDone = collect($stages)->every(
            fn($stage) => collect($stage['sections'])->every(fn($s) => !empty($tl[$s]))
        );
        $foundCurrent = false;
        foreach ($stages as &$stage) {
            $allDone = collect($stage['sections'])->every(fn($s) => !empty($tl[$s]));
            if ($allStagesDone) {
                $stage['color'] = '#144e90';
            } elseif (!$allDone && !$foundCurrent) {
                $stage['color'] = '#144e90';
                $foundCurrent = true;
            } else {
                $stage['color'] = '#adb5bd';
            }
        }
        unset($stage);
        @endphp
        <div class="d-flex" style="overflow-x:visible; margin-top:4px; gap:6px;">
          @foreach ($stages as $stage)
            @php $stageColor = $stage['color']; @endphp
            <div style="flex:{{ $stage['units'] }}; min-width:{{ $stage['units'] * 64 }}px; display:flex; flex-direction:column; align-items:stretch; padding:0 8px;">
              {{-- U-bracket: two vertical drops + horizontal bar at bottom --}}
              <div style="display:flex; align-items:flex-end; height:{{ $dropHeight }};">
                <div style="width:{{ $barHeight }}; height:100%; background:{{ $stageColor }}; flex-shrink:0;"></div>
                <div style="flex:1;"></div>
                <div style="width:{{ $barHeight }}; height:100%; background:{{ $stageColor }}; flex-shrink:0;"></div>
              </div>
              {{-- Horizontal bar --}}
              <div style="height:{{ $barHeight }}; background:{{ $stageColor }};"></div>
              {{-- Stage label — fixed min-height so all boxes are equal height --}}
              <div class="text-center mt-1"
                   style="font-size:0.68rem; color:#212529 !important; font-weight:400 !important;
                          line-height:1.4; padding:6px 10px; min-height:64px;
                          display:flex; align-items:center; justify-content:center;">
                {{ $stage['label'] }}
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
<div class="row" id="section-2">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">2</span> NF: Upload BOQ/Invoice Files</h3>
        @if($boqHidden)
          <p class="text-muted mb-0"><em>Payment to KUTT/ is {{ ucfirst(str_replace('_', ' ', $project->payment_to_pbt)) }} — BOQ/INV section not applicable.</em></p>
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
                         class="btn-action btn-action-sm"><i class="ti-download me-1"></i>Download</a>
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
                          <input type="text" autocomplete="off" name="document_info" class="form-control form-control-sm" value="{{ $item->document_info }}" required>
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
                          <input type="text" autocomplete="off" name="remarks" class="form-control form-control-sm" value="{{ $item->remarks }}" placeholder="e.g. BOQ/INV No">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label small">Replace File (PDF, max 10MB)</label>
                          <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                            <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                              Choose File
                              <input type="file" name="file" class="d-none" accept="application/pdf" @change="fn = $event.target.files[0]?.name ?? ''">
                            </label>
                            <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px; font-weight:normal;"></span>
                          </div>
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
          <div class="mt-3 text-center" x-data="{ open: false }">
            <button type="button" class="btn-action" x-on:click="open = !open">+ Add New BOQ/INV</button>
            <div x-show="open" x-cloak class="mt-3">
              <form action="{{ route('projects.boq-inv-items.store', $project) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small">Document Info <span class="text-danger">*</span></label>
                    <input type="text" autocomplete="off" name="document_info" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-control form-control-sm" required>
                      <option value="BQ">BQ</option>
                      <option value="INV">INV</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small">Date Received <span class="text-danger">*</span></label>
                    <input type="date" name="date_received" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small">Amount (RM)</label>
                    <input type="number" name="amount" step="0.01" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small">Remarks</label>
                    <input type="text" autocomplete="off" name="remarks" class="form-control form-control-sm" placeholder="BOQ/Invoice No">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small">File (PDF, max 10MB)</label>
                    <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                      <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                        Choose File
                        <input type="file" name="file" class="d-none" accept="application/pdf" @change="fn = $event.target.files[0]?.name ?? ''">
                      </label>
                      <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:120px; font-weight:normal;"></span>
                    </div>
                  </div>
                </div>
                <div class="mt-2 text-center">
                  <button type="submit" class="btn-action btn-action-sm">Save</button>
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
<div class="row" id="section-3">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">3</span> TM: BOQ/Invoice Endorsement and Payment</h3>
        @if($boqHidden)
          <p class="text-muted mb-0"><em>Payment to PBT is {{ ucfirst(str_replace('_', ' ', $project->payment_to_pbt)) }} — BOQ/INV section not applicable.</em></p>
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
                  <th style="font-weight:600; color:#07326A;">EDS Application Date</th>
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
                  <td>{{ $item->type === 'INV' ? ($item->eds_no ?? '—') : '—' }}</td>
                  <td>{{ $item->type === 'INV' ? ($item->eds_application_date?->format('d/m/Y') ?? '—') : '—' }}</td>
                  <td>
                    {{ $item->payment_status ? ucfirst(str_replace('_', ' ', $item->payment_status)) : '—' }}
                  </td>
                  <td>{{ $item->endorsedBy?->name ?? '—' }}</td>
                  <td>{{ $item->remarks ?? '—' }}</td>
                  <td>
                    @if($item->file_path)
                      <a href="{{ route('projects.download', ['project' => $project, 'path' => $item->file_path]) }}"
                         class="btn-action btn-action-sm"><i class="ti-download me-1"></i>Download</a>
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
                          <input type="text" autocomplete="off" name="document_info" class="form-control form-control-sm" value="{{ $item->document_info }}">
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
                        @if($item->type === 'INV')
                        <div class="col-md-4">
                          <label class="form-label small">EDS No</label>
                          <input type="text" autocomplete="off" name="eds_no" class="form-control form-control-sm" value="{{ $item->eds_no }}">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label small">EDS Application Date</label>
                          <input type="date" name="eds_application_date" class="form-control form-control-sm" value="{{ $item->eds_application_date?->format('Y-m-d') }}">
                        </div>
                        @endif
                        <div class="col-md-4">
                          <label class="form-label small">Payment Status</label>
                          <select name="payment_status" class="form-control form-control-sm">
                            <option value="">— Select —</option>
                            <option value="pending_endorsement" {{ $item->payment_status === 'pending_endorsement' ? 'selected' : '' }}>Pending Endorsement</option>
                            <option value="endorsed"            {{ $item->payment_status === 'endorsed'            ? 'selected' : '' }}>Endorsed</option>
                            @if($item->type === 'INV')
                            <option value="endorsed_and_paid"   {{ $item->payment_status === 'endorsed_and_paid'   ? 'selected' : '' }}>Endorsed and Paid</option>
                            @endif
                            <option value="waived"              {{ $item->payment_status === 'waived'              ? 'selected' : '' }}>Waived</option>
                            <option value="cancelled"           {{ $item->payment_status === 'cancelled'           ? 'selected' : '' }}>Cancelled</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <label class="form-label small">Remarks</label>
                          <input type="text" autocomplete="off" name="remarks" class="form-control form-control-sm" value="{{ $item->remarks }}">
                        </div>
                        <div class="col-12">
                          <label class="form-label small">Replace File (overwrites original)</label>
                          <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                            <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                              Choose File
                              <input type="file" name="file" class="d-none" accept="application/pdf" @change="fn = $event.target.files[0]?.name ?? ''">
                            </label>
                            <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px; font-weight:normal;"></span>
                          </div>
                          <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
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
                {{-- Inline endorse row: EDS No, Payment Status, file upload --}}
                <tr x-show="endorsing" x-cloak style="background:#f8f9fa;">
                  <td colspan="11" class="py-3 px-3">
                    <form action="{{ route('projects.boq-inv-items.update', [$project, $item]) }}" method="POST" enctype="multipart/form-data">
                      @csrf
                      <div class="row g-2">
                        @if($item->type === 'INV')
                        <div class="col-md-3">
                          <label class="form-label small">EDS No</label>
                          <input type="text" autocomplete="off" name="eds_no" class="form-control form-control-sm" value="{{ $item->eds_no }}" placeholder="EDS No">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label small">EDS Application Date</label>
                          <input type="date" name="eds_application_date" class="form-control form-control-sm" value="{{ $item->eds_application_date?->format('Y-m-d') }}">
                        </div>
                        @endif
                        <div class="col-md-3">
                          <label class="form-label small">Payment Status</label>
                          <select name="payment_status" class="form-control form-control-sm">
                            <option value="">— Select —</option>
                            <option value="pending_endorsement" {{ $item->payment_status === 'pending_endorsement' ? 'selected' : '' }}>Pending Endorsement</option>
                            <option value="endorsed"            {{ $item->payment_status === 'endorsed'            ? 'selected' : '' }}>Endorsed</option>
                            @if($item->type === 'INV')
                            <option value="endorsed_and_paid"   {{ $item->payment_status === 'endorsed_and_paid'   ? 'selected' : '' }}>Endorsed and Paid</option>
                            @endif
                            <option value="waived"              {{ $item->payment_status === 'waived'              ? 'selected' : '' }}>Waived</option>
                            <option value="cancelled"           {{ $item->payment_status === 'cancelled'           ? 'selected' : '' }}>Cancelled</option>
                          </select>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label small">Upload Endorsed File</label>
                          <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2 mt-1">
                            <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                              Choose File
                              <input type="file" name="file" class="d-none" accept="application/pdf" @change="fn = $event.target.files[0]?.name ?? ''">
                            </label>
                            <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:200px; font-weight:normal;"></span>
                          </div>
                          <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
                        </div>
                      </div>
                      <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn-action btn-action-green">Save Endorsement</button>
                        <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                                x-on:click="endorsing = false">Cancel</button>
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
<div class="row" id="section-4">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">4</span> NF: Wayleave Received</h3>

        @forelse ($project->wayleavePhbts as $pbt)
        <div class="border rounded p-2 mb-2" x-data="{ editing: false, showOther: {{ $pbt->pbt_name === 'Others' ? 'true' : 'false' }} }">
          <div class="d-flex justify-content-between align-items-center">
            <strong class="small">{{ $pbt->pbt_number }} — {{ $pbt->pbt_name === 'Others' ? $pbt->pbt_name_other : str_replace('_', ' ', $pbt->pbt_name) }}</strong>
            @if($pbt->endorsed_by)
              <span class="text-muted small fw-normal">Endorsed by {{ $pbt->endorsedBy?->name }}</span>
            @endif
          </div>

          {{-- Read view --}}
          <div x-show="!editing">
            <div class="d-flex align-items-start justify-content-between mt-2">
              <div class="d-flex align-items-start gap-4">
                <div>
                  <div class="small fw-normal text-muted">Date Received</div>
                  <div class="small">{{ $pbt->wayleave_received_date?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div>
                  <div class="small fw-normal text-muted">Wayleave File</div>
                  @if($pbt->wayleave_file)
                    <a href="{{ route('projects.download', ['project' => $project, 'path' => $pbt->wayleave_file]) }}"
                       class="btn-action btn-action-sm"><i class="ti-download me-1"></i>Download</a>
                  @else
                    <span class="small text-muted">—</span>
                  @endif
                </div>
              </div>
              @can('update', $project)
              <button type="button" class="btn-action btn-action-sm" x-on:click="editing = true">Edit</button>
              @endcan
            </div>
          </div>

          {{-- Edit form --}}
          @can('update', $project)
          <div x-show="editing" x-cloak class="mt-2">
            <form action="{{ route('projects.wayleave-pbts.update', [$project, $pbt]) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              <div class="row g-2">
                @if(!$pbt->endorsed_by)
                {{-- Not yet endorsed: all fields editable --}}
                <div class="col-md-3">
                  <label class="form-label small">PBT Name <span class="text-danger">*</span></label>
                  <select name="pbt_name" class="form-control form-control-sm" required
                          @change="showOther = $event.target.value === 'Others'">
                    @foreach(['MBKT - MAJLIS BANDARAYA KUALA TERENGGANU','MPK - MAJLIS PERBANDARAN KEMAMAN','MDS - MAJLIS PERBANDARAN SETIU','MDB - MAJLIS DAERAH BESUT','MPD - MAJLIS PERBANDARAN DUNGUN','JKR HT - JKR HULU TERENGGANU','JKR KN - JKR KUALA NERUS','JKR DN - JKR DUNGUN',
                    'JKR KT - JKR KUALA TERENGGANU','JKR KM - JKR KEMAMAN','JKR ST - JKR SETIU','Others'] as $pn)
                      <option value="{{ $pn }}" {{ $pbt->pbt_name === $pn ? 'selected' : '' }}>{{ $pn }}</option>
                    @endforeach
                  </select>
                  <input type="text" autocomplete="off" name="pbt_name_other" class="form-control form-control-sm mt-1"
                         placeholder="Specify other PBT name"
                         value="{{ $pbt->pbt_name_other }}"
                         x-show="showOther" x-cloak>
                </div>
                @else
                {{-- Already endorsed: PBT name is locked, pass hidden values through --}}
                <input type="hidden" name="pbt_name" value="{{ $pbt->pbt_name }}">
                @if($pbt->pbt_name === 'Others')
                  <input type="hidden" name="pbt_name_other" value="{{ $pbt->pbt_name_other }}">
                @endif
                @endif

                <div class="col-md-3">
                  <label class="form-label small">Date Received</label>
                  <input type="date" name="wayleave_received_date" class="form-control form-control-sm"
                         value="{{ $pbt->wayleave_received_date?->format('Y-m-d') }}">
                </div>

                @if(!$pbt->endorsed_by)
                {{-- Not yet endorsed: file upload allowed --}}
                <div class="col-md-4">
                  <label class="form-label small">Wayleave File (PDF) — leave blank to keep existing</label>
                  <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                    <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                      Choose File
                      <input type="file" name="wayleave_file" class="d-none" accept="application/pdf"
                             @change="fn = $event.target.files[0]?.name ?? ''">
                    </label>
                    <span class="text-muted small" x-text="fn || 'No file chosen'"
                          style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:140px; font-weight:normal;"></span>
                  </div>
                  <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
                </div>
                @endif
              </div>

              <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn-action btn-action-sm">Save</button>
                <button type="button" class="btn-action btn-action-sm" style="background:#6c757d;"
                        x-on:click="editing = false">Cancel</button>
              </div>
            </form>
          </div>
          @endcan
        </div>
        @empty
          <p class="text-muted">No wayleave PBT records yet.</p>
        @endforelse

        {{-- Add new PBT --}}
        @can('update', $project)
        @if($project->wayleavePhbts->count() < 3)
        <div class="text-center" x-data="{ open: false }">
          <button type="button" class="btn-action" x-on:click="open = !open">+ Add Wayleave PBT</button>
          <div x-show="open" x-cloak class="mt-3">
            <form action="{{ route('projects.wayleave-pbts.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row g-2">
                <div class="col-md-3">
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
                    @foreach(['MBKT - MAJLIS BANDARAYA KUALA TERENGGANU','MPK - MAJLIS PERBANDARAN KEMAMAN','MDS - MAJLIS PERBANDARAN SETIU','MDB - MAJLIS DAERAH BESUT','MPD - MAJLIS PERBANDARAN DUNGUN','JKR HT - JKR HULU TERENGGANU','JKR KN - JKR KUALA NERUS','JKR DN - JKR DUNGUN',
                    'JKR KT - JKR KUALA TERENGGANU','JKR KM - JKR KEMAMAN','JKR ST - JKR SETIU','Others'] as $pn)
                      <option value="{{ $pn }}">{{ $pn }}</option>
                    @endforeach
                  </select>
                  <input type="text" autocomplete="off" name="pbt_name_other" class="form-control form-control-sm mt-1"
                         placeholder="Specify other PBT name" x-show="showOther" x-cloak>
                </div>
                <div class="col-md-3">
                  <label class="form-label small">Date Received</label>
                  <input type="date" name="wayleave_received_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                  <label class="form-label small">Wayleave File (PDF)</label>
                  <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                    <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                      Choose File
                      <input type="file" name="wayleave_file" class="d-none" accept="application/pdf" @change="fn = $event.target.files[0]?.name ?? ''">
                    </label>
                    <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:80px; font-weight:normal;"></span>
                  </div>
                  <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
                </div>
              </div>
              <div class="mt-2 text-center">
                <button type="submit" class="btn-action btn-action-sm">Save</button>
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
<div class="row" id="section-5">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">5</span> TM: Wayleave Endorsement</h3>
        @if($project->wayleavePhbts->isEmpty())
          <p class="text-muted">No wayleave PBT records yet (Section 4 must be filled first).</p>
        @else
          @foreach($project->wayleavePhbts as $pbt)
          <div class="border rounded p-2 mb-2" x-data="{ open: false }">
            <div class="d-flex justify-content-between align-items-center">
              <strong class="small">{{ $pbt->pbt_number }} — {{ str_replace('_', ' ', $pbt->pbt_name === 'Others' ? $pbt->pbt_name_other : $pbt->pbt_name) }}</strong>
              @if($pbt->endorsed_by)
                <span class="text-muted small fw-normal">Endorsed by {{ $pbt->endorsedBy?->name }}</span>
              @else
                <span class="text-muted small fw-normal">Pending Endorsement</span>
              @endif
            </div>
            <div class="d-flex align-items-start justify-content-between mt-2">
              <div class="d-flex align-items-start gap-4">
                <div>
                  <div class="small fw-normal text-muted">Endorsed Date</div>
                  <div class="small">{{ $pbt->endorsed_date?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div>
                  <div class="small fw-normal text-muted">Endorsed File</div>
                  @if($pbt->wayleave_file)
                    <a href="{{ route('projects.download', ['project' => $project, 'path' => $pbt->wayleave_file]) }}"
                       class="btn-action btn-action-sm"><i class="ti-download me-1"></i>Download</a>
                  @else
                    <span class="small text-muted">—</span>
                  @endif
                </div>
              </div>
              @role('officer|admin')
              <button type="button" class="btn-action btn-action-sm" x-on:click="open = !open">
                {{ $pbt->endorsed_by ? 'Edit' : 'Upload Endorsed File' }}
              </button>
              @endrole
            </div>
            @role('officer|admin')
            <div x-show="open" x-cloak class="mt-2">
              <form action="{{ route('projects.wayleave-pbts.endorse', [$project, $pbt]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-2 mb-2">
                  <div class="col-md-3">
                    <label class="form-label small">Endorsed Date</label>
                    <input type="date" name="endorsed_date" class="form-control form-control-sm"
                           value="{{ $pbt->endorsed_date?->format('Y-m-d') }}">
                  </div>
                  <div class="col-md-9">
                    <label class="form-label small">Endorsed File (PDF) <span class="text-danger">*</span></label>
                    <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                      <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                        Choose File
                        <input type="file" name="wayleave_file" class="d-none" accept="application/pdf" required @change="fn = $event.target.files[0]?.name ?? ''">
                      </label>
                      <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:160px; font-weight:normal;"></span>
                    </div>
                    <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
                  </div>
                </div>
                <div class="d-flex gap-2">
                  <button type="submit" class="btn-action btn-action-sm">Save</button>
                  <button type="button" class="btn-action btn-action-sm" style="background:#6c757d; border-color:#6c757d;"
                          x-on:click="open = false">Cancel</button>
                </div>
              </form>
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
<div class="row" id="section-6">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">6</span> TM: Update Deposit & FI Payment (BG/BD) Application Date</h3>
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
                  <th style="font-weight:600; color:#07326A;">EDS Application Date</th>
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
                              x-on:click="editing = !editing">Update</button>
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
                                <input type="text" autocomplete="off" name="fi[eds_no]" class="form-control form-control-sm" value="{{ $paymentFI?->eds_no }}">
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
                                <input type="text" autocomplete="off" name="deposit[eds_no]" class="form-control form-control-sm" value="{{ $paymentDep?->eds_no }}">
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
<div class="row" id="section-7">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">7</span> TM: UPLOAD BG/BD RECEIVED DATE FROM FINSSO</h3>
        @php
          $requiredPayments = $project->wayleavePayments->where('status', 'required');
        @endphp
        @if($requiredPayments->isEmpty())
          <p class="text-muted">No required payments.</p>
        @else
          <div class="table-responsive">
            <table class="table table-borderless mb-0">
              <thead>
                <tr>
                  <th style="font-weight:600; color:#07326A;">PBT</th>
                  <th style="font-weight:600; color:#07326A;">Payment Type</th>
                  <th style="font-weight:600; color:#07326A;">Amount (RM)</th>
                  <th style="font-weight:600; color:#07326A;">EDS No</th>
                  <th style="font-weight:600; color:#07326A;">Method of Payment</th>
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
                         class="btn-action btn-action-sm"><i class="ti-download me-1"></i>Download</a>
                    @else
                      —
                    @endif
                  </td>
                  @role('officer|admin')
                  <td>
                    <button type="button" class="btn-action btn-action-sm"
                            x-on:click="editing = !editing">Update</button>
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
                          <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                            <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                              Choose File
                              <input type="file" name="bg_bd_file" class="d-none" accept="application/pdf" @change="fn = $event.target.files[0]?.name ?? ''">
                            </label>
                            <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px; font-weight:normal;"></span>
                          </div>
                          <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
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
{{-- SECTION 8: DOC PERMIT APPLICATION SUBMISSION TO PBT             --}}
{{-- ================================================================ --}}
<div class="row" id="section-8">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">8</span> NF: UPLOAD PERMIT DOCUMENT SUBMISSION TO PBT</h3>

        {{-- List of existing submissions --}}
        @forelse($project->permitSubmissions as $sub)
        <div x-data="{ editing: false }">
          {{-- Read row --}}
          <div class="d-flex align-items-center gap-4 mb-2" x-show="!editing">
            <div>
              <div class="text-muted small">Submit Date</div>
              <div>{{ $sub->submit_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div>
              <div class="text-muted small">File</div>
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $sub->submission_file]) }}"
                 class="btn-action btn-action-sm"><i class="ti-download me-1"></i>Download</a>
            </div>
            @can('update', $project)
            <div class="ms-auto d-flex gap-2 align-items-center">
              <button type="button" class="btn-action btn-action-sm" x-on:click="editing = true">Edit</button>
              <form action="{{ route('projects.permit-submission.destroy', [$project, $sub]) }}" method="POST"
                    onsubmit="return confirm('Delete this submission? This cannot be undone.')"
                    style="display:contents;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-action btn-action-red btn-action-sm">Delete</button>
              </form>
            </div>
            @endcan
          </div>
          {{-- Inline edit form --}}
          @can('update', $project)
          <div x-show="editing" x-cloak class="mb-3 p-3" style="background:#f8f9fa; border-radius:4px;">
            <form action="{{ route('projects.permit-submission.update', [$project, $sub]) }}" method="POST" enctype="multipart/form-data">
              @csrf @method('PUT')
              <div class="row g-2">
                <div class="col-md-3">
                  <label class="form-label small">Submit Date <span class="text-danger">*</span></label>
                  <input type="date" name="submit_date" class="form-control form-control-sm"
                         value="{{ $sub->submit_date?->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-5">
                  <label class="form-label small">Replace File <span class="text-muted">(leave blank to keep current)</span></label>
                  <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                    <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                      Choose File
                      <input type="file" name="submission_file" class="d-none" accept="application/pdf"
                             @change="fn = $event.target.files[0]?.name ?? ''">
                    </label>
                    <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px; font-weight:normal;"></span>
                  </div>
                  <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
                </div>
              </div>
              <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn-action btn-action-sm">Save</button>
                <button type="button" class="btn-action btn-action-sm" style="background:#6c757d; border-color:#6c757d;"
                        x-on:click="editing = false">Cancel</button>
              </div>
            </form>
          </div>
          @endcan
        </div>
        @empty
        @endforelse

        {{-- Add New button — max 3 --}}
        @can('update', $project)
        @if($project->permitSubmissions->count() < 3)
        <div class="mt-3 text-center" x-data="{ open: false }">
          <button type="button" class="btn-action" x-on:click="open = !open">+ Add New Permit Submission</button>
          <div x-show="open" x-cloak class="mt-3">
            <form action="{{ route('projects.permit-submission.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row g-2">
                <div class="col-md-3">
                  <label class="form-label small">Submit Date <span class="text-danger">*</span></label>
                  <input type="date" name="submit_date" class="form-control form-control-38" required>
                </div>
                <div class="col-md-5">
                  <label class="form-label small">Submission File (PDF) <span class="text-danger">*</span></label>
                  <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                    <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                      Choose File
                      <input type="file" name="submission_file" class="d-none" accept="application/pdf"
                             required @change="fn = $event.target.files[0]?.name ?? ''">
                    </label>
                    <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px; font-weight:normal;"></span>
                  </div>
                  <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
                </div>
              </div>
              <div class="mt-2 text-center">
                <button type="submit" class="btn-action">Save</button>
              </div>
            </form>
          </div>
        </div>
        @endif
        @endcan
        <hr class="mt-3 mb-2" style="opacity:1;">
        {{-- Note + example image side by side --}}
        <div class="d-flex align-items-start gap-4">
          <div class="text-muted small flex-grow-1">
            <strong>NOTA:</strong>
            <ol class="mb-0 ps-3 mt-1">
              <li>SILA HANTAR Serahan Dokumen Lengkap Bagi Permohonan Permit BESERTA BG/BD KEPADA PIHAK PBT.</li>
              <li>PASTIKAN ADA COP TERIMA &amp; TARIKH TERIMA OLEH PBT.</li>
              <li>SILA SCAN &amp; MUAT-NAIK Surat Serahan Dokumen Lengkap Bagi Permohonan Permit.</li>
            </ol>
          </div>
          {{-- Example image panel — $exampleImages['section8'] resolved in ProjectController --}}
          <div class="text-center" style="min-width:130px;">
            @if($exampleImages['section8'])
              {{-- Thumbnail — click opens modal --}}
              <img src="{{ route('example-images.show', 'section8') }}"
                   alt="Contoh dokumen" class="img-thumbnail" style="max-width:120px; cursor:pointer;"
                   data-bs-toggle="modal" data-bs-target="#exampleImageModal8">
              <div class="text-muted" style="font-size:0.7rem; margin-top:3px;">Contoh Dokumen</div>
              {{-- Bootstrap modal for full-size view --}}
              <div class="modal fade" id="exampleImageModal8" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header py-2">
                      <span class="fw-semibold small">Contoh Permohonan Permit</span>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-2">
                      <img src="{{ route('example-images.show', 'section8') }}"
                           alt="Contoh dokumen" class="img-fluid">
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="text-muted" style="font-size:0.7rem;">Tiada contoh</div>
            @endif
            @role('admin|officer')
            <form action="{{ route('example-images.upload', 'section8') }}" method="POST"
                  enctype="multipart/form-data" class="mt-1">
              @csrf
              <label class="btn-action btn-action-sm mb-0" style="cursor:pointer; font-size:0.75rem;">
                {{ $exampleImages['section8'] ? 'Replace' : 'Upload' }}
                <input type="file" name="image" class="d-none" accept="image/*"
                       onchange="this.form.submit()">
              </label>
              <div class="text-muted" style="font-size:0.65rem; margin-top:3px;">JPG, PNG, WebP — max 5MB</div>
            </form>
            @endrole
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 9: PERMIT RECEIVED                                        --}}
{{-- ================================================================ --}}
<div class="row" id="section-9">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">9</span> NF: Upload Permit Received from PBT</h3>

        {{-- List of existing permits received --}}
        @forelse($project->permitReceiveds as $rec)
        <div x-data="{ editing: false }">
          {{-- Read row --}}
          <div class="d-flex align-items-center gap-4 mb-2" x-show="!editing">
            <div>
              <div class="text-muted small">Permit Received Date</div>
              <div>{{ $rec->permit_received_date?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div>
              <div class="text-muted small">PBT Name</div>
              <div>{{ $rec->remarks ?? '—' }}</div>
            </div>
            <div>
              <div class="text-muted small">Permit File</div>
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $rec->permit_file]) }}"
                 class="btn-action btn-action-sm"><i class="ti-download me-1"></i>Download</a>
            </div>
            @can('update', $project)
            <div class="ms-auto d-flex gap-2 align-items-center">
              <button type="button" class="btn-action btn-action-sm" x-on:click="editing = true">Edit</button>
              <form action="{{ route('projects.permit-received.destroy', [$project, $rec]) }}" method="POST"
                    onsubmit="return confirm('Delete this permit record? This cannot be undone.')"
                    style="display:contents;">
                @csrf @method('DELETE')
                <button type="submit" class="btn-action btn-action-red btn-action-sm">Delete</button>
              </form>
            </div>
            @endcan
          </div>
          {{-- Inline edit form --}}
          @can('update', $project)
          <div x-show="editing" x-cloak class="mb-3 p-3" style="background:#f8f9fa; border-radius:4px;">
            <form action="{{ route('projects.permit-received.update', [$project, $rec]) }}" method="POST" enctype="multipart/form-data">
              @csrf @method('PUT')
              <div class="row g-2">
                <div class="col-md-3">
                  <label class="form-label small">Date Received <span class="text-danger">*</span></label>
                  <input type="date" name="permit_received_date" class="form-control form-control-sm"
                         value="{{ $rec->permit_received_date?->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label small">PBT Name</label>
                  <input type="text" autocomplete="off" name="remarks" class="form-control form-control-sm"
                         value="{{ $rec->remarks }}">
                </div>
                <div class="col-md-5">
                  <label class="form-label small">Replace File <span class="text-muted">(leave blank to keep current)</span></label>
                  <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                    <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                      Choose File
                      <input type="file" name="permit_file" class="d-none" accept="application/pdf"
                             @change="fn = $event.target.files[0]?.name ?? ''">
                    </label>
                    <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px; font-weight:normal;"></span>
                  </div>
                  <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
                </div>
              </div>
              <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn-action btn-action-sm">Save</button>
                <button type="button" class="btn-action btn-action-sm" style="background:#6c757d; border-color:#6c757d;"
                        x-on:click="editing = false">Cancel</button>
              </div>
            </form>
          </div>
          @endcan
        </div>
        @empty
        @endforelse

        {{-- Add New button — max 3 --}}
        @can('update', $project)
        @if($project->permitReceiveds->count() < 3)
        <div class="mt-3 text-center" x-data="{ open: false }">
          <button type="button" class="btn-action" x-on:click="open = !open">+ Add New Permit Received</button>
          <div x-show="open" x-cloak class="mt-3">
            <form action="{{ route('projects.permit-received.store', $project) }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row g-2">
                <div class="col-md-3">
                  <label class="form-label small">Date Received <span class="text-danger">*</span></label>
                  <input type="date" name="permit_received_date" class="form-control form-control-38" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label small">PBT Name</label>
                  <input type="text" autocomplete="off" name="remarks" class="form-control form-control-38">
                </div>
                <div class="col-md-5">
                  <label class="form-label small">Permit File (PDF) <span class="text-danger">*</span></label>
                  <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                    <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                      Choose File
                      <input type="file" name="permit_file" class="d-none" accept="application/pdf"
                             required @change="fn = $event.target.files[0]?.name ?? ''">
                    </label>
                    <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px; font-weight:normal;"></span>
                  </div>
                  <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
                </div>
              </div>
              <div class="mt-2 text-center">
                <button type="submit" class="btn-action">Save</button>
              </div>
            </form>
          </div>
        </div>
        @endif
        @endcan
        <hr class="mt-3 mb-2" style="opacity:1;">
        <div class="d-flex align-items-start gap-4">
          <div class="text-muted small flex-grow-1">
            <strong>NOTA:</strong>
            <ol class="mb-0 ps-3 mt-1">
              <li>HARDCOPY PERMIT PERLU DIAMBIL DARIPADA PIHAK PBT.</li>
              <li>SILA SCAN DAN MUAT NAIK KELULUSAN PERMIT.</li>
            </ol>
          </div>
          <div class="text-center" style="min-width:130px;">
            @if($exampleImages['section9'])
              <img src="{{ route('example-images.show', 'section9') }}"
                   alt="Contoh dokumen" class="img-thumbnail" style="max-width:120px; cursor:pointer;"
                   data-bs-toggle="modal" data-bs-target="#exampleImageModal9">
              <div class="text-muted" style="font-size:0.7rem; margin-top:3px;">Contoh Dokumen</div>
              <div class="modal fade" id="exampleImageModal9" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header py-2">
                      <span class="fw-semibold small">Contoh Permit Received</span>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-2">
                      <img src="{{ route('example-images.show', 'section9') }}" alt="Contoh dokumen" class="img-fluid">
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="text-muted" style="font-size:0.7rem;">Tiada contoh</div>
            @endif
            @role('admin|officer')
            <form action="{{ route('example-images.upload', 'section9') }}" method="POST"
                  enctype="multipart/form-data" class="mt-1">
              @csrf
              <label class="btn-action btn-action-sm mb-0" style="cursor:pointer; font-size:0.75rem;">
                {{ $exampleImages['section9'] ? 'Replace' : 'Upload' }}
                <input type="file" name="image" class="d-none" accept="image/*" onchange="this.form.submit()">
              </label>
              <div class="text-muted" style="font-size:0.65rem; margin-top:3px;">JPG, PNG, WebP — max 5MB</div>
            </form>
            @endrole
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 10: NOTIS MULA KERJA                                      --}}
{{-- ================================================================ --}}
<div class="row" id="section-10">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">10</span> NF: Upload Notis Mula Kerja</h3>
        @php $workNotice = $project->workNotice; @endphp
        @if($workNotice?->notis_mula_file)
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="text-muted small">Tarikh Mula Kerja</div>
              <div>{{ $workNotice->tarikh_mula_kerja?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="col-md-3">
              <div class="text-muted small">File</div>
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $workNotice->notis_mula_file]) }}"
                 class="btn-action btn-action-sm"><i class="ti-download me-1"></i>Download</a>
            </div>
          </div>
        @endif
        @can('update', $project)
        <div x-data="{ replacing: {{ $workNotice?->notis_mula_file ? 'false' : 'true' }} }">
          @if($workNotice?->notis_mula_file)
            <div x-show="!replacing" class="mt-2">
              <button type="button" class="btn-action btn-action-sm" x-on:click="replacing = true">Replace</button>
            </div>
          @endif
          <form x-show="replacing" x-cloak action="{{ route('projects.notis-mula.store', $project) }}" method="POST" enctype="multipart/form-data" class="mt-3">
            @csrf
            <div class="row g-2">
              <div class="col-md-3">
                <label class="form-label small">Tarikh Mula Kerja <span class="text-danger">*</span></label>
                <input type="date" name="tarikh_mula_kerja" class="form-control form-control-38"
                       value="{{ old('tarikh_mula_kerja', $workNotice?->tarikh_mula_kerja?->format('Y-m-d')) }}" required>
              </div>
              <div class="col-md-5">
                <label class="form-label small">Notis Mula Kerja (PDF)</label>
                <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                  <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                    Choose File
                    <input type="file" name="notis_mula_file" class="d-none" accept="application/pdf"
                           {{ $workNotice?->notis_mula_file ? '' : 'required' }} @change="fn = $event.target.files[0]?.name ?? ''">
                  </label>
                  <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px; font-weight:normal;"></span>
                </div>
                <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
              </div>
            </div>
            <div class="mt-2 d-flex gap-2 @if(!$workNotice?->notis_mula_file) justify-content-center @endif">
              <button type="submit" class="btn-action">Save</button>
              @if($workNotice?->notis_mula_file)
                <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;" x-on:click="replacing = false">Cancel</button>
              @endif
            </div>
          </form>
        </div>
        @endcan
        <hr class="mt-3 mb-2" style="opacity:1;">
        <div class="d-flex align-items-start gap-4">
          <div class="text-muted small flex-grow-1">
            <strong>NOTA:</strong>
            <ol class="mb-0 ps-3 mt-1">
              <li>SILA HANTAR NOTIS MULA KERJA KEPADA PIHAK PBT.</li>
              <li>PASTIKAN ADA COP TERIMA &amp; TARIKH TERIMA OLEH PBT.</li>
              <li>SEKIRANYA TERDAPAT PERUBAHAN ATAU HALANGAN DI TAPAK, SILA MAKLUM KEPADA PBT SEBELUM KERJA-KERJA DIBUAT.</li>
              <li>SILA AMBIL GAMBAR SEBELUM, SEMASA DAN SELEPAS.</li>
            </ol>
          </div>
          <div class="text-center" style="min-width:130px;">
            @if($exampleImages['section10'])
              <img src="{{ route('example-images.show', 'section10') }}"
                   alt="Contoh dokumen" class="img-thumbnail" style="max-width:120px; cursor:pointer;"
                   data-bs-toggle="modal" data-bs-target="#exampleImageModal10">
              <div class="text-muted" style="font-size:0.7rem; margin-top:3px;">Contoh Dokumen</div>
              <div class="modal fade" id="exampleImageModal10" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header py-2">
                      <span class="fw-semibold small">Contoh Notis Mula Kerja</span>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-2">
                      <img src="{{ route('example-images.show', 'section10') }}" alt="Contoh dokumen" class="img-fluid">
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="text-muted" style="font-size:0.7rem;">Tiada contoh</div>
            @endif
            @role('admin|officer')
            <form action="{{ route('example-images.upload', 'section10') }}" method="POST"
                  enctype="multipart/form-data" class="mt-1">
              @csrf
              <label class="btn-action btn-action-sm mb-0" style="cursor:pointer; font-size:0.75rem;">
                {{ $exampleImages['section10'] ? 'Replace' : 'Upload' }}
                <input type="file" name="image" class="d-none" accept="image/*" onchange="this.form.submit()">
              </label>
              <div class="text-muted" style="font-size:0.65rem; margin-top:3px;">JPG, PNG, WebP — max 5MB</div>
            </form>
            @endrole
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 11: NOTIS SIAP KERJA                                      --}}
{{-- ================================================================ --}}
<div class="row" id="section-11">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">11</span> NF: Upload Notis Siap Kerja</h3>
        @if($workNotice?->notis_siap_file)
          <div class="row mb-3">
            <div class="col-md-3">
              <div class="text-muted small">Tarikh Siap Kerja</div>
              <div>{{ $workNotice->tarikh_siap_kerja?->format('d/m/Y') ?? '—' }}</div>
            </div>
            <div class="col-md-3">
              <div class="text-muted small">File</div>
              <a href="{{ route('projects.download', ['project' => $project, 'path' => $workNotice->notis_siap_file]) }}"
                 class="btn-action btn-action-sm"><i class="ti-download me-1"></i>Download</a>
            </div>
          </div>
        @endif
        @can('update', $project)
        <div x-data="{ replacing: {{ $workNotice?->notis_siap_file ? 'false' : 'true' }} }">
          @if($workNotice?->notis_siap_file)
            <div x-show="!replacing" class="mt-2">
              <button type="button" class="btn-action btn-action-sm" x-on:click="replacing = true">Replace</button>
            </div>
          @endif
          <form x-show="replacing" x-cloak action="{{ route('projects.notis-siap.store', $project) }}" method="POST" enctype="multipart/form-data" class="mt-3">
            @csrf
            <div class="row g-2">
              <div class="col-md-3">
                <label class="form-label small">Tarikh Siap Kerja <span class="text-danger">*</span></label>
                <input type="date" name="tarikh_siap_kerja" class="form-control form-control-38"
                       value="{{ old('tarikh_siap_kerja', $workNotice?->tarikh_siap_kerja?->format('Y-m-d')) }}" required>
              </div>
              <div class="col-md-5">
                <label class="form-label small">Notis Siap Kerja (PDF)</label>
                <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                  <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                    Choose File
                    <input type="file" name="notis_siap_file" class="d-none" accept="application/pdf"
                           {{ $workNotice?->notis_siap_file ? '' : 'required' }} @change="fn = $event.target.files[0]?.name ?? ''">
                  </label>
                  <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px; font-weight:normal;"></span>
                </div>
                <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
              </div>
            </div>
            <div class="mt-2 d-flex gap-2 @if(!$workNotice?->notis_siap_file) justify-content-center @endif">
              <button type="submit" class="btn-action">Save</button>
              @if($workNotice?->notis_siap_file)
                <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;" x-on:click="replacing = false">Cancel</button>
              @endif
            </div>
          </form>
        </div>
        @endcan
        <hr class="mt-3 mb-2" style="opacity:1;">
        <div class="d-flex align-items-start gap-4">
          <div class="text-muted small flex-grow-1">
            <strong>NOTA:</strong>
            <ol class="mb-0 ps-3 mt-1">
              <li>SILA HANTAR NOTIS SIAP KERJA KEPADA PIHAK PBT.</li>
              <li>PASTIKAN ADA COP TERIMA &amp; TARIKH TERIMA OLEH PBT.</li>
              <li>SILA SCAN &amp; MUAT-NAIK SALINAN NOTIS SIAP KERJA.</li>
            </ol>
          </div>
          <div class="text-center" style="min-width:130px;">
            @if($exampleImages['section11'])
              <img src="{{ route('example-images.show', 'section11') }}"
                   alt="Contoh dokumen" class="img-thumbnail" style="max-width:120px; cursor:pointer;"
                   data-bs-toggle="modal" data-bs-target="#exampleImageModal11">
              <div class="text-muted" style="font-size:0.7rem; margin-top:3px;">Contoh Dokumen</div>
              <div class="modal fade" id="exampleImageModal11" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header py-2">
                      <span class="fw-semibold small">Contoh Notis Siap Kerja</span>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-2">
                      <img src="{{ route('example-images.show', 'section11') }}" alt="Contoh dokumen" class="img-fluid">
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="text-muted" style="font-size:0.7rem;">Tiada contoh</div>
            @endif
            @role('admin|officer')
            <form action="{{ route('example-images.upload', 'section11') }}" method="POST"
                  enctype="multipart/form-data" class="mt-1">
              @csrf
              <label class="btn-action btn-action-sm mb-0" style="cursor:pointer; font-size:0.75rem;">
                {{ $exampleImages['section11'] ? 'Replace' : 'Upload' }}
                <input type="file" name="image" class="d-none" accept="image/*" onchange="this.form.submit()">
              </label>
              <div class="text-muted" style="font-size:0.65rem; margin-top:3px;">JPG, PNG, WebP — max 5MB</div>
            </form>
            @endrole
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 12: PERMOHONAN SIJIL PERAKUAN SIAP KERJA (CPC)            --}}
{{-- ================================================================ --}}
<div class="row" id="section-12">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">12</span> NF: Upload Permohonan Sijil Perakuan Siap Kerja (CPC)</h3>
        @php
          $cpcApp = $project->cpcApplication;
          $allUploaded = $cpcApp
            && $cpcApp->surat_serahan_file
            && $cpcApp->laporan_bergambar_file
            && $cpcApp->salinan_coa_file
            && $cpcApp->salinan_permit_file;
        @endphp

        @if($cpcApp)
          <div class="row mb-3">
            <div class="col-md-2">
              <div class="text-muted small fw-normal">Date Submitted to PBT</div>
              <div>{{ $cpcApp->date_submit_to_pbt?->format('d/m/Y') ?? '—' }}</div>
            </div>
            @foreach([
              'surat_serahan_file'    => 'Surat Serahan',
              'laporan_bergambar_file'=> 'Laporan Bergambar',
              'salinan_coa_file'      => 'Salinan COA',
              'salinan_permit_file'   => 'Salinan Permit',
            ] as $col => $label)
              @if($cpcApp->$col)
                <div class="col-md-2">
                  <div class="text-muted small fw-normal">{{ $label }}</div>
                  <a href="{{ route('projects.download', ['project' => $project, 'path' => $cpcApp->$col]) }}"
                     class="btn-action btn-action-sm mt-1"><i class="ti-download me-1"></i>Download</a>
                </div>
              @endif
            @endforeach
          </div>
        @endif

        @can('update', $project)
        {{-- Wrapper div holds x-data so the Delete All form can be a sibling (nested forms are invalid HTML) --}}
        <div x-data="{ replacing: {{ $allUploaded ? 'false' : 'true' }} }" class="mt-3">
        <form action="{{ route('projects.cpc-application.store', $project) }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="row g-3" x-show="replacing">
            <div class="col-md-4">
              <label class="form-label small">Date Submitted to PBT <span class="text-danger">*</span></label>
              <input type="date" name="date_submit_to_pbt" class="form-control form-control-38"
                     value="{{ $cpcApp?->date_submit_to_pbt?->format('Y-m-d') }}" :required="replacing">
            </div>
          </div>
          <div class="row g-3 mt-0" x-show="replacing">
            <div class="col-md-4">
              <label class="form-label small">Surat Serahan</label>
              <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                  Choose File
                  <input type="file" name="surat_serahan_file" class="d-none" accept="application/pdf" @change="fn = $event.target.files[0]?.name ?? ''">
                </label>
                <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:120px; font-weight:normal;"></span>
              </div>
              <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Laporan Bergambar</label>
              <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                  Choose File
                  <input type="file" name="laporan_bergambar_file" class="d-none" accept="application/pdf" @change="fn = $event.target.files[0]?.name ?? ''">
                </label>
                <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:120px; font-weight:normal;"></span>
              </div>
              <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Salinan COA</label>
              <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                  Choose File
                  <input type="file" name="salinan_coa_file" class="d-none" accept="application/pdf" @change="fn = $event.target.files[0]?.name ?? ''">
                </label>
                <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:120px; font-weight:normal;"></span>
              </div>
              <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Salinan Permit</label>
              <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                  Choose File
                  <input type="file" name="salinan_permit_file" class="d-none" accept="application/pdf" @change="fn = $event.target.files[0]?.name ?? ''">
                </label>
                <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:120px; font-weight:normal;"></span>
              </div>
              <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
            </div>
          </div>
          <div class="mt-3 text-center">
            <template x-if="replacing">
              <span class="d-inline-flex gap-2">
                <button type="submit" class="btn-action">{{ $allUploaded ? 'Replace' : 'Save' }}</button>
                @if($allUploaded)
                  <button type="button" class="btn-action btn-action-red" @click="replacing = false">Cancel</button>
                @endif
              </span>
            </template>
          </div>
        </form>

        {{-- Delete All Files — sibling form outside the upload form (nested forms invalid in HTML) --}}
        @if($cpcApp)
        <div class="mt-2 text-center" x-show="!replacing">
          <form action="{{ route('projects.cpc-application.destroy-all-files', [$project, $cpcApp]) }}" method="POST"
                onsubmit="return confirm('Delete all uploaded CPC files? This cannot be undone.')"
                style="display:inline;">
            @csrf @method('DELETE')
            <span class="d-inline-flex gap-2">
              <button type="button" class="btn-action" @click="replacing = true">Replace Files</button>
              <button type="submit" class="btn-action btn-action-red">Delete All Files</button>
            </span>
          </form>
        </div>
        @endif

        </div>{{-- end x-data wrapper --}}
        @endcan
        <hr class="mt-3 mb-2" style="opacity:1;">
        <div class="d-flex align-items-start gap-4">
          <div class="text-muted small flex-grow-1">
            <strong>NOTA:</strong>
            <ol class="mb-0 ps-3 mt-1">
              <li>SILA MUAT-NAIK SALINAN Surat Serahan Dokumen Lengkap CPC.</li>
              <li>PASTIKAN ADA COP TERIMA &amp; TARIKH TERIMA OLEH PBT.</li>
            </ol>
          </div>
          <div class="text-center" style="min-width:130px;">
            @if($exampleImages['section12'])
              <img src="{{ route('example-images.show', 'section12') }}"
                   alt="Contoh dokumen" class="img-thumbnail" style="max-width:120px; cursor:pointer;"
                   data-bs-toggle="modal" data-bs-target="#exampleImageModal12">
              <div class="text-muted" style="font-size:0.7rem; margin-top:3px;">Contoh Surat Serahan</div>
              <div class="modal fade" id="exampleImageModal12" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header py-2">
                      <span class="fw-semibold small">Contoh Surat Serahan</span>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-2">
                      <img src="{{ route('example-images.show', 'section12') }}" alt="Contoh dokumen" class="img-fluid">
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="text-muted" style="font-size:0.7rem;">Tiada contoh</div>
            @endif
            @role('admin|officer')
            <form action="{{ route('example-images.upload', 'section12') }}" method="POST"
                  enctype="multipart/form-data" class="mt-1">
              @csrf
              <label class="btn-action btn-action-sm mb-0" style="cursor:pointer; font-size:0.75rem;">
                {{ $exampleImages['section12'] ? 'Replace' : 'Upload' }}
                <input type="file" name="image" class="d-none" accept="image/*" onchange="this.form.submit()">
              </label>
              <div class="text-muted" style="font-size:0.65rem; margin-top:3px;">JPG, PNG, WebP — max 5MB</div>
            </form>
            @endrole
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================================================================ --}}
{{-- SECTION 13: SIJIL PERAKUAN SIAP KERJA                             --}}
{{-- Uploading CPC sets project status to Completed                    --}}
{{-- ================================================================ --}}
<div class="row" id="section-13">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h3 class="card-title"><span class="me-3">13</span> NF: Upload Approved CPC</h3>
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
                 class="btn-action btn-action-sm"><i class="ti-download me-1"></i>Download CPC</a>
            </div>
          </div>
          <div class="alert alert-success py-2">Project marked as <strong>Completed</strong>.</div>
        @endif
        @can('update', $project)
        <div x-data="{ replacing: {{ $cpcRec ? 'false' : 'true' }} }">
          {{-- Show Replace button only when CPC already exists --}}
          @if($cpcRec)
            <div x-show="!replacing" class="mt-2">
              <button type="button" class="btn-action btn-action-sm" x-on:click="replacing = true">Replace</button>
            </div>
          @endif

          <form x-show="replacing" x-cloak action="{{ route('projects.cpc-received.store', $project) }}" method="POST" enctype="multipart/form-data" class="mt-3">
            @csrf
            <div class="row g-2">
              <div class="col-md-3">
                <label class="form-label small">CPC Date <span class="text-danger">*</span></label>
                <input type="date" name="cpc_date" class="form-control form-control-38"
                       value="{{ $cpcRec?->cpc_date?->format('Y-m-d') }}" required>
              </div>
              <div class="col-md-5">
                <label class="form-label small">CPC File (PDF)</label>
                <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                  <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                    Choose File
                    <input type="file" name="cpc_file" class="d-none" accept="application/pdf"
                           {{ $cpcRec ? '' : 'required' }} @change="fn = $event.target.files[0]?.name ?? ''">
                  </label>
                  <span class="text-muted small" x-text="fn || 'No file chosen'" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px; font-weight:normal;"></span>
                </div>
                <div class="text-muted" style="font-size:0.7rem; margin-top:2px;">PDF only — max 10MB</div>
              </div>
            </div>
            <div class="mt-2 d-flex gap-2 @if(!$cpcRec) justify-content-center @endif">
              <button type="submit" class="btn-action">
                {{ $cpcRec ? 'Save' : 'Upload & Mark Completed' }}
              </button>
              @if($cpcRec)
                <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                        x-on:click="replacing = false">Cancel</button>
              @endif
            </div>
          </form>
        </div>
        @endcan
        <hr class="mt-3 mb-2" style="opacity:1;">
        <div class="text-muted small">
          <strong>NOTA:</strong>
          <ol class="mb-0 ps-3 mt-1">
            <li>SILA SCAN &amp; MUAT-NAIK SIJIL PERAKUAN SIAP KERJA.</li>
            <li>SILA SERAH HARDCOPY CPC KEPADA PIHAK TM.</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</div>

@endif {{-- end if not cancelled --}}

@endsection
