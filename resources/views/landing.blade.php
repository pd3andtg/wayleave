<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ config('app.name') }} - Wayleave &amp; Permit Tracking</title>
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" />
  <link rel="stylesheet" href="{{ asset('css/normalize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

  <!-- ============================================================ -->
  <!-- HEADER                                                        -->
  <!-- ============================================================ -->
  <div id="header-wrap">
    <header id="header">
      <div class="container-fluid">
        <div class="row align-items-center">

          <div class="col-md-2">
            <div class="main-logo">
              <a href="{{ route('landing') }}">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
              </a>
            </div>
          </div>

          <div class="col-md-10">
            <nav id="navbar">
              <div class="main-menu stellarnav">
                <ul class="menu-list">
                  <li class="menu-item {{ request()->routeIs('landing') ? 'active' : '' }}">
                    <a href="{{ route('landing') }}">Home</a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('register') ? 'active' : '' }}">
                    <a href="{{ route('register') }}">Sign Up</a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('register.company') ? 'active' : '' }}">
                    <a href="{{ route('register.company') }}">Register Company</a>
                  </li>
                  <li class="menu-item">
                    <a href="{{ route('login') }}" class="btn btn-primary px-3">Sign In</a>
                  </li>
                </ul>
                <div class="hamburger">
                  <span class="bar"></span>
                  <span class="bar"></span>
                  <span class="bar"></span>
                </div>
              </div>
            </nav>
          </div>

        </div>
      </div>
    </header>
  </div>
  <!-- End Header -->


  <!-- ============================================================ -->
  <!-- HERO / BILLBOARD                                              -->
  <!-- ============================================================ -->
  <section id="billboard">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="main-slider pattern-overlay">
            <div class="slider-item">
              <div class="banner-content">
                <h2 class="banner-title">Wayleave &amp; Permit Tracking and Filing System</h2>
                <p>Streamline your wayleave applications, track permit statuses, and manage all filings in one place.</p>
                <div class="btn-wrap">
                  <a href="{{ route('login') }}" class="btn btn-accent btn-accent-arrow">
                    Sign In <i class="icon icon-ns-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- End Billboard -->


  <!-- ============================================================ -->
  <!-- FOOTER                                                        -->
  <!-- ============================================================ -->
  <div id="footer-bottom">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="copyright">
            <div class="row">
              <div class="col-md-6">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Footer -->

  <script src="{{ asset('js/jquery-1.11.0.min.js') }}"></script>
  <script src="{{ asset('js/plugins.js') }}"></script>
  <script src="{{ asset('js/script.js') }}"></script>

</body>

</html>
