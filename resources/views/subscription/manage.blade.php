@extends('layouts.customer')

@section('customer-content')
<style>
    .pagination {
        margin: 0;
    }
</style>

<div class="container my-5">
    <h1 class="mb-4 text-center">Your Subscriptions</h1>

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

    {{-- Card for Manage Subscriptions --}}
    <div class="card shadow-sm">
        {{-- Card Header --}}
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <h5 class="mb-0">Manage Subscriptions</h5>
        </div>

        {{-- Card Body --}}
        <div class="card-body">
            <!-- Search and Per-Page Form -->
            <form method="GET" action="{{ route('customer.subscription.manage') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-6 d-flex align-items-center">
                        <label for="searchSubscriptions" class="form-label fw-bold me-2 mb-0">
                            Search Subscriptions:
                        </label>
                        <input type="text"
                               name="search"
                               id="searchSubscriptions"
                               class="form-control form-control-sm"
                               placeholder="Search..."
                               value="{{ request('search', $search) }}">
                        <button type="submit" class="btn btn-sm btn-light ms-2">
                            Search
                        </button>
                    </div>
                    <div class="col-md-6 d-flex align-items-center justify-content-md-end mt-2 mt-md-0">
                        <label for="entriesSelect" class="form-label fw-bold me-2 mb-0">
                            Show entries per page:
                        </label>
                        <select name="per_page"
                                id="entriesSelect"
                                class="form-select form-select-sm"
                                style="width: auto;"
                                onchange="this.form.submit()">
                            <option value="10"  {{ request('per_page', $perPage) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20"  {{ request('per_page', $perPage) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50"  {{ request('per_page', $perPage) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', $perPage) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
            </form>

            @if($subscriptions->isEmpty())
                <div class="row">
                    <div class="col-md-9">
                        <div class="alert alert-warning">
                            You do not have any subscriptions.
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
                            <th>Stripe Status</th>
                            <th>Active?</th>
                            <th>Valid?</th>
                            <th>Current Period End</th>
                            <th>Time Remaining</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $subscription)
                            <tr>
                                {{-- Use local package name if set; otherwise fallback --}}
                                <td>{{ $subscription->package_name ?? $subscription->name }}</td>

                                <td>
                                    @if(isset($subscription->local_price))
                                        £{{ number_format($subscription->local_price, 2) }} / Month
                                    @else
                                        N/A
                                    @endif
                                </td>

                                <td>{{ $subscription->stripe_status }}</td>
                                <td>{{ $subscription->active() ? 'Yes' : 'No' }}</td>
                                <td>{{ $subscription->valid() ? 'Yes' : 'No' }}</td>

                                <td>
                                    @if($subscription->current_period_end)
                                        {{ \Carbon\Carbon::parse($subscription->current_period_end)->format('M d, Y H:i:s') }}
                                    @else
                                        N/A
                                    @endif
                                </td>

                                <td>
                                    @if($subscription->current_period_end)
                                        @php
                                            $end = \Carbon\Carbon::parse($subscription->current_period_end);
                                            $daysRemaining = \Carbon\Carbon::now()->diffInDays($end);
                                        @endphp
                                        {{ $daysRemaining }} days
                                    @else
                                        N/A
                                    @endif
                                </td>

                                <td>
                                    {{-- Show Cancel button if active --}}
                                    @if($subscription->active() && !$subscription->onGracePeriod())
                                        <form action="{{ route('customer.subscription.cancel', $subscription->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('The package will fully deactivate after crossing the end period time. Are you sure you want to cancel?');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                        </form>
                                    @else
                                    <span class="badge bg-danger">Cancelled</span>
                                    @endif

                                    {{-- Show Download Invoice button if available --}}
                                    @if($subscription->latest_invoice_url)
                                        <a href="{{ $subscription->latest_invoice_url }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fa fa-download"></i>
                                        </a>
                                    @endif

                                    {{-- Show Active Package badge or Switch button --}}
                                    @if(isset($activeUserPackage) && $activeUserPackage->stripe_id == $subscription->stripe_id)
                                        <span class="badge bg-success">Active Package</span>
                                    @elseif($subscription->current_period_end && \Carbon\Carbon::parse($subscription->current_period_end)->isFuture())
                                        <form action="{{ route('customer.subscription.switch', $subscription->stripe_id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm">Switch</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination Links --}}
                <div class="d-flex align-items-center justify-content-between mt-3">
                    <div class="text-muted small">
                        @if($subscriptions->total() > 0)
                            Showing {{ $subscriptions->firstItem() }} to {{ $subscriptions->lastItem() }} of {{ $subscriptions->total() }} entries
                        @else
                            Showing 0 to 0 of 0 entries
                        @endif
                    </div>
                    <div>
                        {{ $subscriptions->links() }}
                    </div>
                </div>
            @endif
        </div> <!-- card-body -->
    </div> <!-- card -->
</div>
@endsection
