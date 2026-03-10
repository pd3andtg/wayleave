<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>{{ config('app.name') }} - @yield('title')</title>
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="{{ asset('css/themify-icons.css') }}">
  @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
</head>

<body>
  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-back-home">
        <a href="{{ url('/') }}">&larr; Back to Home</a>
      </div>
      @yield('content')
    </div>
  </div>

  @stack('scripts')
</body>

</html>
