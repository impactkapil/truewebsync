@extends('layouts.customer')

@section('customer-content')
<div class="container-fluid">
    <h2>{{ $storeName }}'s Shopify Products</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(isset($totalVariants))
        <div id="selectedVariantsAlert" class="alert alert-success" style="display: none;"></div>
    @endif

    @isset($storeId)
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div class="col-md-3 col-lg-2 col-xl-2">
                <form id="multiVariantAddForm" 
                      action="{{ route('customer.shopify.products.add') }}"
                      method="POST"
                      style="display: none;">
                    @csrf
                    <input type="hidden" name="store_id" value="{{ $storeId }}">
                    <input type="hidden" name="variant_image" value="">
                    <input type="hidden" name="currency_symbol" value="">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-plus-circle" aria-hidden="true"></i>
                        Add Selected Variants
                    </button>
                </form>
            </div>
            
            <form id="searchForm"
                  method="GET"
                  action="{{ route('customer.shopify.productsbyid', ['store' => $storeId]) }}"
                  class="col-md-9 col-lg-12 col-xl-10 row align-items-end">
                <div class="col-md-8">
                    <label class="form-label" for="searchInput">Search Products:</label>
                    <input id="searchInput"
                           type="text"
                           name="search"
                           class="form-control"
                           placeholder="Type to search..."
                           value="{{ request('search', $search ?? '') }}">
                </div>
                <div class="col-md-2" style="margin-top: 29px;">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="limitSelect">Products per page:</label>
                    <select name="limit" class="form-select" id="limitSelect">
                        <option value="10"  {{ (request('limit', $limit) == 10)  ? 'selected' : '' }}>10</option>
                        <option value="20"  {{ (request('limit', $limit) == 20)  ? 'selected' : '' }}>20</option>
                        <option value="50"  {{ (request('limit', $limit) == 50)  ? 'selected' : '' }}>50</option>
                        <option value="100" {{ (request('limit', $limit) == 100) ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </form>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width:40px; text-align:center;">
                        <input type="checkbox" id="selectAll" style="transform: scale(1.2);" />
                    </th>
                    <th>Product Image</th>
                    <th>Product + Variant</th>
                    <th>Variant SKU</th>
                    <th>Price</th>
                    <th>Location-wise Inventory</th>
                    <th>Vendor</th>
                    <th>Tags</th>
                    <th>Store Locations</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($products->count() > 0)
                    @foreach($products as $product)
                        @php
                            $numericProductId   = $product->product_id;
                            $productTitle       = $product->product_title ?? 'Untitled';
                            // For display, vendor and tags are taken from the model fields.
                            $vendor             = $product->brand;
                            $tags               = $product->tags;
                            // Capture the additional fields for product_type and barcode.
                            $productType        = $product->product_type ?? '';
                            $barcode            = $product->barcode ?? '';
                            $imageSrc           = $product->variant_image;
                            $variantName        = $product->variant_name ?? '';
                            $numericVarId       = $product->variant_id;
                            $sku                = $product->variant_sku ?? 'N/A';
                            $price              = $product->variant_price ?? '0.00';
                            $currency           = $product->currency_symbol ?? '$';
                            $invByLocation      = is_array($product->variant_inventory) ? $product->variant_inventory : (json_decode($product->variant_inventory, true) ?? []);
                            $locations          = is_array($product->location_ids) ? $product->location_ids : (json_decode($product->location_ids, true) ?? []);
                            $isAlreadySelected  = in_array($numericVarId, $alreadySelectedVariantIds ?? []);
                            $hasNoLocationData  = empty($invByLocation);
                        @endphp

                        <tr>
                            <td class="text-center align-middle">
                                @if($isAlreadySelected)
                                    <small class="text-muted">✔</small>
                                @elseif($hasNoLocationData)
                                    <input type="checkbox" disabled style="transform: scale(1.2);" />
                                    <i class="fa fa-info-circle text-danger" title="You can't add this variant because No location data found."></i>
                                @else
                                    <!-- Multi-variant add hidden inputs -->
                                    <input type="checkbox"
                                           form="multiVariantAddForm"
                                           class="multi-checkbox"
                                           name="variants[{{ $numericVarId }}][checked]"
                                           value="1"
                                           style="transform: scale(1.2);" />
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][product_id]"
                                           value="{{ $numericProductId }}">
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][product_title]"
                                           value="{{ $productTitle }}">
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][variant_name]"
                                           value="{{ $variantName }}">
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][variant_sku]"
                                           value="{{ $sku }}">
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][variant_price]"
                                           value="{{ $price }}">
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][variant_inventory]"
                                           value="{{ json_encode($invByLocation) }}">
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][location_ids]"
                                           value="{{ json_encode($locations) }}">
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][variant_image]"
                                           value="{{ $imageSrc }}">
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][currency_symbol]"
                                           value="{{ $currency }}">
                                    <!-- Hidden fields for brand and tags -->
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][brand]"
                                           value="{{ $vendor }}">
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][tags]"
                                           value="{{ $tags }}">
                                    <!-- New hidden fields for product_type and barcode -->
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][product_type]"
                                           value="{{ $productType }}">
                                    <input type="hidden" form="multiVariantAddForm"
                                           name="variants[{{ $numericVarId }}][barcode]"
                                           value="{{ $barcode }}">
                                @endif
                            </td>
                            <td>
                                @if($imageSrc)
                                    <img src="{{ $imageSrc }}" alt="{{ $productTitle }}" style="max-height:100px;" class="img-fluid">
                                @else
                                    <span>No Image</span>
                                @endif
                            </td>
                            <td>
                                {{ $productTitle }}
                                @if($variantName && $variantName !== 'Default Title')
                                    <br><small class="text-muted">{{ $variantName }}</small>
                                @endif
                            </td>
                            <td>{{ $sku }}</td>
                            <td>{{ $currency.' '.$price }}</td>
                            <td>
                                @if(!empty($invByLocation))
                                    <table class="table table-sm table-bordered mb-0">
                                        <tbody>
                                            @foreach($invByLocation as $locId => $avail)
                                                @php
                                                    $locCity = 'Unknown';
                                                    foreach ($locations as $l) {
                                                        if(isset($l['id']) && $l['id'] == $locId) {
                                                            $locCity = $l['city'] ?? $l['name'] ?? 'Unknown';
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    <td @if($avail < 20) style="color:red;" @endif>{{ $locCity }}</td>
                                                    <td @if($avail < 20) style="color:red;" @endif>{{ $avail }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <small>No location data</small>
                                @endif
                            </td>
                            <td>{{ $vendor }}</td>
                            <td>{{ $tags }}</td>
                            <td>
                                @if(!empty($locations))
                                    @foreach($locations as $loc)
                                        <p>{{ $loc['city'] ?? 'Unknown City' }}</p>
                                    @endforeach
                                @else
                                    <p>No Locations</p>
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($isAlreadySelected)
                                    <button type="button" class="btn btn-secondary btn-sm" disabled>Already Added</button>
                                @elseif($hasNoLocationData)
                                    <i class="fa fa-info-circle text-danger" title="You can't add this variant because No location data found."></i>
                                @else
                                    @isset($storeId)
                                        <form action="{{ route('customer.shopify.products.add') }}"
                                              method="POST"
                                              style="display:inline-block; margin-bottom:6px;">
                                            @csrf
                                            <input type="hidden" name="single_variant_add" value="1">
                                            <input type="hidden" name="store_id" value="{{ $storeId }}">
                                            <input type="hidden" name="product_id" value="{{ $numericProductId }}">
                                            <input type="hidden" name="product_title" value="{{ $productTitle }}">
                                            <input type="hidden" name="variant_name" value="{{ $variantName }}">
                                            <input type="hidden" name="variant_id" value="{{ $numericVarId }}">
                                            <input type="hidden" name="variant_sku" value="{{ $sku }}">
                                            <input type="hidden" name="variant_price" value="{{ $price }}">
                                            <input type="hidden" name="variant_inventory" value="{{ json_encode($invByLocation) }}">
                                            <input type="hidden" name="location_ids" value="{{ json_encode($locations) }}">
                                            <input type="hidden" name="variant_image" value="{{ $imageSrc }}">
                                            <input type="hidden" name="currency_symbol" value="{{ $currency }}">
                                            <!-- Hidden fields for brand and tags -->
                                            <input type="hidden" name="brand" value="{{ $vendor }}">
                                            <input type="hidden" name="tags" value="{{ $tags }}">
                                            <!-- New hidden fields for product_type and barcode -->
                                            <input type="hidden" name="product_type" value="{{ $productType }}">
                                            <input type="hidden" name="barcode" value="{{ $barcode }}">
                                            <button type="submit" class="btn btn-success btn-sm">Add Variant</button>
                                        </form>
                                    @else
                                        <small class="text-muted">No store ID</small>
                                    @endisset
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="alert alert-warning mb-0">No products found or failed to fetch products!</div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        @if(method_exists($products, 'links'))
            <div class="mt-3">{{ $products->links() }}</div>
        @endif
    @else
        <div class="alert alert-danger">No Product Found.</div>
    @endisset
</div>
@endsection

@section('scripts')
<script>
    const limitSelect = document.getElementById('limitSelect');
    const searchForm = document.getElementById('searchForm');
    const selectAllCheckbox = document.getElementById('selectAll');
    const multiCheckboxes = document.querySelectorAll('.multi-checkbox');
    const multiVariantAddForm = document.getElementById('multiVariantAddForm');
    const selectedVariantsAlert = document.getElementById('selectedVariantsAlert');
    const totalVariants = {{ $totalVariants }};
    
    if (limitSelect) {
        limitSelect.addEventListener('change', function() {
            searchForm.submit();
        });
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            multiCheckboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = selectAllCheckbox.checked;
                }
            });
            toggleMultiAddButton();
        });
    }

    function toggleMultiAddButton() {
        const checkedBoxes = Array.from(multiCheckboxes).filter(cb => cb.checked && !cb.disabled);
        const anyChecked = (checkedBoxes.length > 0);
        multiVariantAddForm.style.display = anyChecked ? 'inline-block' : 'none';
        updateSelectedVariantsAlert(checkedBoxes.length);
    }

    function updateSelectedVariantsAlert(count) {
        if (!selectedVariantsAlert) return;
        if (count > 0) {
            selectedVariantsAlert.style.display = 'block';
            selectedVariantsAlert.textContent = count + ' variants are selected out of ' + totalVariants;
        } else {
            selectedVariantsAlert.style.display = 'none';
            selectedVariantsAlert.textContent = '';
        }
    }

    multiCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!cb.checked) {
                selectAllCheckbox.checked = false;
            }
            toggleMultiAddButton();
        });
    });
</script>
@endsection
