@extends('layouts.customer')

@section('customer-content')
<style>
    .pagination {
        margin: 0;
    }
</style>

<div class="container my-5">
    <h1 class="mb-4 text-center">Available Packages</h1>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
        </div>
    @endif

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="alert alert-danger text-center">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Card for Packages --}}
    <div class="card shadow-sm">
        {{-- Card Header --}}
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <h5 class="mb-0">Packages</h5>
        </div>

        {{-- Card Body --}}
        <div class="card-body">
           

            @if($packages->isEmpty())
                <div class="row">
                    <div class="col-md-9">
                        <div class="alert alert-warning">
                            No packages available.
                        </div>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <a href="{{ route('packages') }}" class="btn btn-primary" target="_blank">Subscribe Now</a>
                    </div>
                </div>
            @else
                <table class="table table-striped table-hover align-middle" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Package Name</th>
                            <th>Local Price</th>
                            <th>Status</th>
                            <th>Subscription Info</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                            @php
                                // Determine if this package is the active one.
                                $isActive = $activeUserPackage && ((string)$activeUserPackage->package_id === (string)$package->id);
                            @endphp
                            <tr>
                                <td>{{ $package->package_name }}</td>
                                <td>£{{ number_format($package->price, 2) }} / Month</td>
                                <td>
                                    @if($isActive)
                                        <span class="badge bg-success">Active Package</span>
                                    @else
                                        <span class="badge bg-secondary">Not Subscribed</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Only show subscription details for the active package if available --}}
                                    @if($isActive && $activeSubscription && $activeSubscription->current_period_start && $activeSubscription->current_period_end)
                                        @php
                                            $buyDate = \Carbon\Carbon::parse($activeSubscription->current_period_start)->format('M d, Y H:i:s');
                                            $end = \Carbon\Carbon::parse($activeSubscription->current_period_end);
                                            $now = \Carbon\Carbon::now();
                                            $timeRemaining = $end->diffForHumans($now, ['parts' => 3, 'short' => true]);
                                        @endphp
                                        <span class="badge bg-info">
                                            Bought: {{ $buyDate }} | Expires: {{ $timeRemaining }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($activeUserPackage)
                                        {{-- If user already has an active package, show a Switch button for non-active packages --}}
                                        @if(!$isActive && $activeSubscription)
                                        <a href="{{ route('customer.subscription.switchConfirm', [
                                              'subscription' => $activeSubscription->id,
                                              'priceId'      => $package->stripe_price_id
                                          ]) }}" class="btn btn-warning btn-sm">
                                          Switch to {{ $package->package_name }}
                                        </a>
                                        @endif
                                    @else
                                        {{-- If no active package, show a Subscribe button --}}
                                        <a href="{{ route('customer.subscribe.form', ['package' => $package->id]) }}" class="btn btn-primary btn-sm">
                                            Subscribe
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination Links --}}
                
            @endif
        </div>
    </div>
</div>
@endsection
