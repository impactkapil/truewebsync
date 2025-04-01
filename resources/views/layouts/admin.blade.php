<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Common Head Content -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Admin Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/app.css') }}?{{ time() }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}?{{ time() }}" rel="stylesheet">
    <!-- Load jQuery here if app.js does not include it -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Font Awesome (Optional) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="d-flex min-vh-100">
    <!-- Admin Sidebar -->
    <nav class="sidebar d-flex flex-column p-3">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4">Admin Panel</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.packages.index') }}" class="nav-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                    <i class="fas fa-box-open me-2"></i> Packages
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i> Customers
                </a>
            </li>
            <!-- Add more admin links as needed -->
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownAdmin" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="{{ Auth::guard('admin')->user()->avatar ?? 'https://via.placeholder.com/30' }}" alt="" width="30" height="30" class="rounded-circle me-2">
                <strong>{{ Auth::guard('admin')->user()->name }}</strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownAdmin">
                <li><a class="dropdown-item" href="">Profile</a></li>
                <li><a class="dropdown-item" href="">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.logout') }}"
                       onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                        Logout
                    </a>
                    <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Admin Main Content -->
    <div class="main-content">
        <main class="py-4">
            @yield('admin-content')
        </main>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
