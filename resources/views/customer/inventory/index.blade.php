@extends('layouts.customer')

@section('customer-content')
<style>
/* Existing styling plus anything else you need. */
/* Force the "Show _MENU_ entries per page" label & select onto one line, if desired. */

.dropdown-menu {
  padding: 1rem !important;
  background-color: #F5F9FC;
  border: 1px solid #ddd;
  max-height: 600px;
  overflow-y: auto;
  min-width: 420px;
  border-radius: 6px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* ... your other custom styles remain ... */
.active-filters-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}
.scrollable-options {
  max-height: 200px;
  overflow-y: auto;
}
.scrollable-chips {
  max-height: 140px;
  overflow-y: auto;
}
</style>

<div class="container-fluid">
    <h2>Manage Inventory</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- BOX #1: Filters, Stock Buttons, LARGE Search, and "Show entries per page" -->
<div class="d-flex align-items-center mb-3">
    <!-- Inventory Filter Buttons (All Stocks, Low Stock, Out of Stock) -->
    <div class="btn-group me-3" role="group" aria-label="Inventory Filter">
        <a href="{{ route('customer.shopify.fetchSelectedProducts', array_merge(request()->all(), ['filter' => 'all'])) }}"
           class="btn btn-primary {{ request('filter') == 'all' ? 'active' : '' }}">
            All Stocks
        </a>
        <a href="{{ route('customer.shopify.fetchSelectedProducts', array_merge(request()->all(), ['filter' => 'low_stock'])) }}"
           class="btn btn-warning {{ request('filter') == 'low_stock' ? 'active' : '' }}">
            Low Stock
        </a>
        <a href="{{ route('customer.shopify.fetchSelectedProducts', array_merge(request()->all(), ['filter' => 'out_of_stock'])) }}"
           class="btn btn-danger {{ request('filter') == 'out_of_stock' ? 'active' : '' }}">
            Out of Stock
        </a>
    </div>
</div>
    @php
        // Check if ANY filter is used for toggling the "Active Filters" row
        $anyFilters = (
            request('variant_name') ||
            request('variant_sku')  ||
            request('barcode')      ||
            (request('tags') && count(request('tags'))>0) ||
            (request('product_types') && count(request('product_types'))>0) ||
            (request('brands') && count(request('brands'))>0) ||
            request('stock_min')    ||
            request('stock_max')    ||
            (request('filter') && request('filter') !== 'all') ||
            request('locations')
        );

        // Count total active filters for possibly adding scroll
        $activeFiltersCount = 0;
        if(request('variant_name')) $activeFiltersCount++;
        if(request('variant_sku'))  $activeFiltersCount++;
        if(request('barcode'))      $activeFiltersCount++;
        if(request('stock_min') || request('stock_max')) $activeFiltersCount++;
        if(request('filter') && request('filter') !== 'all') $activeFiltersCount++;
        if(request('locations') && is_array(request('locations'))) $activeFiltersCount++;
        if(request('product_types') && is_array(request('product_types'))) $activeFiltersCount++;
        if(request('brands') && is_array(request('brands'))) $activeFiltersCount++;
        if(request('tags') && is_array(request('tags'))) $activeFiltersCount++;
    @endphp

    <!-- BOX #2: Active Filters on SECOND line -->
    @if($anyFilters)
    <div class="active-filters-row mb-3 @if($activeFiltersCount > 5) scrollable-chips @endif">
        <span class="fw-bold">Active Filters:</span>
        <div class="d-flex flex-wrap gap-2">
            @if(request('variant_name'))
                <div class="d-inline-block position-relative">
                    <span class="badge bg-primary"
                          data-bs-toggle="dropdown"
                          style="cursor: pointer;">
                        Variant Name: {{ request('variant_name') }}
                        <i class="fas fa-caret-down ms-1"></i>
                    </span>
                    <div class="dropdown-menu p-3">
                        <form method="GET" action="{{ route('customer.shopify.fetchSelectedProducts') }}">
                            @foreach(request()->except(['variant_name','page']) as $param => $value)
                                @if(is_array($value))
                                    @foreach($value as $k => $v)
                                        <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label class="form-label"><strong>Variant Name</strong></label>
                            <input type="text"
                                   name="variant_name"
                                   class="form-control mb-2"
                                   value="{{ request('variant_name') }}">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('customer.shopify.fetchSelectedProducts', request()->except('variant_name')) }}"
                                   class="btn btn-sm btn-light">Clear</a>
                                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if(request('variant_sku'))
                <div class="d-inline-block position-relative">
                    <span class="badge bg-primary"
                          data-bs-toggle="dropdown"
                          style="cursor: pointer;">
                        Variant SKU: {{ request('variant_sku') }}
                        <i class="fas fa-caret-down ms-1"></i>
                    </span>
                    <div class="dropdown-menu p-3">
                        <form method="GET" action="{{ route('customer.shopify.fetchSelectedProducts') }}">
                            @foreach(request()->except(['variant_sku','page']) as $param => $value)
                                @if(is_array($value))
                                    @foreach($value as $k => $v)
                                        <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label class="form-label"><strong>Variant SKU</strong></label>
                            <input type="text"
                                   name="variant_sku"
                                   class="form-control mb-2"
                                   value="{{ request('variant_sku') }}">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('customer.shopify.fetchSelectedProducts', request()->except('variant_sku')) }}"
                                   class="btn btn-sm btn-light">Clear</a>
                                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if(request('barcode'))
                <div class="d-inline-block position-relative">
                    <span class="badge bg-primary"
                          data-bs-toggle="dropdown"
                          style="cursor: pointer;">
                        Barcode: {{ request('barcode') }}
                        <i class="fas fa-caret-down ms-1"></i>
                    </span>
                    <div class="dropdown-menu p-3">
                        <form method="GET" action="{{ route('customer.shopify.fetchSelectedProducts') }}">
                            @foreach(request()->except(['barcode','page']) as $param => $value)
                                @if(is_array($value))
                                    @foreach($value as $k => $v)
                                        <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label class="form-label"><strong>Barcode</strong></label>
                            <input type="text"
                                   name="barcode"
                                   class="form-control mb-2"
                                   value="{{ request('barcode') }}">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('customer.shopify.fetchSelectedProducts', request()->except('barcode')) }}"
                                   class="btn btn-sm btn-light">Clear</a>
                                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if(request('product_types'))
                @php
                    $selectedProductTypes = request('product_types');
                    $displayProductTypes  = implode(', ', $selectedProductTypes);
                @endphp
                <div class="d-inline-block position-relative">
                    <span class="badge bg-primary"
                          data-bs-toggle="dropdown"
                          style="cursor: pointer;">
                        Product Type: {{ $displayProductTypes }}
                        <i class="fas fa-caret-down ms-1"></i>
                    </span>
                    <div class="dropdown-menu p-3">
                        <form method="GET" action="{{ route('customer.shopify.fetchSelectedProducts') }}">
                            @foreach(request()->except(['product_types','page']) as $param => $value)
                                @if(is_array($value))
                                    @foreach($value as $k => $v)
                                        <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label class="form-label"><strong>Product Types</strong></label>
                            <div>
                                @foreach($allProductTypes as $type)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="product_types[]"
                                               value="{{ $type }}"
                                               id="active_producttype_{{ $loop->index }}"
                                               @if(in_array($type, $selectedProductTypes)) checked @endif>
                                        <label class="form-check-label" for="active_producttype_{{ $loop->index }}">
                                            {{ $type }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <a href="{{ route('customer.shopify.fetchSelectedProducts', request()->except('product_types')) }}"
                                   class="btn btn-sm btn-light">Clear</a>
                                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if(request('brands'))
                @php
                    $selectedBrands = request('brands');
                    $displayBrands  = implode(', ', $selectedBrands);
                @endphp
                <div class="d-inline-block position-relative">
                    <span class="badge bg-primary"
                          data-bs-toggle="dropdown"
                          style="cursor: pointer;">
                        Brands: {{ $displayBrands }}
                        <i class="fas fa-caret-down ms-1"></i>
                    </span>
                    <div class="dropdown-menu p-3">
                        <form method="GET" action="{{ route('customer.shopify.fetchSelectedProducts') }}">
                            @foreach(request()->except(['brands','page']) as $param => $value)
                                @if(is_array($value))
                                    @foreach($value as $k => $v)
                                        <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label class="form-label"><strong>Brands</strong></label>
                            <div>
                                @foreach($allBrands as $brand)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="brands[]"
                                               value="{{ $brand }}"
                                               id="active_brand_{{ $loop->index }}"
                                               @if(in_array($brand, $selectedBrands)) checked @endif>
                                        <label class="form-check-label" for="active_brand_{{ $loop->index }}">
                                            {{ $brand }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <a href="{{ route('customer.shopify.fetchSelectedProducts', request()->except('brands')) }}"
                                   class="btn btn-sm btn-light">Clear</a>
                                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if(request('tags'))
                @php
                    $selectedTagArray = request('tags');
                    $displayTags      = implode(', ', $selectedTagArray);
                @endphp
                <div class="d-inline-block position-relative">
                    <span class="badge bg-primary"
                          data-bs-toggle="dropdown"
                          style="cursor: pointer;">
                        Tags: {{ $displayTags }}
                        <i class="fas fa-caret-down ms-1"></i>
                    </span>
                    <div class="dropdown-menu p-3">
                        <form method="GET" action="{{ route('customer.shopify.fetchSelectedProducts') }}">
                            @foreach(request()->except(['tags','page']) as $param => $value)
                                @if(is_array($value))
                                    @foreach($value as $k => $v)
                                        <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label class="form-label"><strong>Tags</strong></label>
                            <div class="@if(count($allTags) > 5) scrollable-options @endif">
                                @foreach($allTags as $tag)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="tags[]"
                                               value="{{ $tag }}"
                                               id="active_tag_{{ $loop->index }}"
                                               @if(in_array($tag, $selectedTagArray)) checked @endif>
                                        <label class="form-check-label" for="active_tag_{{ $loop->index }}">
                                            {{ $tag }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <a href="{{ route('customer.shopify.fetchSelectedProducts', request()->except('tags')) }}"
                                   class="btn btn-sm btn-light">Clear</a>
                                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if(request('stock_min') || request('stock_max'))
                <div class="d-inline-block position-relative">
                    <span class="badge bg-primary"
                          data-bs-toggle="dropdown"
                          style="cursor: pointer;">
                        Stock:
                        @if(request('stock_min'))
                            Min {{ request('stock_min') }}
                        @endif
                        @if(request('stock_max'))
                            - Max {{ request('stock_max') }}
                        @endif
                        <i class="fas fa-caret-down ms-1"></i>
                    </span>
                    <div class="dropdown-menu p-3">
                        <form method="GET" action="{{ route('customer.shopify.fetchSelectedProducts') }}">
                            @foreach(request()->except(['stock_min','stock_max','page']) as $param => $value)
                                @if(is_array($value))
                                    @foreach($value as $k => $v)
                                        <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <div class="mb-2">
                                <label class="form-label"><strong>Min. Stock</strong></label>
                                <input type="number"
                                       name="stock_min"
                                       class="form-control"
                                       value="{{ request('stock_min') }}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label"><strong>Max. Stock</strong></label>
                                <input type="number"
                                       name="stock_max"
                                       class="form-control"
                                       value="{{ request('stock_max') }}">
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('customer.shopify.fetchSelectedProducts', request()->except(['stock_min','stock_max'])) }}"
                                   class="btn btn-sm btn-light">Clear</a>
                                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if(request('filter') && request('filter') !== 'all')
                <div class="d-inline-block position-relative">
                    <span class="badge bg-primary"
                          data-bs-toggle="dropdown"
                          style="cursor: pointer;">
                        {{ ucfirst(request('filter')) }}
                        <i class="fas fa-caret-down ms-1"></i>
                    </span>
                    <div class="dropdown-menu p-3">
                        <form method="GET" action="{{ route('customer.shopify.fetchSelectedProducts') }}">
                            @foreach(request()->except(['filter','page']) as $param => $value)
                                @if(is_array($value))
                                    @foreach($value as $k => $v)
                                        <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label class="form-label">Filter</label>
                            <select name="filter" class="form-select mb-2">
                                <option value="all" {{ request('filter') === 'all' ? 'selected' : '' }}>All Stocks</option>
                                <option value="low_stock" {{ request('filter') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                <option value="out_of_stock" {{ request('filter') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                            </select>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('customer.shopify.fetchSelectedProducts', request()->except('filter')) }}"
                                   class="btn btn-sm btn-light">Clear</a>
                                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if(request('locations'))
                @php
                    $selectedLocationIds = request('locations');
                    $selectedLocationNames = [];
                    foreach($selectedLocationIds as $locId) {
                        $selectedLocationNames[] = $allLocations[$locId] ?? $locId;
                    }
                    $displayLocationNames = implode(', ', $selectedLocationNames);
                @endphp
                <div class="d-inline-block position-relative">
                    <span class="badge bg-primary"
                          data-bs-toggle="dropdown"
                          style="cursor: pointer;">
                        Locations: {{ $displayLocationNames }}
                        <i class="fas fa-caret-down ms-1"></i>
                    </span>
                    <div class="dropdown-menu p-3">
                        <form method="GET" action="{{ route('customer.shopify.fetchSelectedProducts') }}">
                            @foreach(request()->except(['locations','page']) as $param => $value)
                                @if(is_array($value))
                                    @foreach($value as $k => $v)
                                        <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label class="form-label"><strong>Locations</strong></label>
                            <div>
                                @foreach($allLocations as $locId => $locName)
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="locations[]"
                                               value="{{ $locId }}"
                                               id="active_location_{{ $locId }}"
                                               @if(in_array($locId, $selectedLocationIds)) checked @endif>
                                        <label class="form-check-label" for="active_location_{{ $locId }}">
                                            {{ $locName }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <a href="{{ route('customer.shopify.fetchSelectedProducts', request()->except('locations')) }}"
                                   class="btn btn-sm btn-light">Clear</a>
                                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
        <a href="{{ route('customer.shopify.fetchSelectedProducts') }}">Clear Filters</a>
    </div>
    @endif
    <!-- END BOX #2 -->

    <!-- TABLE + DATA -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fas fa-box-open me-2"></i>
            <h5 class="mb-0">Master Shop Selected Products</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="dropdown col-md" data-bs-auto-close="outside" style="max-width: 120px">
                    <button class="btn btn-outline-primary dropdown-toggle"
                            type="button"
                            id="advancedFilterBtn"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        <i class="fa fa-filter"></i> &nbsp;Filters
                    </button>
                    <div class="dropdown-menu"
                         aria-labelledby="advancedFilterBtn"
                         onclick="event.stopPropagation()">
                        <!-- Existing filter form -->
                        <form method="GET" id="filterForm" action="{{ route('customer.shopify.fetchSelectedProducts') }}">
                            <div class="accordion" id="filterAccordion">
                                <!-- VARIANT ACCORDION ITEM -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="variantHeading">
                                        <button class="accordion-button shadow-none"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#variantCollapse"
                                                aria-expanded="true"
                                                aria-controls="variantCollapse">
                                            <i class="fa fa-tag"></i>&nbsp;Variant
                                        </button>
                                    </h2>
                                    <div id="variantCollapse"
                                         class="accordion-collapse collapse show"
                                         aria-labelledby="variantHeading"
                                         data-bs-parent="#filterAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Variant Name</label>
                                                <input type="text"
                                                       name="variant_name"
                                                       class="form-control"
                                                       placeholder="Variant Name"
                                                       value="{{ request('variant_name') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Variant SKU code</label>
                                                <input type="text"
                                                       name="variant_sku"
                                                       class="form-control"
                                                       placeholder="Variant SKU code"
                                                       value="{{ request('variant_sku') }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Barcode</label>
                                                <input type="text"
                                                       name="barcode"
                                                       class="form-control"
                                                       placeholder="Barcode"
                                                       value="{{ request('barcode') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- STOCK QTY ACCORDION ITEM -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="stockQtyHeading">
                                        <button class="accordion-button shadow-none collapsed"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#stockQtyCollapse"
                                                aria-expanded="false"
                                                aria-controls="stockQtyCollapse">
                                            <i class="fa fa-adjust"></i>&nbsp;Stock qty
                                        </button>
                                    </h2>
                                    <div id="stockQtyCollapse"
                                         class="accordion-collapse collapse"
                                         aria-labelledby="stockQtyHeading"
                                         data-bs-parent="#filterAccordion">
                                        <div class="accordion-body">
                                            <div class="row g-2">
                                                <div class="col">
                                                    <label class="form-label fw-bold">Min.</label>
                                                    <input type="number"
                                                           name="stock_min"
                                                           class="form-control"
                                                           placeholder="0"
                                                           value="{{ request('stock_min') }}">
                                                </div>
                                                <div class="col">
                                                    <label class="form-label fw-bold">Max.</label>
                                                    <input type="number"
                                                           name="stock_max"
                                                           class="form-control"
                                                           placeholder="600"
                                                           value="{{ request('stock_max') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- PRODUCT TYPE ACCORDION ITEM (Multi-Checkbox) -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="productTypeHeading">
                                        <button class="accordion-button shadow-none collapsed"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#productTypeCollapse"
                                                aria-expanded="false"
                                                aria-controls="productTypeCollapse">
                                            <i class="fa fa-box"></i>&nbsp;Product Type
                                        </button>
                                    </h2>
                                    <div id="productTypeCollapse"
                                         class="accordion-collapse collapse"
                                         aria-labelledby="productTypeHeading"
                                         data-bs-parent="#filterAccordion">
                                        <!-- Add scrollable if more than 5 product types -->
                                        <div class="accordion-body @if(count($allProductTypes) > 5) scrollable-options @endif">
                                            @if(count($allProductTypes) > 0)
                                                @foreach($allProductTypes as $type)
                                                    <div class="form-check">
                                                        <input class="form-check-input"
                                                               type="checkbox"
                                                               name="product_types[]"
                                                               value="{{ $type }}"
                                                               id="product_type_{{ $loop->index }}"
                                                               @if(is_array(request('product_types')) && in_array($type, request('product_types'))) checked @endif>
                                                        <label class="form-check-label" for="product_type_{{ $loop->index }}">
                                                            {{ $type }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="small text-muted">No product types found.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!-- LOCATIONS ACCORDION ITEM -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="locationsHeading">
                                        <button class="accordion-button shadow-none collapsed"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#locationsCollapse"
                                                aria-expanded="false"
                                                aria-controls="locationsCollapse">
                                            <i class="fa fa-location-dot"></i>&nbsp;Locations
                                        </button>
                                    </h2>
                                    <div id="locationsCollapse"
                                         class="accordion-collapse collapse"
                                         aria-labelledby="locationsHeading"
                                         data-bs-parent="#filterAccordion">
                                        <!-- Add scrollable if more than 5 locations -->
                                        <div class="accordion-body @if(count($allLocations) > 5) scrollable-options @endif">
                                            @if(count($allLocations) > 0)
                                                @foreach($allLocations as $locId => $locName)
                                                    <div class="form-check">
                                                        <input class="form-check-input"
                                                               type="checkbox"
                                                               name="locations[]"
                                                               value="{{ $locId }}"
                                                               id="location_{{ $locId }}"
                                                               @if(is_array(request('locations')) && in_array($locId, request('locations'))) checked @endif>
                                                        <label class="form-check-label" for="location_{{ $locId }}">
                                                            {{ $locName }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="small text-muted">No locations found.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!-- BRANDS ACCORDION ITEM (Multi-Checkbox) -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="brandsHeading">
                                        <button class="accordion-button shadow-none collapsed"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#brandsCollapse"
                                                aria-expanded="false"
                                                aria-controls="brandsCollapse">
                                            <i class="fa fa-copyright"></i>&nbsp;Brands
                                        </button>
                                    </h2>
                                    <div id="brandsCollapse"
                                         class="accordion-collapse collapse"
                                         aria-labelledby="brandsHeading"
                                         data-bs-parent="#filterAccordion">
                                        <!-- Add scrollable if more than 5 brands -->
                                        <div class="accordion-body @if(count($allBrands) > 5) scrollable-options @endif">
                                            @if(count($allBrands) > 0)
                                                @foreach($allBrands as $brand)
                                                    <div class="form-check">
                                                        <input class="form-check-input"
                                                               type="checkbox"
                                                               name="brands[]"
                                                               value="{{ $brand }}"
                                                               id="brand_{{ $loop->index }}"
                                                               @if(is_array(request('brands')) && in_array($brand, request('brands'))) checked @endif>
                                                        <label class="form-check-label" for="brand_{{ $loop->index }}">
                                                            {{ $brand }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="small text-muted">No brands found.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!-- TAGS ACCORDION ITEM (Multi-Checkbox) -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="tagsHeading">
                                        <button class="accordion-button shadow-none collapsed"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#tagsCollapse"
                                                aria-expanded="false"
                                                aria-controls="tagsCollapse">
                                            <i class="fa fa-hashtag"></i>&nbsp;Tags
                                        </button>
                                    </h2>
                                    <div id="tagsCollapse"
                                         class="accordion-collapse collapse"
                                         aria-labelledby="tagsHeading"
                                         data-bs-parent="#filterAccordion">
                                        <!-- Add scrollable if more than 5 tags -->
                                        <div class="accordion-body @if(count($allTags) > 5) scrollable-options @endif">
                                            @if(count($allTags) > 0)
                                                @foreach($allTags as $tag)
                                                    <div class="form-check">
                                                        <input class="form-check-input"
                                                               type="checkbox"
                                                               name="tags[]"
                                                               value="{{ $tag }}"
                                                               id="tag_{{ $loop->index }}"
                                                               @if(is_array(request('tags')) && in_array($tag, request('tags'))) checked @endif>
                                                        <label class="form-check-label" for="tag_{{ $loop->index }}">
                                                            {{ $tag }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="small text-muted">No tags found.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary btn-sm" style="background-color:#4485c9">
                                    Apply Filters
                                </button>
                                <a href="{{ route('customer.shopify.fetchSelectedProducts') }}" class="btn btn-secondary btn-sm">
                                    Clear Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md">
                    <form method="GET"
                          action="{{ route('customer.shopify.fetchSelectedProducts') }}"
                          class="d-flex align-items-center flex-grow-1 me-3">
                        <!-- Preserve existing filters -->
                        @foreach(request()->except(['general_search','page']) as $param => $value)
                            @if(is_array($value))
                                @foreach($value as $v)
                                    <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <div class="input-group w-100">
                            <input type="text"
                                   name="general_search" class="form-control" placeholder="Search products..."
                                   value="{{ request('general_search') }}">
                            <button type="submit" class="input-group-text bg-primary text-white">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-md-1">
                    <form method="GET" action="{{ route('customer.shopify.fetchSelectedProducts') }}" class="d-flex align-items-center">
                        @foreach(request()->except(['per_page','page']) as $param => $value)
                            @if(is_array($value))
                                @foreach($value as $v)
                                    <input type="hidden" name="{{ $param }}[]" value="{{ $v }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach

{{--                        <label for="perPageSelect" class="form-label fw-bold me-2 mb-0">--}}
{{--                            Show entries per page:--}}
{{--                        </label>--}}
                        <select name="per_page"
                                id="perPageSelect"
                                class="form-select"
                                style="width: auto;"
                                onchange="this.form.submit()">
                            <option value="10"  {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20"  {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                            <option value="50"  {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </form>
                </div>
            </div>

            <form id="bulkDeleteForm"
                  action="{{ route('customer.shopify.selectedProducts.deleteMultiple') }}"
                  method="POST"
                  onsubmit="return confirm('Are you sure you want to delete the selected products?');">
                @csrf
                @method('DELETE')

                <div class="mb-3">
                    <button id="deleteSelectedBtn"
                            type="submit"
                            class="btn btn-danger"
                            style="display: none;">
                        <i class="fas fa-trash-alt"></i> Delete Selected
                    </button>
                </div>

                <table id="inventoryTable" class="table table-striped table-hover align-middle table-sm small">
                    <thead class="table-dark">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Sr.</th>
                            <th>Store</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Available</th>
                            <th>Added On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($selectedProducts->count())
                            @php $i = ($selectedProducts->currentPage() - 1) * $selectedProducts->perPage(); @endphp
                            @foreach($selectedProducts as $sp)
                                @php
                                    $i++;
                                    $rowId          = $sp->id;
                                    $locationsArray = is_string($sp->location_ids)
                                        ? json_decode($sp->location_ids, true) ?? []
                                        : ($sp->location_ids ?? []);
                                    $inventoryData  = $sp->variant_inventory;
                                    $parsedInventory = is_string($inventoryData)
                                        ? json_decode($inventoryData, true)
                                        : $inventoryData;
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-checkbox" name="selected_products[]" value="{{ $sp->id }}">
                                    </td>
                                    <td><strong>{{ $i }}</strong></td>
                                    <td>
                                        @if($sp->shopifyStore)
                                            {{ $sp->shopifyStore->store_name }}
                                        @else
                                            <span class="text-muted">No Store</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($sp->variant_image)
                                            <img src="{{ $sp->variant_image }}"
                                                 class="img-fluid img-thumbnail"
                                                 alt="Product Image"
                                                 style="max-width: 75px; max-height: 75px">
                                        @else
                                            <img src="https://dummyimage.com/75x75/cccccc/000000&text=No+Image"
                                                 class="img-thumbnail" alt="No Image">
                                        @endif
                                    </td>
                                    <td>
                                        {{ $sp->product_title ?? 'N/A' }}<br>
                                        <small>{{ $sp->variant_name ?? '' }}</small>
                                    </td>
                                    <td>{{ $sp->variant_sku ?? 'N/A' }}</td>
                                    <td>{{ $sp->currency_symbol.' '.$sp->variant_price ?? 'N/A' }}</td>
                                    <td>
                                        @if(is_array($parsedInventory))
                                            <div class="d-flex flex-column">
                                                @foreach($parsedInventory as $locId => $invCount)
                                                    @php
                                                        $locName = 'Unknown';
                                                        foreach ($locationsArray as $location) {
                                                            if (isset($location['id']) && $location['id'] == $locId) {
                                                                $locName = $location['city'] ?? $location['name'] ?? 'Unknown';
                                                                break;
                                                            }
                                                        }
                                                        $textClass = ($invCount < 20) ? 'text-danger fw-bold' : 'text-success fw-bold';
                                                    @endphp
                                                    <div class="d-flex justify-content-between align-items-center p-1 border-bottom"
                                                         style="cursor: pointer;"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#editLocationModal_{{ $rowId }}_{{ $locId }}">
                                                        <span class="fw-normal">{{ $locName }}</span>
                                                        <span class="{{ $textClass }}">{{ $invCount }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            {{ $sp->variant_inventory ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>{{ $sp->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>

                <!-- Pagination info (left) and links (right) -->
                @if($selectedProducts->total() > 0)
                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div class="text-muted small">
                            Showing {{ $selectedProducts->firstItem() }}
                            to {{ $selectedProducts->lastItem() }}
                            of {{ $selectedProducts->total() }} entries
                        </div>
                        <div>
                            {{ $selectedProducts->links() }}
                        </div>
                    </div>
                @endif

            </form>
        </div>
    </div>

    <!-- EDIT INVENTORY MODALS -->
    @foreach($selectedProducts as $sp)
        @php
            $rowId          = $sp->id;
            $locationsArray = is_string($sp->location_ids)
                ? json_decode($sp->location_ids, true) ?? []
                : ($sp->location_ids ?? []);
            $inventoryData   = $sp->variant_inventory;
            $parsedInventory = is_string($inventoryData)
                ? json_decode($inventoryData, true)
                : $inventoryData;
        @endphp
        @if(is_array($parsedInventory))
            @foreach($parsedInventory as $locId => $invCount)
                @php
                    $locName = 'Unknown';
                    foreach ($locationsArray as $location) {
                        if (isset($location['id']) && $location['id'] == $locId) {
                            $locName = $location['city'] ?? $location['name'] ?? 'Unknown';
                            break;
                        }
                    }
                @endphp
                <div class="modal fade"
                     id="editLocationModal_{{ $rowId }}_{{ $locId }}"
                     tabindex="-1"
                     aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('customer.shopify.selectedProducts.updateInventory') }}">
                                @csrf
                                <div class="modal-body">
                                    <input type="hidden" name="selected_product_id" value="{{ $sp->id }}">
                                    <input type="hidden" name="variant_id" value="{{ $sp->variant_id }}">
                                    <input type="hidden" name="shopify_store_id" value="{{ $sp->shopify_store_id }}">
                                    <input type="hidden" name="location_id" value="{{ $locId }}">
                                    <input type="hidden" name="location_ids" value='@json($locationsArray)'>

                                    <div class="input-group mb-2">
                                        <span class="input-group-text" id="base1">
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="radio"
                                                       name="inventory_operation"
                                                       value="set"
                                                       checked>
                                                <label class="form-check-label">Set</label>
                                            </div>
                                        </span>
                                        <span class="input-group-text" id="base2">
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="radio"
                                                       name="inventory_operation"
                                                       value="adjust">
                                                <label class="form-check-label">Adjust</label>
                                            </div>
                                        </span>
                                        <input type="number"
                                               class="form-control"
                                               name="inventory_quantity"
                                               placeholder="10">
                                    </div>

                                    <div class="text-center mt-2">
                                        <span class="fw-bold">{{ $invCount }}</span> On Hand <br>
                                        {{ $sp->product_title }} / {{ $sp->variant_sku }}
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-center">
                                    <button type="submit" class="btn btn-success">Save</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endforeach
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

<!-- Keep jQuery if you need it for checkbox toggling, etc. -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // No DataTables init - we removed it.

    // Bulk Delete: "selectAll" / "row-checkbox" logic
    var selectAll = $('#selectAll');
    var deleteSelectedBtn = $('#deleteSelectedBtn');

    function toggleDeleteButton() {
        // Show "Delete Selected" if any row-checkbox is checked
        var anyChecked = $('.row-checkbox:checked').length > 0;
        deleteSelectedBtn.toggle(anyChecked);
    }

    selectAll.on('change', function() {
        var checked = $(this).prop('checked');
        $('.row-checkbox').prop('checked', checked);
        toggleDeleteButton();
    });

    $('.row-checkbox').on('change', function() {
        if (!$(this).prop('checked')) {
            selectAll.prop('checked', false);
        }
        toggleDeleteButton();
    });

    // On filter form submission, disable empty inputs so they don't clutter the URL
    $('#filterForm').on('submit', function() {
        $(this).find('input, select').filter(function() {
            return !this.value;
        }).prop('disabled', true);
    });
});
</script>
@endsection
