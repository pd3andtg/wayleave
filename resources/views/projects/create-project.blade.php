@extends('layouts.dashboard')

@section('title', 'Register New Project')

@section('content')

<div class="row">
  <div class="col-md-8 grid-margin">
    <div class="card">
      <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Register New Project</h4>
          <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to Projects</a>
        </div>

        {{-- Validation errors --}}
        @if ($errors->any())
          <div class="alert alert-danger py-2">
            @foreach ($errors->all() as $error)
              <div>{{ $error }}</div>
            @endforeach
          </div>
        @endif

        <form action="{{ route('projects.store') }}" method="POST">
          @csrf

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">KUTT Ref No / PBT Ref No</label>
            <div class="col-sm-9">
              <input type="text"
                     class="form-control @error('ref_no') is-invalid @enderror"
                     name="ref_no"
                     value="{{ old('ref_no') }}"
                     placeholder="e.g. KUTT/2024/001">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">LOR No</label>
            <div class="col-sm-9">
              <input type="text"
                     class="form-control @error('lor_no') is-invalid @enderror"
                     name="lor_no"
                     value="{{ old('lor_no') }}"
                     placeholder="LOR reference number">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Project No</label>
            <div class="col-sm-9">
              <input type="text"
                     class="form-control @error('project_no') is-invalid @enderror"
                     name="project_no"
                     value="{{ old('project_no') }}"
                     placeholder="Internal project number">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Project Description <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <textarea class="form-control @error('project_desc') is-invalid @enderror"
                        name="project_desc"
                        rows="3"
                        placeholder="Describe the project scope and location"
                        required>{{ old('project_desc') }}</textarea>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">ND State <span class="text-danger">*</span></label>
            <div class="col-sm-9">
              <select class="form-control @error('nd_state') is-invalid @enderror"
                      name="nd_state"
                      required>
                <option value="">-- Select ND State --</option>
                <option value="ND_TRG" {{ old('nd_state') === 'ND_TRG' ? 'selected' : '' }}>ND TRG</option>
                <option value="ND_PHG" {{ old('nd_state') === 'ND_PHG' ? 'selected' : '' }}>ND PHG</option>
                <option value="ND_KEL" {{ old('nd_state') === 'ND_KEL' ? 'selected' : '' }}>ND KEL</option>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-3 col-form-label">Remarks</label>
            <div class="col-sm-9">
              <textarea class="form-control @error('remarks') is-invalid @enderror"
                        name="remarks"
                        rows="2"
                        placeholder="Optional remarks">{{ old('remarks') }}</textarea>
            </div>
          </div>

          <div class="form-group row mt-4">
            <div class="col-sm-9 offset-sm-3">
              <button type="submit" class="btn btn-primary me-2">Register Project</button>
              <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>

@endsection
