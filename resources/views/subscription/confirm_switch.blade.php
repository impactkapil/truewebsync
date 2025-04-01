@extends('layouts.customer')

@section('title', 'Confirm Package Switch')

@section('customer-content')
<div class="container my-5">
    <h2 class="text-center mb-4">Confirm Package Switch</h2>
    
    <div class="row">
        <!-- Left Column: Package Details -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">New Package: {{ $package->package_name }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>No. of Shops:</strong> {{ $package->number_of_shops }}</li>
                        <li class="list-group-item"><strong>No. of Products:</strong> {{ $package->number_of_products }}</li>
                        <li class="list-group-item"><strong>Orders:</strong> {{ $package->orders }}</li>
                        <li class="list-group-item"><strong>Manage Customers:</strong> {{ $package->manage_customers }}</li>
                        <li class="list-group-item"><strong>Locations:</strong> {{ $package->locations }}</li>
                    </ul>
                    <div class="mt-3">
                        <p>
                            <strong>Monthly Price:</strong> £{{ number_format($package->price, 2) }} / Month
                        </p>
                        <p>
                            <strong>Tax Rate:</strong> {{ $package->tax_rate ?? '20%' }}
                        </p>
                        <p>
                            <strong>Estimated Total (Incl. Tax):</strong>
                            £{{ number_format($package->price * (1 + (isset($package->tax_rate) ? floatval($package->tax_rate)/100 : 0.20)), 2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Proration & Payment Details -->
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Proration Charge</h5>
                    <p>You’re switching your subscription to <strong>{{ $package->package_name }}</strong>.</p>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Amount ({{ strtoupper($currency) }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lines as $line)
                                <tr>
                                    <td>{{ $line['description'] }}</td>
                                    <td class="text-end">{{ $line['amount'] }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td><strong>Total Due</strong></td>
                                <td class="text-end">
                                    <strong>{{ number_format($amountDue, 2) }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <form action="{{ route('customer.subscription.switchStripe', ['subscription' => $subscription->id, 'priceId' => $priceId]) }}" method="POST">
                        @csrf
                        <button class="btn btn-primary w-100 mb-2">Pay &amp; Switch Plan</button>
                    </form>

                    <a href="{{ route('customer.subscription.packages') }}" class="btn btn-link w-100 text-center">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
