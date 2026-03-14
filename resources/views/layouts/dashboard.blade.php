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
          <img src="{{ asset('images/logo.png') }}" class="me-2" alt="{{ config('app.name') }}" style="height:52px;width:auto;" />
        </a>
        <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
          <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="height:52px;width:auto;" />
        </a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span style="display:flex;flex-direction:column;gap:5px;">
            <span style="display:block;width:22px;height:2px;background:#07326A;border-radius:2px;"></span>
            <span style="display:block;width:22px;height:2px;background:#07326A;border-radius:2px;"></span>
            <span style="display:block;width:22px;height:2px;background:#07326A;border-radius:2px;"></span>
          </span>
        </button>

        <ul class="navbar-nav navbar-nav-right">

          {{-- Notification dropdown (pending approvals for admin + officer) --}}
          {{-- $pendingCount is provided by the view composer in AppServiceProvider --}}
          <li class="nav-item dropdown">
            <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
              <i class="ti-bell mx-0"></i>
              @hasanyrole('admin|officer')
                @if ($pendingCount > 0)
                  <span class="count" style="background:#e74c3c;">{{ $pendingCount }}</span>
                @else
                  <span class="count"></span>
                @endif
              @else
                <span class="count"></span>
              @endhasanyrole
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="notificationDropdown">
              <p class="mb-0 font-weight-medium float-left dropdown-header">Notifications</p>
              @hasanyrole('admin|officer')
                @if ($pendingCount > 0)
                  <a class="dropdown-item" href="{{ route('approvals.index') }}">
                    <div class="item-thumbnail">
                      <div class="item-icon bg-danger">
                        <i class="ti-user mx-0"></i>
                      </div>
                    </div>
                    <div class="item-content">
                      <h6 class="font-weight-normal">{{ $pendingCount }} pending approval{{ $pendingCount > 1 ? 's' : '' }}</h6>
                      <p class="font-weight-light small-text mb-0 text-muted">Click to review</p>
                    </div>
                  </a>
                @else
                  <a class="dropdown-item">
                    <div class="item-content">
                      <h6 class="font-weight-normal">No pending approvals</h6>
                    </div>
                  </a>
                @endif
              @else
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
              @endhasanyrole
            </div>
          </li>

          {{-- Profile dropdown --}}
          <li class="nav-item nav-profile dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
              <img src="{{ asset('images/user.png') }}" alt="profile" />
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
          <span style="display:flex;flex-direction:column;gap:5px;">
            <span style="display:block;width:22px;height:2px;background:#07326A;border-radius:2px;"></span>
            <span style="display:block;width:22px;height:2px;background:#07326A;border-radius:2px;"></span>
            <span style="display:block;width:22px;height:2px;background:#07326A;border-radius:2px;"></span>
          </span>
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

          <li class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('projects.index') }}">
              <i class="ti-home menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>

          @hasanyrole('admin|officer')
          <li class="nav-item {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('approvals.index') }}">
              <i class="ti-check-box menu-icon"></i>
              <span class="menu-title">
                Approvals
                @if (isset($pendingCount) && $pendingCount > 0)
                  <span class="badge ms-1" style="background:#e74c3c; color:#fff; font-size:0.7rem; padding:2px 6px; border-radius:10px;">{{ $pendingCount }}</span>
                @endif
              </span>
            </a>
          </li>
          @endhasanyrole

          @role('admin')
          {{-- Alpine.js toggle replaces Bootstrap collapse to avoid BS4/BS5 conflict --}}
          <li class="nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}"
              x-data="{ open: {{ request()->routeIs('admin.*') ? 'true' : 'false' }} }">
            <a class="nav-link" href="javascript:void(0)" @click="open = !open">
              <i class="ti-settings menu-icon"></i>
              <span class="menu-title">Admin</span>
              <i class="menu-arrow"></i>
            </a>
            <div x-show="open" x-cloak style="width:100%;">
              <ul class="nav flex-column sub-menu" style="width:100%;">
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
