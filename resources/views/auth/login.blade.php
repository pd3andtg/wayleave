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

  {{-- Session status (e.g. after logout or password reset) --}}
  @if (session('status'))
    <div class="alert alert-success py-2">{{ session('status') }}</div>
  @endif

  {{-- Post-registration approval notice --}}
  @if (session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
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
      @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mt-3">
      <button type="submit" class="auth-submit-btn">
        SIGN IN
      </button>
    </div>

    <div class="my-2 d-flex justify-content-between align-items-center">
      <div class="form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label text-muted" for="remember">Keep me signed in</label>
      </div>
      @if ($errors->has('password'))
        <a href="{{ route('password.request') }}" class="text-primary" style="font-size: 0.82rem;">Forgot password?</a>
      @endif
    </div>

    <div class="text-center mt-4 font-weight-light">
      Don't have an account? <a href="{{ route('register') }}" class="text-primary">Sign Up</a>
    </div>

  </form>

@endsection
