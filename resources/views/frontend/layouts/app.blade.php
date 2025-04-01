<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Common Head Content -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

    <!-- Font Awesome (Optional) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link href="{{ asset('css/app.css') }}?{{ time() }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}?{{ time() }}" rel="stylesheet">
</head>

<body>
    <div class="topbar border-bottom small text-bg-dark py-1">
    <div class="container">
        <div class="row small">
            <div class="col-md-6 fw-bold">
                <a href="mailto:info@truewebpro.co.uk" class="link-light text-decoration-none" title="Email">
                    <i class="iconify" data-icon="mdi:email-fast-outline"></i>
                    info@truewebpro.co.uk
                </a>
                <span>|</span>
                <a href="tel:+447492 835206" class="link-light text-decoration-none" title="Call">
                    <i class="iconify" data-icon="line-md:phone-call-loop"></i>
                    +44 7492 835 206
                </a>
            </div>
            <div class="col-md-6 text-md-end fw-bold">
                @guest
                    @if (Route::has('login'))
                        <a href="{{route('login')}}" class="link-light text-decoration-none">Login</a>
                    @endif
                    <span>|</span>
                    @if (Route::has('register'))
                        <a href="{{route('register')}}" class="link-light text-decoration-none">Register</a>
                    @endif
                    <span>|</span>
                @else
                    <a class="link-light text-decoration-none" href="{{ route('logout') }}"
                       onclick="event.preventDefault();
                               document.getElementById('logout-form').submit();">
                        {{ __('Logout') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endguest
                <a href="{{route('about')}}" class="link-light text-decoration-none">About</a>
            </div>
        </div>
    </div>

</div>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('homepage') }}">
                <img src="{{asset('images/true_web_sync_customer_logo.png')}}" alt="TrueWebSync" width="120" height="41">
{{--                <i class="iconify fs-2" data-icon="arcticons:sparss-decsync" style="stroke-width: 3px;"></i>TrueWebSync--}}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#frontendNavbar"
                aria-controls="frontendNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="frontendNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 w-75 nav-justified">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('homepage') ? 'active' : '' }}"
                            href="{{ route('homepage') }}">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Solutions
                        </a>
                        <ul class="dropdown-menu">
                            <li class="submenu">
                                <ul>
                                    <li>
                                        <a class="dropdown-item fw-bold" href="#">Inventory</a>
                                        <ul>
                                            <li>
                                                <a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="material-symbols:inventory-2"></span>
                                                    Inventory Management
                                                    <div class="casoon text-success">Shopify Stores</div>
                                                </a>
                                            </li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="mingcute:inventory-line"></span>
                                                    Inventory & Stock Forcasting
                                                    <div class="casoon">Coming Soon</div>
                                                </a>
                                            </li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="si:barcode-scan-alt-fill"></span>
                                                    Barcode Inventory System
                                                    <div class="casoon">Coming Soon</div>
                                                </a></li>
                                        </ul>
                                    </li>
                                </ul>
                                <ul>
                                    <li>
                                        <a class="dropdown-item fw-bold" href="#">Order Processing</a>
                                        <ul>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="fluent-mdl2:activate-orders"></span>
                                                    Order Management
                                                    <div class="casoon">Coming Soon</div>
                                                </a>
                                            </li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="fluent-mdl2:reservation-orders"></span>
                                                    Order Fullfilment
                                                    <div class="casoon">Coming Soon</div>
                                                </a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="carbon:ibm-watson-orders"></span>
                                                    Returns Management
                                                    <div class="casoon">Coming Soon</div>
                                                </a></li>
                                        </ul>
                                    </li>
                                </ul>
                                <ul>
                                    <li>
                                        <a class="dropdown-item fw-bold" href="#">Shipping & Logistics</a>
                                        <ul>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="la:shipping-fast"></span>
                                                    Shipping Management
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="material-symbols:qr-code-scanner-rounded"></span>
                                                    Scan & Dispatch
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="mdi:courier-check"></span>
                                                    Courier Management
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="bx:map-pin"></span>
                                                    Drop Shipping
                                                    <div class="casoon">Coming Soon</div></a></li>
                                        </ul>
                                    </li>
                                </ul>
                                <ul>
                                    <li>
                                        <a class="dropdown-item fw-bold" href="#">Warehouse Operation</a>
                                        <ul>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="ic:baseline-warehouse"></span>
                                                    Warehouse Management
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="game-icons:box-unpacking"></span>
                                                    Packing & Packing
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="lsicon:toggle-warehouse-x-filled"></span>
                                                    Booking In System
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="material-symbols:print-disabled-outline"></span>
                                                    Paperless Warehousing
                                                    <div class="casoon">Coming Soon</div></a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Integrations
                        </a>
                        <ul class="dropdown-menu">
                            <li class="submenu">
                                <ul>
                                    <li>
                                        <a class="dropdown-item fw-bold" href="#">Ecommerce Platforms</a>
                                        <ul>
                                            <li>
                                                <a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="logos:shopify"></span>
                                                    Shopify
                                                    <div class="casoon text-success">Multi Stores</div>
                                                </a>
                                            </li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="logos:woocommerce-icon"></span>
                                                    WooCommerce
                                                    <div class="casoon">Coming Soon</div></a>
                                            </li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="simple-icons:bigcommerce"></span>
                                                    BigCommerce
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="devicon:magento"></span>
                                                    Magento
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="f7:cart-fill-badge-plus"></span>
                                                    Customized
                                                    <div class="casoon">Coming Soon</div></a></li>
                                        </ul>
                                    </li>
                                </ul>
                                <ul>
                                    <li>
                                        <a class="dropdown-item fw-bold" href="#">MarketPlaces</a>
                                        <ul>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="la:ebay"></span>
                                                    Ebay
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="la:amazon"></span>
                                                    Amazon
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="la:etsy"></span>
                                                    Etsy
                                                    <div class="casoon">Coming Soon</div></a></li>
                                        </ul>
                                    </li>
                                </ul>
                                <ul>
                                    <li>
                                        <a class="dropdown-item fw-bold" href="#">Couriers</a>
                                        <ul>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="cbi:royalmail"></span>
                                                    RoyalMail
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="simple-icons:dpd"></span>
                                                    DPD
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="carbon:delivery-parcel"></span>
                                                    Parcelforce
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="logos:hermes"></span>
                                                    Hermes
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="mdi:courier-check"></span>
                                                    Others
                                                    <div class="casoon">Coming Soon</div></a></li>
                                        </ul>
                                    </li>
                                </ul>
                                <ul>
                                    <li>
                                        <a class="dropdown-item fw-bold" href="#">Accounting</a>
                                        <ul>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="simple-icons:quickbooks"></span>
                                                    Quickbooks
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="simple-icons:sage"></span>
                                                    Sage
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="simple-icons:xero"></span>
                                                    Xero
                                                    <div class="casoon">Coming Soon</div></a></li>
                                            <li><a class="dropdown-item" href="#">
                                                    <span class="iconify" data-icon="map:accounting"></span>
                                                    Customized
                                                    <div class="casoon">Coming Soon</div></a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}"
                           href="{{ route('about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('packages') ? 'active' : '' }}"
                            href="{{ route('packages') }}">Packages</a>
                    </li>
                    <!-- Add more navigation links as needed -->
                    @guest('customer')
{{--                        <li class="nav-item">--}}
{{--                            <a class="nav-link" href="{{ route('admin.login') }}">Admin Login</a>--}}
{{--                        </li>--}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('customer.login') }}">Login</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::guard('customer')->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="">Profile</a></li>
                                <li><a class="dropdown-item" href="">Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customer.logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('customer-logout-form').submit();">
                                        Logout
                                    </a>
                                    <form id="customer-logout-form" action="{{ route('customer.logout') }}" method="POST"
                                        class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                    <a class="btn btn-danger rounded-0" href="{{ route('packages') }}">Contact Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container">
        <!-- @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif -->

        @if($errors->any())
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <div class="footer bg-light text-center py-2">
        <div class="container">
            <span class="text-body">&copy; {{ date('Y') }} TrueWebSync. All Rights Reserved.</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
    @yield('scripts')
    <script src="{{ asset('js/main.js') }}?{{ time() }}"></script>
</body>

</html>
