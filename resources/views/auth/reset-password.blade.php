@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')

  <h4>Reset Password</h4>
  <h6 class="font-weight-light">Enter your new password below.</h6>

  @if ($errors->any())
    <div class="alert alert-danger py-2">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  <form class="pt-3" action="{{ route('password.update') }}" method="POST">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
      <input type="email" autocomplete="off"
             class="form-control form-control-lg @error('email') is-invalid @enderror"
             name="email"
             value="{{ old('email') }}"
             placeholder="Email address"
             required autofocus>
    </div>

    <div class="form-group">
      <input type="password" autocomplete="new-password"
             class="form-control form-control-lg @error('password') is-invalid @enderror"
             name="password"
             placeholder="New password (min. 8 characters)"
             required>
    </div>

    <div class="form-group">
      <input type="password" autocomplete="new-password"
             class="form-control form-control-lg"
             name="password_confirmation"
             placeholder="Confirm new password"
             required>
    </div>

    <div class="mt-3">
      <button type="submit" class="auth-submit-btn" style="width:100%">
        RESET PASSWORD
      </button>
    </div>

  </form>

@endsection
