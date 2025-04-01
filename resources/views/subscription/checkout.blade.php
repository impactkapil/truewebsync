@extends('layouts.customer')

@section('title', 'Checkout')

@section('customer-content')
<div class="container my-5">
    <h1 class="mb-4 text-center">Checkout: {{ $package->package_name }}</h1>
    
    <div class="row">
        <!-- Left Column: Package Details (excluding pricing) -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="card-title">Package Details</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item"><strong>Package Name:</strong> {{ $package->package_name }}</li>
                        <li class="list-group-item"><strong>No. of Shops:</strong> {{ $package->number_of_shops }}</li>
                        <li class="list-group-item"><strong>No. of Products:</strong> {{ $package->number_of_products }}</li>
                        <li class="list-group-item"><strong>Orders:</strong> {{ $package->orders }}</li>
                        <li class="list-group-item"><strong>Manage Customers:</strong> {{ $package->manage_customers }}</li>
                        <li class="list-group-item"><strong>Locations:</strong> {{ $package->locations }}</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Pricing & Payment Details -->
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-secondary text-white text-center">
                    <h3 class="card-title">Payment Details</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-center"><strong>Monthly Price:</strong> £{{ number_format($package->price, 2) }} / Month</p>
                        <p class="text-center"><strong>Tax Rate:</strong> 20%</p>
                        <p class="text-center"><strong>Estimated Total (Incl. Tax):</strong> £{{ number_format($package->price * 1.20, 2) }}</p>
                        <p class="text-center"><strong>Subscription Start Date:</strong> {{ now()->format('M d, Y') }}</p>
                    </div>
                    <form action="{{ route('customer.subscribe') }}" method="POST">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}" />
                        <button type="submit" class="btn btn-primary w-100">
                            Proceed to Secure Checkout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Stripe.js inclusion if needed -->
<script src="https://js.stripe.com/v3/"></script>
@endsection
