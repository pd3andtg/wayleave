<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>{{ config('app.name') }} - @yield('title')</title>
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" />
  <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('vendors/base/vendor.bundle.base.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
  <div class="container-scroller">

    <!-- ============================================================ -->
    <!-- NAVBAR                                                        -->
    <!-- ============================================================ -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo me-5" href="{{ route('dashboard') }}">
          <img src="{{ asset('images/logo.png') }}" class="me-2" alt="{{ config('app.name') }}" />
        </a>
        <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
          <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" />
        </a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="ti-view-list"></span>
        </button>

        <ul class="navbar-nav navbar-nav-right">

          {{-- Notification dropdown --}}
          <li class="nav-item dropdown">
            <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
              <i class="ti-bell mx-0"></i>
              <span class="count"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="notificationDropdown">
              <p class="mb-0 font-weight-medium float-left dropdown-header">Notifications</p>
              <a class="dropdown-item">
                <div class="item-thumbnail">
                  <div class="item-icon bg-info">
                    <i class="ti-info-alt mx-0"></i>
                  </div>
                </div>
                <div class="item-content">
                  <h6 class="font-weight-normal">Welcome, {{ auth()->user()->name }}</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">Just now</p>
                </div>
              </a>
            </div>
          </li>

          {{-- Profile dropdown --}}
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
              <img src="{{ asset('images/logo.png') }}" alt="profile" />
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
              <div class="dropdown-item">
                <i class="ti-user text-primary"></i>
                <span class="ms-1">{{ auth()->user()->name }}</span>
              </div>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="{{ route('logout') }}"
                 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="ti-power-off text-primary"></i>
                Logout
              </a>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
              </form>
            </div>
          </li>

        </ul>

        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
          <span class="ti-view-list"></span>
        </button>
      </div>
    </nav>
    <!-- End Navbar -->

    <div class="container-fluid page-body-wrapper">

      <!-- ============================================================ -->
      <!-- SIDEBAR                                                       -->
      <!-- ============================================================ -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">

          <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
              <i class="ti-home menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>

          <li class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('projects.index') }}">
              <i class="ti-files menu-icon"></i>
              <span class="menu-title">Projects</span>
            </a>
          </li>

          @role('admin')
          <li class="nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#admin-menu"
               aria-expanded="{{ request()->routeIs('admin.*') ? 'true' : 'false' }}"
               aria-controls="admin-menu">
              <i class="ti-settings menu-icon"></i>
              <span class="menu-title">Admin</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ request()->routeIs('admin.*') ? 'show' : '' }}" id="admin-menu">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}"
                     href="{{ route('admin.companies.index') }}">Company Requests</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                     href="{{ route('admin.users.index') }}">User Management</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ request()->routeIs('admin.units.*') ? 'active' : '' }}"
                     href="{{ route('admin.units.index') }}">Unit Management</a>
                </li>
              </ul>
            </div>
          </li>
          @endrole

        </ul>
      </nav>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="content-wrapper">
          @yield('content')
        </div>

        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">
              &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </span>
          </div>
        </footer>
      </div>
      <!-- End main-panel -->

    </div>
    <!-- End page-body-wrapper -->

  </div>
  <!-- End container-scroller -->

  <script src="{{ asset('vendors/base/vendor.bundle.base.js') }}"></script>
  <script src="{{ asset('js/jquery.cookie.js') }}"></script>
  <script src="{{ asset('js/off-canvas.js') }}"></script>
  <script src="{{ asset('js/hoverable-collapse.js') }}"></script>
  <script src="{{ asset('js/template.js') }}"></script>
  @stack('scripts')
</body>

</html>
