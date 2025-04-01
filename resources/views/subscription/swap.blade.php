@extends('layouts.customer')

@section('title', 'Change Subscription Plan')

@section('customer-content')
<div class="container my-5">
    <h1 class="mb-4 text-center">Change Your Subscription Plan</h1>

    @if(session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger text-center">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row">
        @foreach($packages as $package)
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
                        <form action="{{ route('customer.subscription.swap') }}" method="POST">
                            @csrf
                            <input type="hidden" name="package_id" value="{{ $package->id }}">
                            <button type="submit" class="btn btn-success w-100">
                                Switch to This Plan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
