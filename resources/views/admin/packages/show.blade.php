@extends('layouts.admin')

@section('admin-content')
    <div class="container mt-5">
        <h1>Package Details</h1>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">{{ $package->package_name }}</h5>
                <p class="card-text"><strong>Number of Shops:</strong> {{ $package->number_of_shops }}</p>
                <p class="card-text"><strong>Number of Products:</strong> {{ $package->number_of_products }}</p>
                <p class="card-text"><strong>Orders:</strong> {{ $package->orders }}</p>
                <p class="card-text"><strong>Manage Customers:</strong> {{ $package->manage_customers }}</p>
                <p class="card-text"><strong>Price (Monthly):</strong> ${{ number_format($package->price, 2) }}</p>
                <p class="card-text"><strong>Locations:</strong> {{ $package->locations }}</p>
                <p class="card-text"><strong>Status:</strong> 
                    @if($package->status)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Deactivated</span>
                    @endif
                </p>
                <p class="card-text"><strong>Created At:</strong> {{ $package->created_at->format('d M Y') }}</p>
                <p class="card-text"><strong>Updated At:</strong> {{ $package->updated_at->format('d M Y') }}</p>
            </div>
        </div>

        <a href="{{ route('admin.packages.edit', $package->id) }}" class="btn btn-warning mt-3">Edit Package</a>
        <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary mt-3">Back to Packages</a>
    </div>
@endsection
