@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')

  <h4>New here?</h4>
  <h6 class="font-weight-light">Signing up is easy. It only takes a few steps.</h6>

  {{-- Validation errors --}}
  @if ($errors->any())
    <div class="alert alert-danger py-2">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  {{-- Alpine.js handles conditional fields based on company selection --}}
  <form class="pt-3" action="{{ route('register.submit') }}" method="POST"
        x-data="{
          companySelection: '{{ old('company_selection') }}',
          get isTmTech()     { return this.companySelection === 'tmtech' },
          get isContractor() { return this.companySelection !== '' && this.companySelection !== 'tmtech' }
        }">
    @csrf

    {{-- Name --}}
    <div class="form-group">
      <input type="text"
             class="form-control form-control-lg @error('name') is-invalid @enderror"
             name="name"
             value="{{ old('name') }}"
             placeholder="Full name"
             required autofocus>
    </div>

    {{-- Email --}}
    <div class="form-group">
      <input type="email"
             class="form-control form-control-lg @error('email') is-invalid @enderror"
             name="email"
             value="{{ old('email') }}"
             placeholder="Email address"
             required>
    </div>

    {{-- Company selection --}}
    {{-- TM Tech → officer role assigned automatically --}}
    {{-- Approved company → contractor role assigned automatically --}}
    <div class="form-group">
      <select class="form-control form-control-lg @error('company_selection') is-invalid @enderror"
              name="company_selection"
              x-model="companySelection"
              required>
        <option value="">-- Select Company --</option>
        <option value="tmtech" {{ old('company_selection') === 'tmtech' ? 'selected' : '' }}>
          TM Tech (Internal Staff)
        </option>
        @foreach ($companies as $company)
          <option value="{{ $company->id }}" {{ old('company_selection') == $company->id ? 'selected' : '' }}>
            {{ $company->name }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Unit dropdown — TM Tech officers only --}}
    <div class="form-group" x-show="isTmTech" x-cloak>
      <select class="form-control form-control-lg @error('unit_id') is-invalid @enderror"
              name="unit_id"
              :required="isTmTech">
        <option value="">-- Select Unit --</option>
        @foreach ($units as $unit)
          <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
            {{ $unit->name }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Staff ID — TM Tech officers only --}}
    <div class="form-group" x-show="isTmTech" x-cloak>
      <input type="text"
             class="form-control form-control-lg @error('id_number') is-invalid @enderror"
             name="id_number"
             value="{{ old('id_number') }}"
             placeholder="Staff ID"
             :required="isTmTech"
             :disabled="!isTmTech">
    </div>

    {{-- IC No — external contractors only --}}
    <div class="form-group" x-show="isContractor" x-cloak>
      <input type="text"
             class="form-control form-control-lg @error('id_number') is-invalid @enderror"
             name="id_number"
             value="{{ old('id_number') }}"
             placeholder="IC Number (e.g. 990101-01-1234)"
             :required="isContractor"
             :disabled="!isContractor">
    </div>

    {{-- Password --}}
    <div class="form-group">
      <input type="password"
             class="form-control form-control-lg @error('password') is-invalid @enderror"
             name="password"
             placeholder="Password"
             required>
    </div>

    {{-- Confirm password --}}
    <div class="form-group">
      <input type="password"
             class="form-control form-control-lg"
             name="password_confirmation"
             placeholder="Confirm password"
             required>
    </div>

    <div class="mt-3">
      <button type="submit" class="auth-submit-btn" style="width:100%">
        SIGN UP
      </button>
    </div>

    {{-- Company not in list? Direct them to the company registration request page --}}
    <div class="text-center mt-3">
      <small class="text-muted">
        Company not listed?
        <a href="{{ route('register.company') }}" class="text-primary">Register a new company</a>
      </small>
    </div>

    <div class="text-center mt-3 font-weight-light">
      Already have an account? <a href="{{ route('login') }}" class="text-primary">Sign In</a>
    </div>

  </form>

@endsection
