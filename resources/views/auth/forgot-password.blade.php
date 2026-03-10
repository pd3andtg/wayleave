@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')

  <h4>Forgot Password</h4>
  <h6 class="font-weight-light">Enter your email and we'll send you a reset link.</h6>

  @if (session('status'))
    <div class="alert alert-success py-2">{{ session('status') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger py-2">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  <form class="pt-3" action="{{ route('password.email') }}" method="POST">
    @csrf

    <div class="form-group">
      <input type="email"
             class="form-control form-control-lg @error('email') is-invalid @enderror"
             name="email"
             value="{{ old('email') }}"
             placeholder="Email address"
             required autofocus>
    </div>

    <div class="mt-3">
      <button type="submit" class="auth-submit-btn" style="width:100%">
        SEND RESET LINK
      </button>
    </div>

    <div class="text-center mt-4 font-weight-light">
      <a href="{{ route('login') }}" class="text-primary">Back to Sign In</a>
    </div>

  </form>

@endsection
