@extends('layouts.customer')

@section('customer-content')
<style>
    .pagination {
        margin: 0;
    }
</style>

<div class="container my-5">
    <h2 class="mb-4">My Purchase History</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($userPackages->isEmpty())
        <div class="row">
            <div class="col-md-9">
                <div class="alert alert-warning">
                    You have not purchased any packages yet.
                </div>
            </div>
            <div class="col-md-3">
                <a href="{{ route('packages') }}" class="btn btn-primary" target="_blank">Buy Now</a>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <!-- Card Header with Title -->
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <i class="fas fa-receipt me-2"></i>
                <h5 class="mb-0">Purchase Records</h5>
            </div>

            <!-- Card Body -->
            <div class="card-body">
                <!-- Filter / Search / Per Page Form -->
                <form method="GET" action="{{ route('customer.purchases') }}">
                    <div class="row mb-3">
                        <!-- Left Column: Search Box -->
                        <div class="col-md-6 d-flex align-items-center">
                            <label for="searchPurchases" class="form-label fw-bold me-2 mb-0">
                                Search Purchases:
                            </label>
                            <input type="text"
                                   name="search"
                                   id="searchPurchases"
                                   class="form-control form-control-sm"
                                   placeholder="Search..."
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-sm btn-light ms-2">
                                Search
                            </button>
                        </div>

                        <!-- Right Column: Show Entries Per Page -->
                        <div class="col-md-6 d-flex align-items-center justify-content-md-end mt-2 mt-md-0">
                            <label for="entriesSelect" class="form-label fw-bold me-2 mb-0">
                                Show entries per page:
                            </label>
                            <select name="per_page"
                                    id="entriesSelect"
                                    class="form-select form-select-sm"
                                    style="width: auto;"
                                    onchange="this.form.submit()">
                                <option value="10"  {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="20"  {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                <option value="50"  {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                </form>

                <!-- Purchase History Table -->
                <table class="table table-striped table-hover align-middle" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Package Name</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Number of Shops</th>
                            <th>Number of Products</th>
                            <th>Orders</th>
                            <th>Manage Customers</th>
                            <th>Locations</th>
                            <th>Purchase Date</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($userPackages as $userPackage)
                            <tr>
                                <td><strong>{{ $userPackage->package->package_name }}</strong></td>
                                <td>${{ number_format($userPackage->package->price, 2) }} / Month</td>
                                <td>
                                    @if($userPackage->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Deactivated</span>
                                    @endif
                                </td>
                                <td>{{ $userPackage->package->number_of_shops }}</td>
                                <td>{{ $userPackage->package->number_of_products }}</td>
                                <td>{{ $userPackage->package->orders }}</td>
                                <td>{{ $userPackage->package->manage_customers }}</td>
                                <td>{{ $userPackage->package->locations }}</td>
                                <td>{{ $userPackage->created_at->format('M d, Y') }}</td>
                                <td>{{ $userPackage->expiry_date }}</td>
                                <td>
                                    @if($userPackage->status == 1)
                                        <button class="btn btn-warning btn-sm" disabled>
                                            Current Plan
                                        </button>
                                    @else
                                        <form action="{{ route('customer.packages.purchase') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="package_id"
                                                   value="{{ $userPackage->package_id }}">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                Renew
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination Info and Links -->
                <div class="d-flex align-items-center justify-content-between mt-3">
                    <div class="text-muted small">
                        @if($userPackages->total() > 0)
                            Showing {{ $userPackages->firstItem() }}
                            to {{ $userPackages->lastItem() }}
                            of {{ $userPackages->total() }} entries
                        @else
                            Showing 0 to 0 of 0 entries
                        @endif
                    </div>
                    <div>
                        {{ $userPackages->links() }}
                    </div>
                </div>

            </div> <!-- card-body -->
        </div> <!-- card -->
    @endif
</div>
@endsection
