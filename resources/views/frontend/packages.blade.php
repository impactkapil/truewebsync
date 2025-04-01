@extends('frontend.layouts.app')

@section('title', 'Our Packages')

@section('content')
    <!-- Packages Section -->
    <div class="container my-5">
        <h1 class="mb-4 text-center">Our Packages</h1>
        <div class="row">
            @forelse($packages as $package)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white text-center">
                            <h3 class="card-title">{{ $package->package_name }}</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item"><strong>No. of Shops:</strong> {{ $package->number_of_shops }}</li>
                                <li class="list-group-item"><strong>No. of Products:</strong> {{ $package->number_of_products }}</li>
                                <li class="list-group-item"><strong>Orders:</strong> {{ $package->orders }}</li>
                                <li class="list-group-item"><strong>Manage Customers:</strong> {{ $package->manage_customers }}</li>
                                <li class="list-group-item"><strong>Locations:</strong> {{ $package->locations }}</li>
                            </ul>
                            <h4 class="text-center mb-3">£{{ number_format($package->price, 2) }} / Month</h4>
                            
                            @auth('customer')
                                <!-- Redirect to checkout page -->
                                <a href="{{ route('customer.subscribe.form', $package->id) }}" class="btn btn-success w-100">
                                    Buy Package
                                </a>
                            @else
                                <!-- Choose Plan Button -->
                                <a href="{{ route('customer.login') }}" class="btn btn-primary w-100">Choose Plan</a>
                            @endauth
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center">No packages available at the moment. Please check back later.</p>
            @endforelse
        </div>
    </div>
@endsection
