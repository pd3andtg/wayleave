@extends('layouts.auth')

@section('title', 'Register Company')

@section('content')

  <h4>Register a New Company</h4>
  <h6 class="font-weight-light">Submit your company for Admin approval. Once approved, you can create your account.</h6>

  {{-- Validation errors --}}
  @if ($errors->any())
    <div class="alert alert-danger py-2">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  {{-- Success message after submission --}}
  @if (session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif

  <form class="pt-3" action="{{ route('register.company.submit') }}" method="POST">
    @csrf

    {{-- Company name --}}
    <div class="form-group">
      <input type="text" autocomplete="off"
             class="form-control form-control-lg @error('company_name') is-invalid @enderror"
             name="company_name"
             value="{{ old('company_name') }}"
             placeholder="Company name"
             required autofocus>
    </div>

    {{-- Requester name --}}
    <div class="form-group">
      <input type="text" autocomplete="off"
             class="form-control form-control-lg @error('requester_name') is-invalid @enderror"
             name="requester_name"
             value="{{ old('requester_name') }}"
             placeholder="Your full name"
             required>
    </div>

    {{-- Requester email --}}
    <div class="form-group">
      <input type="email" autocomplete="off"
             class="form-control form-control-lg @error('requester_email') is-invalid @enderror"
             name="requester_email"
             value="{{ old('requester_email') }}"
             placeholder="Your email address"
             required>
    </div>

    <div class="mt-3">
      <button type="submit" class="auth-submit-btn" style="width:100%">
        SUBMIT REQUEST
      </button>
    </div>

    <div class="text-center mt-3">
      <small class="text-muted">
        Admin will review your request. You will be notified once approved.
      </small>
    </div>

    <div class="text-center mt-3">
      <small class="text-muted">
        Already have an account? <a href="{{ route('login') }}" class="text-primary">Sign In</a>
        &nbsp;|&nbsp;
        <a href="{{ route('register') }}" class="text-primary">Back to Sign Up</a>
      </small>
    </div>

  </form>

@endsection
