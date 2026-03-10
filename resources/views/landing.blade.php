<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ config('app.name') }} - Wayleave &amp; Permit Tracking</title>
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
  <link rel="stylesheet" href="{{ asset('css/normalize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root {
      --body-font: 'Poppins', sans-serif;
      --heading-font: 'Poppins', sans-serif;
      --heading-font-weight: 600;
    }

    /* Header: logo left, nav right */
    .header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 38px;
      padding: 0 15px;
    }

    .main-logo,
    .main-logo a {
      display: flex;
      align-items: center;
      height: 100%;
    }

    .main-logo img {
      height: 22px;
      width: auto;
    }

    /* Desktop nav */
    #navbar {
      height: 100%;
      display: flex;
      align-items: center;
    }

    #navbar .menu-list {
      display: flex;
      align-items: center;
      list-style: none;
      margin: 0;
      padding: 0;
      gap: 4px;
      height: 100%;
    }

    #navbar .menu-list .menu-item {
      display: flex;
      align-items: center;
      height: 100%;
      margin: 0;
      padding: 0;
    }

    #navbar .menu-list .menu-item a {
      display: flex;
      align-items: center;
      padding: 0 14px;
      height: 100%;
      white-space: nowrap;
      font-size: 0.95rem;
      font-weight: 500;
    }

    /* Hamburger — hidden on desktop */
    .hamburger {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 5px;
      padding: 5px;
    }

    .hamburger .bar {
      display: block;
      width: 25px;
      height: 3px;
      background-color: #EBEBEB;
      border-radius: 3px;
      transition: all 0.3s ease;
    }

    .hamburger.active .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
    .hamburger.active .bar:nth-child(2) { opacity: 0; }
    .hamburger.active .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }

    /* Mobile */
    @media screen and (max-width: 999px) {
      .hamburger { display: flex; }

      #navbar .menu-list {
        display: none;
        flex-direction: column;
        align-items: center;
        height: auto;
        width: 100%;
        background: #064089;
        padding: 12px 0 16px;
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 9999;
        box-shadow: 0 4px 10px rgba(0,0,0,0.12);
      }

      #navbar .menu-list.open { display: flex; }

      #navbar .menu-list .menu-item {
        width: 100%;
        height: auto;
      }

      #navbar .menu-list .menu-item a {
        height: auto;
        padding: 12px 20px;
        text-align: center;
      }

      #header { position: relative; }
    }
  </style>
</head>

<body>

  <!-- ============================================================ -->
  <!-- HEADER                                                        -->
  <!-- ============================================================ -->
  <div id="header-wrap">
    <header id="header">
      <div class="container-fluid">
        <div class="header-inner">

          <div class="main-logo">
            <a href="{{ route('landing') }}">
              <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
            </a>
          </div>

          <nav id="navbar">
            <ul class="menu-list" id="menu-list">
              <li class="menu-item {{ request()->routeIs('landing') ? 'active' : '' }}">
                <a href="{{ route('landing') }}">Home</a>
              </li>
              <li class="menu-item {{ request()->routeIs('login') ? 'active' : '' }}">
                <a href="{{ route('login') }}">Sign In</a>
              </li>
              <li class="menu-item {{ request()->routeIs('register.company') ? 'active' : '' }}">
                <a href="{{ route('register.company') }}">Register Company</a>
              </li>
            </ul>
          </nav>

          <div class="hamburger" id="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
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
              <div class="banner-content text-center" style="width: 60%; margin-left: auto; margin-right: auto;">
                <h2 class="banner-title text-center" style="font-size: 2.5em;">Wayleave &amp; Permit Tracking<br>And Filing System</h2>
                <p>Streamline your wayleave applications, track permit statuses, and manage all filings in one place.</p>
                <div class="btn-wrap">
                  <a href="{{ route('login') }}" class="btn btn-accent btn-accent-arrow" style="font-size: 1.1em; padding: 0.6em 3.5em;">
                    Sign In
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
  <script>
    (function () {
      var hamburger = document.getElementById('hamburger');
      var menuList  = document.getElementById('menu-list');
      if (!hamburger || !menuList) return;

      hamburger.addEventListener('click', function () {
        hamburger.classList.toggle('active');
        menuList.classList.toggle('open');
      });

      // Close menu when a link is clicked
      menuList.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
          hamburger.classList.remove('active');
          menuList.classList.remove('open');
        });
      });
    })();
  </script>

</body>

</html>
