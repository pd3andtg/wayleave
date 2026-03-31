@extends('layouts.dashboard')

@section('title', 'Register New Project')

@section('content')

<div class="row justify-content-center">
  <div class="col-md-8 grid-margin">
    <div class="card">
      <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Register New Project</h4>
          <a href="{{ route('projects.index') }}" class="btn-action" style="font-size:0.75rem; padding:0 1rem; color:#ffffff !important;">&larr; Back to Projects</a>
        </div>

        @if ($errors->any())
          <div class="alert alert-danger py-2">
            @foreach ($errors->all() as $error)
              <div>{{ $error }}</div>
            @endforeach
          </div>
        @endif

        <form action="{{ route('projects.store') }}" method="POST"
              x-data="{
                selfApplied: '{{ old('self_applied_by_tm', '0') }}' === '1',
                isTm: {{ auth()->user()->hasRole('contractor') ? 'false' : 'true' }},
                ndState: '{{ old('nd_state', '') }}',
                allNodes: {{ Js::from($nodes->map(fn($n) => ['id' => $n->id, 'acronym' => $n->acronym, 'full_name' => $n->full_name, 'nd' => $n->nd])) }},
                nodeId: '{{ old('node_id', '') }}',
                nodeSearch: '',
                showNodeDropdown: false,
                get filteredNodes() {
                    const ndMap = {
                        'ND_TRG': 'TRG', 'ND_PHG': 'PHG', 'ND_KEL': 'KEL',
                        'ND_JS': 'JS', 'ND_JU': 'JU', 'ND_KD_PL': 'KD/PL',
                        'ND_KL': 'KL', 'ND_MK': 'MK', 'ND_MSC': 'MSC',
                        'ND_NS': 'NS', 'ND_PG': 'PP', 'ND_PHG': 'PHG',
                        'ND_PJ': 'PJ', 'ND_PRK': 'PRK', 'ND_SABAH': 'SABAH',
                        'ND_SARAWAK': 'SARAWAK', 'ND_SB': 'SB', 'ND_ST': 'ST',
                        'NO_TRG': 'TRG', 'NO_PHG': 'PHG', 'NO_KEL': 'KEL',
                        'NO_JS': 'JS', 'NO_JU': 'JU', 'NO_KD_PL': 'KD/PL',
                        'NO_KL': 'KL', 'NO_MK': 'MK', 'NO_MSC': 'MSC',
                        'NO_NS': 'NS', 'NO_PG': 'PP', 'NO_PHG': 'PHG',
                        'NO_PJ': 'PJ', 'NO_PRK': 'PRK', 'NO_SABAH': 'SABAH',
                        'NO_SARAWAK': 'SARAWAK', 'NO_SB': 'SB', 'NO_ST': 'ST',
                    };
                    const nd = ndMap[this.ndState] ?? null;
                    if (!nd) return [];
                    let list = this.allNodes.filter(n => n.nd === nd);
                    if (this.nodeSearch.trim()) {
                        const q = this.nodeSearch.toLowerCase();
                        list = list.filter(n =>
                            n.acronym.toLowerCase().includes(q) ||
                            n.full_name.toLowerCase().includes(q)
                        );
                    }
                    return list;
                },
                selectNode(node) {
                    this.nodeId = node.id;
                    this.nodeSearch = node.acronym + ' \u2014 ' + node.full_name;
                    this.showNodeDropdown = false;
                },
                initNodeSearch() {
                    if (this.nodeId) {
                        const found = this.allNodes.find(n => String(n.id) === String(this.nodeId));
                        if (found) this.nodeSearch = found.acronym + ' \u2014 ' + found.full_name;
                    }
                },
                allCompanies: {{ Js::from($companies->map(fn($c) => ['id' => $c->id, 'name' => $c->name])) }},
                companyId: '{{ old('company_id', '') }}',
                companySearch: '',
                showCompanyDropdown: false,
                get filteredCompanies() {
                    if (!this.companySearch.trim()) return this.allCompanies;
                    const q = this.companySearch.toLowerCase();
                    return this.allCompanies.filter(c => c.name.toLowerCase().includes(q));
                },
                selectCompany(company) {
                    this.companyId = company.id;
                    this.companySearch = company.name;
                    this.showCompanyDropdown = false;
                },
                initCompanySearch() {
                    if (this.companyId) {
                        const found = this.allCompanies.find(c => String(c.id) === String(this.companyId));
                        if (found) this.companySearch = found.name;
                    }
                }
              }"
              x-init="initNodeSearch(); initCompanySearch()">
          @csrf

          {{-- Self Applied by TM (officer/admin only) --}}
          @unless(auth()->user()->hasRole('contractor'))
          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">Self Applied by TM</label>
            <div class="col-sm-9">
              <select class="form-control" name="self_applied_by_tm"
                      @change="selfApplied = $event.target.value === '1'">
                <option value="0" {{ old('self_applied_by_tm', '0') !== '1' ? 'selected' : '' }}>No: Managed by TM Contractor (NF)</option>
                <option value="1" {{ old('self_applied_by_tm') === '1' ? 'selected' : '' }}>Yes: Self-applied and managed by TM</option>
              </select>
            </div>
          </div>

          {{-- Company searchable typeahead (only if NOT self-applied) --}}
          <div class="form-group row" x-show="!selfApplied" x-cloak>
            <label class="col-sm-3 col-form-label text-sm-end">Company <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <input type="hidden" name="company_id" :value="companyId" :required="!selfApplied">
              <div style="position:relative;">
                <input type="text"
                       class="form-control @error('company_id') is-invalid @enderror"
                       x-model="companySearch"
                       @focus="showCompanyDropdown = true"
                       @input="showCompanyDropdown = true; companyId = ''"
                       @click.outside="showCompanyDropdown = false"
                       placeholder="Search company name..."
                       autocomplete="off">
                <div x-show="showCompanyDropdown && filteredCompanies.length > 0"
                     style="position:absolute; z-index:1000; width:100%; max-height:220px; overflow-y:auto;
                            background:#fff; border:1px solid #ced4da; border-radius:4px; margin-top:2px;">
                  <template x-for="company in filteredCompanies" :key="company.id">
                    <div @click="selectCompany(company)"
                         style="padding:6px 12px; cursor:pointer; font-size:0.875rem;"
                         @mouseover="$el.style.background='#f0f4ff'"
                         @mouseout="$el.style.background='#fff'"
                         x-text="company.name">
                    </div>
                  </template>
                </div>
                <div x-show="showCompanyDropdown && companySearch.trim() && filteredCompanies.length === 0"
                     class="form-text text-muted" style="font-size:0.8rem; margin-top:4px;">
                  No companies found matching your search.
                </div>
              </div>
              @error('company_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          @endunless

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">PBT Reference No <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <input type="text" autocomplete="off" class="form-control @error('ref_no') is-invalid @enderror"
                     name="ref_no" value="{{ old('ref_no') }}" placeholder="KUTT/KUP/BKI/PBT REF NO" required>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">LOR No</label>
            <div class="col-sm-9">
              <input type="text" autocomplete="off" class="form-control @error('lor_no') is-invalid @enderror"
                     name="lor_no" value="{{ old('lor_no') }}" placeholder="LOR reference number">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">Project No</label>
            <div class="col-sm-9">
              <input type="text" autocomplete="off" class="form-control @error('project_no') is-invalid @enderror"
                     name="project_no" value="{{ old('project_no') }}" placeholder="Internal project number">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">Project Description <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <textarea autocomplete="off" class="form-control @error('project_desc') is-invalid @enderror"
                        name="project_desc" rows="3"
                        placeholder="Describe the project scope and location"
                        required>{{ old('project_desc') }}</textarea>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">ND/NO State <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <select class="form-control @error('nd_state') is-invalid @enderror" name="nd_state" required
                      x-model="ndState"
                      @change="nodeId = ''; nodeSearch = ''; showNodeDropdown = false;">
                <option value="">-- Select ND/NO State --</option>
                <option value="ND_JS" {{ old('nd_state') === 'ND_JS' ? 'selected' : '' }}>ND JS</option>
                <option value="ND_JU" {{ old('nd_state') === 'ND_JU' ? 'selected' : '' }}>ND JU</option>
                <option value="ND_KD_PL" {{ old('nd_state') === 'ND_KD_PL' ? 'selected' : '' }}>ND KD/PL</option>
                <option value="ND_KEL" {{ old('nd_state') === 'ND_KEL' ? 'selected' : '' }}>ND KEL</option>
                <option value="ND_KL" {{ old('nd_state') === 'ND_KL' ? 'selected' : '' }}>ND KL</option>
                <option value="ND_MK" {{ old('nd_state') === 'ND_MK' ? 'selected' : '' }}>ND MK</option>
                <option value="ND_MSC" {{ old('nd_state') === 'ND_MSC' ? 'selected' : '' }}>ND MSC</option>
                <option value="ND_NS" {{ old('nd_state') === 'ND_NS' ? 'selected' : '' }}>ND NS</option>
                <option value="ND_PG" {{ old('nd_state') === 'ND_PG' ? 'selected' : '' }}>ND PG</option>
                <option value="ND_PHG" {{ old('nd_state') === 'ND_PHG' ? 'selected' : '' }}>ND PHG</option>
                <option value="ND_PJ" {{ old('nd_state') === 'ND_PJ' ? 'selected' : '' }}>ND PJ</option>
                <option value="ND_PRK" {{ old('nd_state') === 'ND_PRK' ? 'selected' : '' }}>ND PRK</option>
                <option value="ND_SABAH" {{ old('nd_state') === 'ND_SABAH' ? 'selected' : '' }}>ND SABAH</option>
                <option value="ND_SARAWAK" {{ old('nd_state') === 'ND_SARAWAK' ? 'selected' : '' }}>ND SARAWAK</option>
                <option value="ND_SB" {{ old('nd_state') === 'ND_SB' ? 'selected' : '' }}>ND SB</option>
                <option value="ND_ST" {{ old('nd_state') === 'ND_ST' ? 'selected' : '' }}>ND ST</option>
                <option value="ND_TRG" {{ old('nd_state') === 'ND_TRG' ? 'selected' : '' }}>ND TRG</option>
                <option value="NO_JS" {{ old('nd_state') === 'NO_JS' ? 'selected' : '' }}>NO JS</option>
                <option value="NO_JU" {{ old('nd_state') === 'NO_JU' ? 'selected' : '' }}>NO JU</option>
                <option value="NO_KD_PL" {{ old('nd_state') === 'NO_KD_PL' ? 'selected' : '' }}>NO KD/PL</option>
                <option value="NO_KEL" {{ old('nd_state') === 'NO_KEL' ? 'selected' : '' }}>NO KEL</option>
                <option value="NO_KL" {{ old('nd_state') === 'NO_KL' ? 'selected' : '' }}>NO KL</option>
                <option value="NO_MK" {{ old('nd_state') === 'NO_MK' ? 'selected' : '' }}>NO MK</option>
                <option value="NO_MSC" {{ old('nd_state') === 'NO_MSC' ? 'selected' : '' }}>NO MSC</option>
                <option value="NO_NS" {{ old('nd_state') === 'NO_NS' ? 'selected' : '' }}>NO NS</option>
                <option value="NO_PG" {{ old('nd_state') === 'NO_PG' ? 'selected' : '' }}>NO PG</option>
                <option value="NO_PHG" {{ old('nd_state') === 'NO_PHG' ? 'selected' : '' }}>NO PHG</option>
                <option value="NO_PJ" {{ old('nd_state') === 'NO_PJ' ? 'selected' : '' }}>NO PJ</option>
                <option value="NO_PRK" {{ old('nd_state') === 'NO_PRK' ? 'selected' : '' }}>NO PRK</option>
                <option value="NO_SABAH" {{ old('nd_state') === 'NO_SABAH' ? 'selected' : '' }}>NO SABAH</option>
                <option value="NO_SARAWAK" {{ old('nd_state') === 'NO_SARAWAK' ? 'selected' : '' }}>NO SARAWAK</option>
                <option value="NO_SB" {{ old('nd_state') === 'NO_SB' ? 'selected' : '' }}>NO SB</option>
                <option value="NO_ST" {{ old('nd_state') === 'NO_ST' ? 'selected' : '' }}>NO ST</option>
                <option value="NO_TRG" {{ old('nd_state') === 'NO_TRG' ? 'selected' : '' }}>NO TRG</option>
              </select>
            </div>
          </div>

          {{-- TM Node — filtered by ND/NO State, searchable typeahead --}}
          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">TM Node <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <input type="hidden" name="node_id" :value="nodeId">
              <div style="position:relative;">
                <input type="text"
                       class="form-control @error('node_id') is-invalid @enderror"
                       x-model="nodeSearch"
                       @focus="if (ndState) showNodeDropdown = true"
                       @input="showNodeDropdown = true; nodeId = ''"
                       @click.outside="showNodeDropdown = false"
                       :placeholder="ndState ? 'Type acronym or name to search...' : 'Select an ND/NO State first'"
                       :disabled="!ndState"
                       autocomplete="off">
                <div x-show="showNodeDropdown && filteredNodes.length > 0"
                     x-cloak
                     style="position:absolute; top:100%; left:0; right:0; z-index:1050; background:#fff; border:1px solid #ced4da; border-top:none; max-height:220px; overflow-y:auto; border-radius:0 0 4px 4px; box-shadow:0 4px 8px rgba(0,0,0,0.08);">
                  <template x-for="node in filteredNodes" :key="node.id">
                    <div @mousedown.prevent="selectNode(node)"
                         style="padding:8px 12px; cursor:pointer; font-size:0.9rem;"
                         @mouseenter="$el.style.background='#f0f4ff'"
                         @mouseleave="$el.style.background=''">
                      <span class="fw-semibold" x-text="node.acronym"></span>
                      <span class="text-muted" x-text="' \u2014 ' + node.full_name"></span>
                    </div>
                  </template>
                </div>
                <div x-show="ndState && nodeSearch.trim() && !nodeId && filteredNodes.length === 0"
                     x-cloak
                     class="form-text text-muted" style="font-size:0.8rem; margin-top:4px;">
                  No nodes found for this ND/NO State matching your search.
                </div>
              </div>
              @error('node_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>

          {{-- Application Date — Registration Date at KUTT/BKI/KUP/PBT --}}
          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">
              Application Date
              <div class="text-muted" style="font-size:0.7rem; font-weight:normal;">(Registration Date at KUTT/BKI/KUP/PBT)</div>
            </label>
            <div class="col-sm-9">
              <input type="date" name="application_date" class="form-control @error('application_date') is-invalid @enderror"
                     value="{{ old('application_date') }}">
              @error('application_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          {{-- Payment to PBT --}}
          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">Payment to KUTT/BKI/KUP</label>
            <div class="col-sm-9">
              <select class="form-control @error('payment_to_pbt') is-invalid @enderror" name="payment_to_pbt">
                <option value="">-- Select --</option>
                <option value="charged"      {{ old('payment_to_pbt') === 'charged'      ? 'selected' : '' }}>Charged</option>
                <option value="waived"       {{ old('payment_to_pbt') === 'waived'       ? 'selected' : '' }}>Waived</option>
                <option value="not_required" {{ old('payment_to_pbt') === 'not_required' ? 'selected' : '' }}>Not Required</option>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">Remarks</label>
            <div class="col-sm-9">
              <textarea autocomplete="off" class="form-control @error('remarks') is-invalid @enderror"
                        name="remarks" rows="2"
                        placeholder="Optional remarks">{{ old('remarks') }}</textarea>
            </div>
          </div>

          <div class="form-group row mt-4">
            <div class="col-sm-9 offset-sm-3">
              <button type="submit" class="btn-action me-2" style="font-weight:400; text-transform:none; color:#ffffff;">Register Project</button>
              <a href="{{ route('projects.index') }}" class="btn-action" style="font-weight:400; text-transform:none; color:#ffffff !important;">Cancel</a>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>

@endsection
