@extends('layouts.customer')

@section('customer-content')
<style>
    /* Example styling; adjust as needed */
    .pagination {
        margin: 0;
    }
</style>

<div class="container">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Linked Shopify Stores</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('customer.shopify.create') }}" class="btn btn-primary">
                <i class="fas fa-store"></i> Add Store
            </a>
        </div>
    </div>

    @if($stores->count())
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <i class="fas fa-store me-2"></i>
                <h5 class="mb-0">All Stores</h5>
            </div>
            <div class="card-body">

                <!-- Search & Per-Page Form -->
                <form method="GET" action="{{ route('customer.shopify.stores') }}" class="d-flex align-items-center w-100 mb-3">
                        <!-- Search on left -->
                            <label for="searchStores" class="form-label fw-bold me-2 mb-0">
                                Search:
                            </label>
                            <div class="input-group w-100">
                            <input type="text"
                                   name="search"
                                   id="searchStores"
                                   class="form-control form-control-sm"
                                   placeholder="Search..."
                                   value="{{ request('search') }}">
                            <button type="submit" class="input-group-text bg-primary text-white me-3">
                                Search
                            </button>
</div>

                        <!-- Per-page dropdown on right -->
                            <label for="perPageSelect" class="form-label fw-bold me-2 mb-0" style="white-space: nowrap">
                                Show entries per page:
                            </label>
                            <select name="per_page"
                                    id="perPageSelect"
                                    class="form-select form-select-sm"
                                    style="width: auto;"
                                    onchange="this.form.submit()">
                                <option value="10"  {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="20"  {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                <option value="50"  {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                </form>
                <!-- /Search & Per-Page Form -->

                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Store Domain</th>
                            <th>Store Name</th>
                            <th>Import Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stores as $store)
                            <tr>
                                <td>
                                    <strong>{{ $store->shopify_domain }}</strong>
                                    @if($store->is_master)
                                        <span class="badge bg-success ms-2">Master</span>
                                    @endif
                                </td>
                                <td><strong>{{ $store->store_name }}</strong></td>
                                <!-- Give this cell a unique ID so we can update it dynamically -->
                                <td>
                                    <strong id="importProgress-{{ $store->id }}">
                                        {{ $store->imported_products }} / {{ $store->total_products }}
                                    </strong>
                                </td>
                                <td>
                                    <strong>{{ $store->status ? 'Active' : 'Inactive' }}</strong>
                                </td>
                                <td>
                                    <a href="{{ route('customer.shopify.productsbyid', $store->id) }}"
                                       class="btn btn-info btn-sm {{ !$store->total_products ? 'disabled' : '' }}"
                                       id="viewProductsBtn-{{ $store->id }}">
                                        <i class="fa fa-eye"></i> View Products
                                    </a>

                                    @if(!$store->is_master)
                                        <form action="{{ route('customer.shopify.stores.destroy', $store->id) }}"
                                              method="POST"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete this store and all related products?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" id="deleteShopBtn-{{ $store->id }}">
                                                <i class="fa fa-trash"></i> Delete Shop
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-danger btn-sm" disabled>
                                            <i class="fa fa-trash"></i> Delete Shop
                                        </button>
                                    @endif

                                    <button type="button"
                                            class="btn btn-warning btn-sm"
                                            onclick="window.location='{{ route('customer.shopify.stores.edit', $store->id) }}'">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                    <button type="button"
                                            class="btn btn-primary btn-sm"
                                            id="syncBtn-{{ $store->id }}"
                                            onclick="startSync('{{ route('customer.shopify.sync.v2', $store->id) }}', {{ $store->id }})"
                                            {{ !$store->total_products ? 'disabled' : '' }}>
                                        <i class="fa fa-refresh"></i> Sync V2
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination info (left) and links (right) -->
                <div class="d-flex align-items-center justify-content-between mt-3">
                    <div class="text-muted small">
                        @if($stores->total() > 0)
                            Showing {{ $stores->firstItem() }}
                            to {{ $stores->lastItem() }}
                            of {{ $stores->total() }} stores
                        @else
                            Showing 0 to 0 of 0 stores
                        @endif
                    </div>
                    <div>
                        {{ $stores->links() }}
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">No stores found!</div>
    @endif
</div>

<!-- Optional Overlay for Sync progress -->
<div id="syncOverlay"
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            background-color: rgba(0,0,0,0.5); z-index:9999; text-align:center;">
    <div style="position: relative; top:40%; margin:0 auto; width:300px;">
        <h4 class="text-white mb-3">Loading... Please Wait</h4>
        <div class="progress" style="height:25px;">
            <div id="syncProgressBar"
                 class="progress-bar progress-bar-striped progress-bar-animated"
                 role="progressbar"
                 style="width:0%;">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- Remove DataTables references; no longer needed -->
    <!-- <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script> -->

    <!-- Keep your existing sync / polling logic. -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
    // Keep track of store progress in a JS object
    let storeProgressData = {};
    // We'll track which storeIDs still need polling
    let storeIds = [];

    @foreach($stores as $store)
        storeProgressData[{{ $store->id }}] = {
            total:    {{ $store->total_products }},
            imported: {{ $store->imported_products }}
        };
        // If not fully imported, add to storeIds for polling
        @if($store->total_products === 0 || $store->imported_products < $store->total_products)
            storeIds.push({{ $store->id }});
        @endif
    @endforeach

    /**
     * updateProgressBar(storeId)
     * Updates the Import Progress cell and (optionally) re-enables buttons when complete.
     */
    function updateProgressBar(storeId) {
        let storeData = storeProgressData[storeId];
        if (!storeData) return;

        let total    = storeData.total;
        let imported = storeData.imported;

        // Update the Import Progress cell text
        const importProgressEl = document.getElementById('importProgress-' + storeId);
        if (importProgressEl) {
            importProgressEl.textContent = imported + ' / ' + total;
        }

        // If the store is now fully imported, re-enable any disabled buttons
        if (total > 0 && imported >= total) {
            // Remove store from polling
            storeIds = storeIds.filter(id => id !== storeId);

            // Example: re-enable "View Products" if needed
            const viewBtn = document.getElementById('viewProductsBtn-' + storeId);
            if (viewBtn) {
                viewBtn.classList.remove('disabled');
            }

            // Re-enable "Delete Shop" if you disabled it
            const deleteBtn = document.getElementById('deleteShopBtn-' + storeId);
            if (deleteBtn) {
                deleteBtn.disabled = false;
            }

            // Re-enable "Sync V2" if you disabled it
            const syncBtn = document.getElementById('syncBtn-' + storeId);
            if (syncBtn) {
                syncBtn.disabled = false;
            }
        }
    }

    // Poll the server every 5 seconds to update import progress
    setInterval(function() {
        if (storeIds.length === 0) return; // no stores need polling

        storeIds.forEach(function(storeId) {
            fetch("{{ url('/customer/shopify/import/status2') }}/" + storeId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.warn("Import status error:", data.error);
                        return;
                    }
                    storeProgressData[storeId] = {
                        total: parseInt(data.total, 10),
                        imported: parseInt(data.imported, 10)
                    };
                    updateProgressBar(storeId);
                })
                .catch(err => console.error("Error polling status:", err));
        });
    }, 5000);

    /**
     * startSync - Called when user clicks "Sync V2" button
     * This triggers the new job and shows an overlay progress bar
     */
    function startSync(syncUrl, storeId) {
        // Show overlay
        const overlay    = document.getElementById('syncOverlay');
        const progressEl = document.getElementById('syncProgressBar');
        overlay.style.display   = 'block';
        progressEl.style.width  = '0%';
        progressEl.textContent  = '0%';

        // Post to the new route
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(syncUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            console.log("Sync request completed:", data);
        })
        .catch(err => console.error("Sync request error:", err));

        // Poll the same status endpoint for overlay progress
        let overlayPolling = setInterval(() => {
            fetch("{{ url('/customer/shopify/import/status2') }}/" + storeId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.warn("Import status error:", data.error);
                        return;
                    }
                    let total    = parseInt(data.total, 10);
                    let imported = parseInt(data.imported, 10);
                    if (total > 0) {
                        let percent = Math.round((imported / total) * 100);
                        progressEl.style.width = percent + '%';
                        progressEl.textContent = percent + '%';

                        // If done, hide overlay & stop polling
                        if (percent >= 100) {
                            clearInterval(overlayPolling);
                            overlay.style.display = 'none';
                        }
                    }
                    // Also update the table cell in real time
                    storeProgressData[storeId] = { total, imported };
                    updateProgressBar(storeId);
                })
                .catch(err => console.error("Overlay polling error:", err));
        }, 3000);
    }
    </script>
@endsection
