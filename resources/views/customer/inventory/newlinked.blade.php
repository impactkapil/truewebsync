@extends('layouts.customer')

@section('customer-content')
<style>
    /* Basic styling adjustments */
    .dataTables_wrapper select.form-select.form-select-sm {
        width: 100%;
    }
    .search-results {
        z-index: 9999;
        position: absolute;
        background-color: #fff;
        border: 1px solid #ddd;
    }
</style>

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-md-5">
            <h2>Manage Inventory</h2>
        </div>
        <div class="col-md-7">
            <!-- Filter by Secondary Shop -->
            <form action="{{ route('customer.linkProducts.list') }}" method="GET" class="d-inline">
                <label for="shop_id" class="form-label me-2 fw-bold">Filter By Secondary Shop:</label>
                <select name="shop_id" id="shop_id" class="form-select d-inline" style="width: auto;" onchange="this.form.submit()">
                    <option value="">All Secondary Shops</option>
                    @foreach($secondaryShops as $shop)
                        <option value="{{ $shop->id }}" {{ isset($selectedShopId) && $selectedShopId == $shop->id ? 'selected' : '' }}>
                            {{ $shop->store_name ?? ('Shop #'.$shop->id) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="inventoryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="linked-tab" data-bs-toggle="tab" data-bs-target="#linked" type="button" role="tab" aria-controls="linked" aria-selected="true">Linked</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="unlinked-tab" data-bs-toggle="tab" data-bs-target="#unlinked" type="button" role="tab" aria-controls="unlinked" aria-selected="false">Unlinked</button>
        </li>
    </ul>

    <div class="tab-content" id="inventoryTabsContent">
        <!-- LINKED PRODUCTS TAB -->
        <div class="tab-pane fade show active p-3" id="linked" role="tabpanel" aria-labelledby="linked-tab">
            <h4>Linked Products</h4>

            @if($linkedProducts->count())
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Linked Products</h5>
                    </div>
                    <div class="card-body">
                        <!-- Search & Per-Page for LINKED -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <form method="GET" action="{{ route('customer.linkProducts.list') }}" class="d-flex align-items-center">
                                @foreach(request()->except(['linked_search','linked_per_page','linked_page']) as $param => $value)
                                    @if(is_array($value))
                                        @foreach($value as $v)
                                            <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                    @endif
                                @endforeach
                                <label for="linked_search" class="form-label fw-bold me-2 mb-0">Search Linked Products:</label>
                                <input type="text" name="linked_search" id="linked_search" class="form-control form-control-sm me-3" placeholder="Search..." value="{{ request('linked_search') }}">
                                <label for="linked_per_page" class="form-label fw-bold me-2 mb-0">Show entries per page:</label>
                                <select name="linked_per_page" id="linked_per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                    <option value="10"  {{ request('linked_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20"  {{ request('linked_per_page') == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50"  {{ request('linked_per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('linked_per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </form>
                        </div>
                        <!-- Linked Products Table -->
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product Image</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Linked With</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($linkedProducts as $product)
                                    <tr>
                                        <td>
                                            @if($product->variant_image)
                                                <img src="{{ $product->variant_image }}" class="img-fluid" alt="Product Image" style="max-width: 100px; max-height: 100px">
                                            @else
                                                <p>No Image</p>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $product->product_title ?? 'N/A' }}</strong><br>
                                            <small>{{ $product->variant_name ?? 'N/A' }}</small>
                                        </td>
                                        <td>{{ $product->variant_sku ?? 'N/A' }}</td>
                                        <td>
                                            @foreach($product->linkedProducts as $lp)
                                                <div class="mb-2">
                                                    @if($lp->variant_image)
                                                        <img src="{{ $lp->variant_image }}" class="img-fluid" alt="Product Image" style="max-width: 100px; max-height: 100px">
                                                    @else
                                                        <p>No Image</p>
                                                    @endif
                                                    <strong>{{ $lp->product_title }}</strong> (SKU: {{ $lp->variant_sku }})<br>
                                                    <small>{{ $lp->variant_name }}</small>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            <form action="{{ route('customer.products.unlinkProducts') }}" method="POST" onsubmit="return confirm('Are you sure you want to unmap these products?');">
                                                @csrf
                                                <input type="hidden" name="product_one_id" value="{{ $product->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-unlink"></i> Unmap</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination (Linked) -->
                        @if($linkedProducts->total() > 0)
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Showing {{ $linkedProducts->firstItem() }} to {{ $linkedProducts->lastItem() }} of {{ $linkedProducts->total() }} entries
                                </div>
                                <div>
                                    {{ $linkedProducts->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-info mt-3">No linked products found.</div>
            @endif
        </div>
        <!-- END LINKED TAB -->

        <!-- UNLINKED PRODUCTS TAB -->
        <!-- UNLINKED PRODUCTS TAB -->
<div class="tab-pane fade p-3" id="unlinked" role="tabpanel" aria-labelledby="unlinked-tab">
    <h4>Unlinked Products (Secondary Shop)</h4>

    @if($unlinkedProducts->count())
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Unlinked Products</h5>
            </div>
            <div class="card-body">

                <!-- Bulk delete form (unchanged) -->
                <form action="{{ route('customer.products.delete') }}"
                      method="POST"
                      onsubmit="return confirm('Are you sure you want to delete the selected product(s)?');"
                      id="multi-delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn btn-danger mb-3 d-none"
                            id="multi-delete-btn">
                        Delete Selected
                    </button>
                </form>

                <!-- UPDATED: Search & Per-Page controls with a "Search" button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <form method="GET"
                          action="{{ route('customer.linkProducts.list') }}"
                          class="d-flex align-items-center">

                        <!-- Preserve existing query params except unlinked_search, unlinked_per_page, unlinked_page -->
                        @foreach(request()->except(['unlinked_search','unlinked_per_page','unlinked_page']) as $param => $value)
                            @if(is_array($value))
                                @foreach($value as $v)
                                    <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <!-- Label & text input for unlinked search -->
                        <label for="unlinked_search"
                               class="form-label fw-bold me-2 mb-0">
                            Search Unlinked Products:
                        </label>
                        <input type="text"
                               name="unlinked_search"
                               id="unlinked_search"
                               class="form-control form-control-sm me-2"
                               placeholder="Search..."
                               value="{{ request('unlinked_search') }}">

                        <!-- NEW: A "Search" button to submit the form -->
                        <button type="submit" class="btn btn-sm btn-primary me-3">
                            Search
                        </button>

                        <!-- "Show entries per page" still auto-submits on change -->
                        <label for="unlinked_per_page" class="form-label fw-bold me-2 mb-0">
                            Show entries per page:
                        </label>
                        <select name="unlinked_per_page"
                                id="unlinked_per_page"
                                class="form-select form-select-sm"
                                style="width: auto;"
                                onchange="this.form.submit()">
                            <option value="10"  {{ request('unlinked_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20"  {{ request('unlinked_per_page') == 20 ? 'selected' : '' }}>20</option>
                            <option value="50"  {{ request('unlinked_per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('unlinked_per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                </div>
                <!-- /Search & Per-Page controls -->

                <table id="unlinkedProductsTable"
                       class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>
                                <!-- <input type="checkbox"
                                       onclick="toggleSelectAll(this, 'unlinked')"> -->
                            </th>
                            <th>Product Image</th>
                            <th>Product Name</th>
                            <th>SKU</th>
                            <th>Matching Product (Master Shop)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                                @foreach($unlinkedProducts as $product)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_unlinked_products[]" value="{{ $product->id }}">
                                        </td>
                                        <td>
                                            @if($product->variant_image)
                                                <img src="{{ $product->variant_image }}" class="img-fluid" alt="Product Image" style="max-width: 100px; max-height: 100px">
                                            @else
                                                <p>No Image</p>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $product->product_title ?? 'N/A' }}</strong><br>
                                            <small>{{ $product->variant_name ?? 'N/A' }}</small>
                                        </td>
                                        <td>{{ $product->variant_sku ?? 'N/A' }}</td>
                                        <td style="position: relative;">
                                            <!-- Matching Product Search Field -->
                                            <input type="text" class="form-control search-matching-product" placeholder="Search Matching Product" data-product-id="{{ $product->id }}">
                                            <input type="hidden" class="matching-product-id" value="">
                                            <div class="search-results d-none"></div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary link-button" data-row-product-id="{{ $product->id }}">
                                                <i class="fa fa-link"></i> Map
                                            </button>
                                            <form action="{{ route('customer.products.delete') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="product_ids[]" value="{{ $product->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                </table>

                <!-- Pagination (Unlinked) -->
                @if($unlinkedProducts->total() > 0)
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ $unlinkedProducts->firstItem() }}
                            to {{ $unlinkedProducts->lastItem() }}
                            of {{ $unlinkedProducts->total() }} entries
                        </div>
                        <div>
                            {{ $unlinkedProducts->links() }}
                        </div>
                    </div>
                @endif

            </div> <!-- card-body -->
        </div> <!-- card -->
    @else
        <div class="alert alert-info mt-3">
            No unlinked products found (in secondary shops).
        </div>
    @endif
</div>
<!-- END UNLINKED TAB -->

        <!-- END UNLINKED TAB -->
    </div> <!-- tab-content -->
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Tab persistence using localStorage ---
    var activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        var triggerEl = document.querySelector('#inventoryTabs button[data-bs-target="' + activeTab + '"]');
        if (triggerEl) {
            var tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }
    document.querySelectorAll('#inventoryTabs button[data-bs-toggle="tab"]').forEach(function(el) {
        el.addEventListener('shown.bs.tab', function(e) {
            localStorage.setItem('activeTab', e.target.getAttribute('data-bs-target'));
        });
    });

    // --- Bulk Delete Logic for Unlinked Products ---
    document.querySelectorAll('input[name="selected_unlinked_products[]"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const countChecked = document.querySelectorAll('input[name="selected_unlinked_products[]"]:checked').length;
            if (countChecked > 0) {
                document.getElementById('multi-delete-btn').classList.remove('d-none');
            } else {
                document.getElementById('multi-delete-btn').classList.add('d-none');
            }
        });
    });
    document.getElementById('multi-delete-btn').addEventListener('click', function(e) {
        e.preventDefault();
        let selectedIds = [];
        document.querySelectorAll('input[name="selected_unlinked_products[]"]:checked').forEach(function(el) {
            selectedIds.push(el.value);
        });
        if (selectedIds.length === 0) {
            alert('No products selected for deletion.');
            return;
        }
        // Clear previous hidden inputs
        document.querySelectorAll('#multi-delete-form input[name="product_ids[]"]').forEach(function(old) {
            old.remove();
        });
        // Add new hidden inputs
        selectedIds.forEach(function(id) {
            let hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'product_ids[]';
            hidden.value = id;
            document.getElementById('multi-delete-form').appendChild(hidden);
        });
        document.getElementById('multi-delete-form').submit();
    });

    // --- Matching Product Search Functionality ---
    // Attach keyup listener to each matching product search input
    document.querySelectorAll('.search-matching-product').forEach(function(input) {
        input.addEventListener('keyup', function(e) {
            let query = input.value.trim();
            let resultsContainer = input.parentElement.querySelector('.search-results');
            if (!query || query.length < 3) {
                resultsContainer.classList.add('d-none');
                resultsContainer.innerHTML = '';
                return;
            }
            // Use your existing route for searching master shop products (unlinked)
            fetch("{{ route('customer.shopify.searchUnlinkedProducts') }}?query=" + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.length) {
                        data.forEach(function(item) {
                            let div = document.createElement('div');
                            div.className = 'p-2 search-result-item';
                            div.style.cursor = 'pointer';
                            div.setAttribute('data-id', item.id);
                            div.setAttribute('data-title', item.product_title);
                            div.innerHTML = item.product_title + " (SKU: " + item.variant_sku + ", Variant: " + item.variant_name + ")";
                            resultsContainer.appendChild(div);
                        });
                        resultsContainer.classList.remove('d-none');
                    } else {
                        resultsContainer.innerHTML = '<div class="p-2">No results found</div>';
                        resultsContainer.classList.remove('d-none');
                    }
                })
                .catch(err => {
                    console.error('Error fetching matching products:', err);
                });
        });
    });
    // Delegate click event for search result items (using document)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('search-result-item')) {
            let item = e.target;
            let title = item.getAttribute('data-title');
            let matchingId = item.getAttribute('data-id');
            let parentTd = item.closest('td');
            parentTd.querySelector('.search-matching-product').value = title;
            parentTd.querySelector('.matching-product-id').value = matchingId;
            let resultsContainer = parentTd.querySelector('.search-results');
            resultsContainer.classList.add('d-none');
            resultsContainer.innerHTML = '';
        }
    });

    // --- Map Button Logic ---
    // Use event delegation on the document to catch clicks on any link-button (or its child)
    document.addEventListener('click', function(e) {
        let mapBtn = e.target.closest('.link-button');
        if (!mapBtn) return;
        let rowProductId = mapBtn.getAttribute('data-row-product-id');
        let parentTr = mapBtn.closest('tr');
        let productTwoInput = parentTr.querySelector('.matching-product-id');
        let productTwoId = productTwoInput ? productTwoInput.value : '';
        if (!productTwoId) {
            alert('Please select a matching product first.');
            return;
        }
        // Send the mapping request via AJAX
        fetch("{{ route('customer.shopify.linkProducts') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_one_id: rowProductId,
                product_two_id: productTwoId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            alert('This product is already linked or an error occurred.');
            console.error(err);
        });
    });
});
</script>
@endsection
