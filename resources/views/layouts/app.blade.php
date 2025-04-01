<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Existing Head Content -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/app.css') }}?{{ time() }}" rel="stylesheet">

    <!-- Optional: Include Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            flex-shrink: 0;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link.active {
            background-color: #495057;
        }
        /* Main Content Styles */
        .main-content {
            flex-grow: 1;
            padding: 20px;
        }
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
            }
            body {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    @auth
        <!-- Sidebar -->
        <nav class="sidebar d-flex flex-column p-3">
            <a href="{{ url('/') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-4">{{ config('app.name', 'Laravel') }}</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" aria-current="page">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile') }}" class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="{{ route('settings') }}" class="nav-link {{ request()->routeIs('settings') ? 'active' : '' }}">
                        <i class="fas fa-cogs me-2"></i> Settings
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports') }}" class="nav-link {{ request()->routeIs('reports') ? 'active' : '' }}">
                        <i class="fas fa-chart-line me-2"></i> Reports
                    </a>
                </li>
                <!-- Add more options as needed -->
            </ul>
            <hr>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ Auth::user()->avatar ?? 'https://via.placeholder.com/30' }}" alt="" width="30" height="30" class="rounded-circle me-2">
                    <strong>{{ Auth::user()->name }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="{{ route('profile') }}">Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('settings') }}">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
    @endauth

    <!-- Main Content -->
    <div class="main-content">
        @auth
            <main class="py-4">
                @yield('content')
            </main>
        @else
            <!-- Content for Guests (e.g., Login, Register Pages) -->
            <main class="py-4">
                @yield('content')
            </main>
        @endauth
    </div>

    <!-- Bootstrap JS and dependencies (Popper.js) -->
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- Optional: Include Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
