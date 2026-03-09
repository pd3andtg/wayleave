<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>{{ config('app.name') }} - @yield('title')</title>
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" />
  <link rel="stylesheet" href="{{ asset('css/themify-icons.css') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-center auth px-0">
        <div class="row w-100 mx-0">
          <div class="col-lg-4 mx-auto">
            <div class="auth-form-light text-left py-5 px-4 px-sm-5">

              <div class="brand-logo">
                <img src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}">
              </div>

              @yield('content')

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @stack('scripts')
</body>

</html>
