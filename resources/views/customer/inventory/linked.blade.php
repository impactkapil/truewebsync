@extends('layouts.customer')

@section('customer-content')
<div class="container-fluid">
    <h2>Linked Products</h2>

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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Filter Products</h5>
        <form class="d-flex">
    <select class="form-select me-2" onchange="window.location.href=this.value;">
        <option value="{{ route('customer.shopify.fetchSelectedProducts') }}"
            {{ request('filter') === null ? 'selected' : '' }}>
            All Products
        </option>
        <option value="{{ route('customer.shopify.fetchLinkedProducts') }}"
            {{ request('filter') === 'linked' ? 'selected' : '' }}>
            Linked Products
        </option>
        <option value="{{ route('customer.shopify.fetchSelectedProducts', ['filter' => 'unlinked']) }}"
            {{ request('filter') === 'unlinked' ? 'selected' : '' }}>
            Unlinked Products
        </option>
    </select>
</form>


</div>
    @if($linkedProducts->count())
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white d-flex align-items-center">
                <i class="fas fa-link me-2"></i>
                <h5 class="mb-0">All Linked Products</h5>
            </div>
            <div class="card-body">
                <table id="linkedInventoryTable"
                       class="table table-striped table-hover align-middle"
                       style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th>Sr.</th>
                            <th>Shopify Domain</th>
                            <th>Product Title</th>
                            <th>Variant SKU</th>
                            <th>Price</th>
                            <th>Inventory</th>
                            <th>Linked With</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0; @endphp
                        @foreach($linkedProducts as $lp)
                            @php
                                $i++;
                                $linkedProductNames = $lp->linkedProducts->pluck('product_title')->implode(', ');
                            @endphp
                            <tr>
                                <td><strong>{{ $i }}</strong></td>
                                <td>
                                    @if($lp->shopifyStore)
                                        <strong>{{ $lp->shopifyStore->shopify_domain }}</strong>
                                    @else
                                        <span class="text-muted">No Store</span>
                                    @endif
                                </td>
                                <td><strong>{{ $lp->product_title ?? 'N/A' }}</strong></td>
                                <td><strong>{{ $lp->variant_sku ?? 'N/A' }}</strong></td>
                                <td><strong>{{ $lp->variant_price ?? 'N/A' }}</strong></td>
                                <td><strong>{{ $lp->variant_inventory ?? 'N/A' }}</strong></td>
                                <td><strong>{{ $linkedProductNames }}</strong></td>
                                <td>
                                    <!-- Unlink Option -->
                                    <form action="{{ route('customer.products.unlink') }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to unlink this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="product_one_id" value="{{ $lp->id }}">
                                        <button type="submit"
                                                class="btn btn-danger btn-sm"
                                                title="Unlink Product">
                                            <i class="fas fa-unlink"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            No linked products found!
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let table = new DataTable('#linkedInventoryTable', {
        dom:
            "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
        pageLength: 10,
        lengthMenu: [10, 20, 50, 100],
        order: [[6, 'desc']],
        language: {
            search: "Search Linked Products:",
            lengthMenu: "Show _MENU_ entries per page",
            zeroRecords: "No matching records found",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries",
            infoFiltered: "(filtered from _MAX_ total entries)"
        }
    });
});
</script>
@endsection
