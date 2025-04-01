<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Common Head Content -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Customer Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/app.css') }}?{{ time() }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}?{{ time() }}" rel="stylesheet">
    <!-- Load jQuery here if app.js does not include it -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="d-flex min-vh-100 position-relative">
    <nav class="sidebar d-flex flex-column">
        <a href="{{ route('customer.dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto p-2 sticky-top border-bottom">
            <img src="{{ asset('images/true_web_sync_customer_logo.png') }}" alt="MShopify Customer Logo" class="img-fluid shover">
            <img src="{{ asset('images/icon.png') }}" alt="favicon" height="80px" class="img-fluid nohover">
         </a>
        <ul class="nav nav-pills flex-column mb-auto p-1">
            <li class="nav-item">
                <a href="{{ route('customer.dashboard') }}"
                   class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('customer.shopify.stores') }}"
                   class="nav-link {{ request()->routeIs('customer.shopify.stores') || request()->routeIs('customer.shopify.create') ? 'active' : '' }}">
                    <i class="fas fa-store"></i>
                    <span>My Stores</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('customer.shopify.fetchSelectedProducts') }}"
                   class="nav-link {{ request()->routeIs('customer.shopify.fetchSelectedProducts') ? 'active' : '' }}">
                    <i class="fas fa-box"></i>
                    <span>Manage Inventory</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('customer.linkProducts.list') }}"
                   class="nav-link {{ request()->routeIs('customer.linkProducts.list') ? 'active' : '' }}">
                    <i class="fas fa-link"></i>
                    <span>Link Products</span>
                </a>
            </li>
            <!-- <li class="nav-item">
                <a href="{{ route('customer.billing.portal') }}"
                   class="nav-link {{ request()->routeIs('customer.billing.portal') ? 'active' : '' }}">
                    <i class="fas fa-history"></i><span>Live Subscription</span>
                </a>
            </li>-->
            <!-- <li class="nav-item">
                <a href="{{ route('customer.subscription.manage') }}"
                   class="nav-link {{ request()->routeIs('customer.subscription.manage') ? 'active' : '' }}">
                    <i class="fas fa-history"></i> <span>Subscription</span>
                </a>
            </li>  -->
            <li class="nav-item">
                <a href="{{ route('customer.subscription.packages') }}"
                   class="nav-link {{ request()->routeIs('customer.subscription.packages') ? 'active' : '' }}">
                    <i class="fas fa-history"></i> <span>Subscription</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('customer.shopify.orders.index') }}"
                   class="nav-link {{ request()->routeIs('customer.shopify.orders.index') ? 'active' : '' }}">
                    <i class="fas fa-gift"></i>
                    <span>Orders</span>
                </a>
            </li>
            <!-- <li class="nav-item">
                <a href="{{ route('customer.purchases') }}"
                   class="nav-link {{ request()->routeIs('customer.purchases') ? 'active' : '' }}">
                    <i class="fas fa-history"></i>
                    <span>Package</span>
                </a>
            </li> -->
            <li class="nav-item">
                <a href="{{ route('customer.settings.index') }}"
                   class="nav-link {{ request()->routeIs('customer.settings.index') ? 'active' : '' }}">
                    <i class="fas fa-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
        <ul class="nav nav-pills flex-column mt-auto p-1">
            @auth('customer')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('customer.logout') }}"
                   onclick="event.preventDefault(); document.getElementById('customer-logout-form').submit();">
                    <i class="iconify" data-icon="basil:logout-solid"></i>
                    <span>Logout</span>
                </a>
                <form id="customer-logout-form"
                      action="{{ route('customer.logout') }}"
                      method="POST"
                      class="d-none">
                    @csrf
                </form>
            </li>
            @else
                <li class="nav-item">
                    <a href="{{ route('customer.login') }}" class="nav-link">Login</a>
                </li>
            @endauth
        </ul>
    </nav>
    <div class="main-content">
        <main class="py-4">
            @yield('customer-content')
        </main>
    </div>

    <!-- Load app.js (Bootstrap etc.) -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Font Awesome JS (already loaded above, but you can keep if needed) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>

    <!-- Yield custom scripts from child views -->
    @yield('scripts')
</body>
</html>
