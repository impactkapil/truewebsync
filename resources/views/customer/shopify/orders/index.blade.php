@extends('layouts.customer')

@section('customer-content')
<style>
.variant-image {
    max-width: 100px;
    max-height: 100px;
}

/* Remove or adapt the old DataTables classes */
.dataTables_wrapper select.form-select.form-select-sm {
    width: 100%;
}
</style>

<div class="container">
    <h2>Ordersii</h2>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fas fa-shopping-cart me-2"></i>
            <h5 class="mb-0">Orders with Selected Variants</h5>
        </div>
        <div class="card-body">

            <!-- Search & Per-Page Controls -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <form method="GET" action="{{ route('customer.shopify.orders.index') }}"
                    class="d-flex align-items-center w-100">
                    <!-- Preserve existing query params except search, per_page, page -->
                    @foreach(request()->except(['search','per_page','page']) as $param => $value)
                    @if(is_array($value))
                    @foreach($value as $v)
                    <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                    @endforeach
                    @else
                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                    @endif
                    @endforeach

                    <label for="search" class="form-label fw-bold me-2 mb-0" style="white-space: nowrap">
                        Search Orders:
                    </label>
                    <div class="input-group w-100">
                        <input type="text" name="search" id="search" class="form-control form-control-sm"
                            placeholder="Search..." value="{{ request('search') }}">

                        <!-- "Search" button to apply the filter -->
                        <button type="submit" class="input-group-text bg-primary text-white me-3">
                            Search
                        </button>
                    </div>

                    <!-- "Show entries per page" auto-submits on change -->
                    <label for="per_page" class="form-label fw-bold me-2 mb-0" style="white-space: nowrap">
                        Show entries per page:
                    </label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" style="width: auto;"
                        onchange="this.form.submit()">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </form>
            </div>
            <!-- /Search & Per-Page Controls -->

            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Order Name</th>
                        <th>Total Price</th>
                        <th>Line Items (Variants)</th>
                        <th>Order At</th>
                    </tr>
                </thead>
                <tbody>
                    @if($orders->count() > 0)
                    @foreach ($orders as $order)
                    <tr>
                        <td>{{ $order->order_name }}</td>
                        <td>{{ $order->total_price }}</td>
                        <td>
                            @if($order->orderItems->count() > 0)
                            @foreach($order->orderItems as $item)
                            @php
                            // The related selected_products record
                            $sp = $item->selectedProduct;
                            @endphp
                            <div class="mb-2">
                                @if($sp && $sp->variant_image)
                                <img src="{{ $sp->variant_image }}" alt="Variant Image" class="variant-image mb-2">
                                @endif
                                <strong>Variant ID:</strong> {{ $item->shopify_variant_id }}<br>
                                <strong>Order Quantity:</strong> {{ $item->quantity }}<br>
                                <strong>Price (Line Item):</strong> {{ $item->price }}<br>

                                @if($sp)
                                <strong>Product Title:</strong> {{ $sp->product_title }}<br>
                                <strong>Variant Name:</strong> {{ $sp->variant_name }}<br>
                                <strong>Variant SKU:</strong> {{ $sp->variant_sku }}<br>
                                @else
                                <span class="text-muted">
                                    No selected_products match found.
                                </span>
                                @endif
                            </div>
                            @endforeach
                            @else
                            <span class="text-muted">No matching line items found.</span>
                            @endif
                        </td>
                        <td>{{ $order->ordered_at }}</td>
                    </tr>
                    @endforeach
                    @else
                    <!-- If no orders, show exactly one row with 4 <td> cells -->
                    <tr>
                        <td class="text-center text-muted">No orders found</td>
                        <td class="text-center text-muted">—</td>
                        <td class="text-center text-muted">—</td>
                        <td class="text-center text-muted">—</td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <!-- Pagination Info & Links -->
            @if($orders->total() > 0)
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }}
                    of {{ $orders->total() }} entries
                </div>
                <div>
                    {{ $orders->links() }}
                </div>
            </div>
            @endif

        </div> <!-- card-body -->
    </div> <!-- card -->
</div>
@endsection

@section('scripts')
<!-- Remove DataTables references -->
<!--
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
-->

<!-- No special JS needed if you're only doing server-side search/pagination. -->
@endsection