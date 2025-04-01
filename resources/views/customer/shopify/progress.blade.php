@extends('layouts.customer')

@section('customer-content')
<div class="container my-5">
    <h2 class="mb-4">Importing Products for "{{ $store->store_name }}"</h2>

    <div class="alert alert-info" id="progress-info">
        Please wait while we import your products. Do not close this page.
    </div>

    <div class="progress" style="height: 30px;">
      <div class="progress-bar progress-bar-striped progress-bar-animated"
           role="progressbar"
           style="width: 0%"
           aria-valuemin="0"
           aria-valuemax="100"
           id="progressBar">
        0%
      </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let storeId = "{{ $store->id }}";
    let pollingInterval = 3000; // 3 seconds

    function fetchProgress() {
        fetch("{{ url('/shopify/import/status') }}/" + storeId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                let total = parseInt(data.total, 10);
                let imported = parseInt(data.imported, 10);
                let percentage = 0;

                if (total > 0) {
                    percentage = Math.round((imported / total) * 100);
                }

                // Update progress bar
                let progressBar = document.getElementById('progressBar');
                progressBar.style.width = percentage + "%";
                progressBar.textContent = percentage + "%";

                // If done, show success message or redirect
                if (imported >= total && total > 0) {
                    document.getElementById('progress-info').innerText =
                        "All products have been imported! Redirecting...";
                    
                    // Optionally redirect after a short delay
                    setTimeout(() => {
                        // For instance, redirect to the store listing:
                        window.location.href = "{{ route('customer.shopify.stores') }}";
                    }, 2000);
                } else {
                    // Not done yet - keep polling
                    setTimeout(fetchProgress, pollingInterval);
                }
            })
            .catch(err => {
                console.error("Error fetching progress:", err);
                setTimeout(fetchProgress, pollingInterval);
            });
    }

    // Start polling
    fetchProgress();
});
</script>
@endsection
