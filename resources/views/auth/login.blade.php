@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')

  <h4>Hello! let's get started</h4>
  <h6 class="font-weight-light">Sign in to continue.</h6>

  {{-- Validation errors --}}
  @if ($errors->any())
    <div class="alert alert-danger py-2">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  {{-- Session status (e.g. after logout) --}}
  @if (session('status'))
    <div class="alert alert-success py-2">{{ session('status') }}</div>
  @endif

  <form class="pt-3" action="{{ route('login') }}" method="POST">
    @csrf

    <div class="form-group">
      <input type="email"
             class="form-control form-control-lg @error('email') is-invalid @enderror"
             name="email"
             value="{{ old('email') }}"
             placeholder="Email address"
             required autofocus>
    </div>

    <div class="form-group">
      <input type="password"
             class="form-control form-control-lg @error('password') is-invalid @enderror"
             name="password"
             placeholder="Password"
             required>
    </div>

    <div class="mt-3">
      <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
        SIGN IN
      </button>
    </div>

    <div class="my-2 d-flex justify-content-between align-items-center">
      <div class="form-check">
        <label class="form-check-label text-muted">
          <input type="checkbox" class="form-check-input" name="remember">
          Keep me signed in
        </label>
      </div>
    </div>

    <div class="text-center mt-4 font-weight-light">
      Don't have an account? <a href="{{ route('register') }}" class="text-primary">Create</a>
    </div>

  </form>

@endsection
