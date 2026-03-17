@extends('layouts.dashboard')

@section('title', 'Register New Project')

@section('content')

<div class="row">
  <div class="col-md-8 grid-margin">
    <div class="card">
      <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Register New Project</h4>
          <a href="{{ route('projects.index') }}" class="btn-action" style="font-size:0.75rem; padding:0 1rem;">&larr; Back to Projects</a>
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
                isTm: {{ auth()->user()->hasRole('contractor') ? 'false' : 'true' }}
              }">
          @csrf

          {{-- Self Applied by TM (officer/admin only) --}}
          @unless(auth()->user()->hasRole('contractor'))
          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">Self Applied by TM</label>
            <div class="col-sm-9">
              <select class="form-control" name="self_applied_by_tm"
                      @change="selfApplied = $event.target.value === '1'">
                <option value="0" {{ old('self_applied_by_tm', '0') !== '1' ? 'selected' : '' }}>No | Register on behalf of contractor</option>
                <option value="1" {{ old('self_applied_by_tm') === '1' ? 'selected' : '' }}>Yes | TM self-applied and managed project</option>
              </select>
            </div>
          </div>

          {{-- Company dropdown (only if NOT self-applied) --}}
          <div class="form-group row" x-show="!selfApplied" x-cloak>
            <label class="col-sm-3 col-form-label text-sm-end">Company <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <select class="form-control @error('company_id') is-invalid @enderror"
                      name="company_id"
                      :required="!selfApplied">
                <option value="">-- Select Company --</option>
                @foreach($companies as $company)
                  <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                    {{ $company->name }}
                  </option>
                @endforeach
              </select>
              @error('company_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          @endunless

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">KUTT/ PBT Reference No <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <input type="text" class="form-control @error('ref_no') is-invalid @enderror"
                     name="ref_no" value="{{ old('ref_no') }}" placeholder="e.g. KUTT/2024/001" required>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">LOR No</label>
            <div class="col-sm-9">
              <input type="text" class="form-control @error('lor_no') is-invalid @enderror"
                     name="lor_no" value="{{ old('lor_no') }}" placeholder="LOR reference number">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">Project No</label>
            <div class="col-sm-9">
              <input type="text" class="form-control @error('project_no') is-invalid @enderror"
                     name="project_no" value="{{ old('project_no') }}" placeholder="Internal project number">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">Project Description <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <textarea class="form-control @error('project_desc') is-invalid @enderror"
                        name="project_desc" rows="3"
                        placeholder="Describe the project scope and location"
                        required>{{ old('project_desc') }}</textarea>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">ND State <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <select class="form-control @error('nd_state') is-invalid @enderror" name="nd_state" required>
                <option value="">-- Select ND State --</option>
                <option value="ND_TRG" {{ old('nd_state') === 'ND_TRG' ? 'selected' : '' }}>ND TRG</option>
                <option value="ND_PHG" {{ old('nd_state') === 'ND_PHG' ? 'selected' : '' }}>ND PHG</option>
                <option value="ND_KEL" {{ old('nd_state') === 'ND_KEL' ? 'selected' : '' }}>ND KEL</option>
              </select>
            </div>
          </div>

          {{-- TM Node --}}
          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">TM Node <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <select class="form-control @error('node_id') is-invalid @enderror" name="node_id" required>
                <option value="">-- Select Node --</option>
                @foreach($nodes as $node)
                  <option value="{{ $node->id }}" {{ old('node_id') == $node->id ? 'selected' : '' }}>
                    {{ $node->acronym }} — {{ $node->full_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          {{-- Payment to KUTT --}}
          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">Payment to KUTT</label>
            <div class="col-sm-9">
              <select class="form-control @error('payment_to_kutt') is-invalid @enderror" name="payment_to_kutt">
                <option value="">-- Select --</option>
                <option value="charged"      {{ old('payment_to_kutt') === 'charged'      ? 'selected' : '' }}>Charged</option>
                <option value="waived"       {{ old('payment_to_kutt') === 'waived'       ? 'selected' : '' }}>Waived</option>
                <option value="not_required" {{ old('payment_to_kutt') === 'not_required' ? 'selected' : '' }}>Not Required</option>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label text-sm-end">Remarks</label>
            <div class="col-sm-9">
              <textarea class="form-control @error('remarks') is-invalid @enderror"
                        name="remarks" rows="2"
                        placeholder="Optional remarks">{{ old('remarks') }}</textarea>
            </div>
          </div>

          <div class="form-group row mt-4">
            <div class="col-sm-9 offset-sm-3">
              <button type="submit" class="btn-action me-2">Register Project</button>
              <a href="{{ route('projects.index') }}" class="btn-action">Cancel</a>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>

@endsection
