@extends('layouts.customer')

@section('customer-content')
<div class="container">
    <h2>Add Your Shopify Store</h2>

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
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fas fa-store me-2"></i>
            <h5 class="mb-0">Store Details</h5>
        </div>
        <div class="card-body">
            <form id="shopifyStoreForm" method="POST" action="{{ route('customer.shopify.store') }}">
                @csrf

                <!-- Store Name Field -->
                <div class="mb-3">
                    <label for="store_name" class="form-label fw-bold">Store Name</label>
                    <input type="text"
                           class="form-control @error('store_name') is-invalid @enderror"
                           name="store_name"
                           value="{{ old('store_name') }}"
                           placeholder="Enter store name">
                    @error('store_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Shopify Domain Field -->
                <div class="mb-3">
                    <label for="shopify_domain" class="form-label fw-bold">Shopify Domain</label>
                    <small class="text-muted d-block mb-1">
                        Use <strong>yourstore.myshopify.com</strong> (no http/https)
                    </small>
                    <input type="text"
                           class="form-control @error('shopify_domain') is-invalid @enderror"
                           name="shopify_domain"
                           value="{{ old('shopify_domain') }}"
                           placeholder="yourstore.myshopify.com">
                    @error('shopify_domain')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror

                    <div id="domainError" class="text-danger mt-1" style="display: none;"></div>
                </div>

                <!-- Access Token Field -->
                <div class="mb-3">
                    <label for="access_token" class="form-label fw-bold">Access Token</label>
                    <a href="https://kanteneo.com/blog/how-to-create-a-custom-app-access-token-in-shopify/"
                       target="_blank">How to generate?</a>
                    <input type="text"
                           class="form-control @error('access_token') is-invalid @enderror"
                           name="access_token"
                           value="{{ old('access_token') }}"
                           placeholder="Paste your Shopify access token here">
                    @error('access_token')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Webhooks Secret Key Field -->
                <div class="mb-3">
                    <label for="webhooks_secret_key" class="form-label fw-bold">Webhooks Secret Key</label>
                    <input type="text"
                           class="form-control @error('webhooks_secret_key') is-invalid @enderror"
                           name="webhooks_secret_key"
                           value="{{ old('webhooks_secret_key') }}"
                           placeholder="Enter your webhooks secret key">
                    @error('webhooks_secret_key')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Store
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('shopifyStoreForm');
    form.addEventListener('submit', function(e) {
        const domainInput = form.querySelector('input[name="shopify_domain"]');
        const domainValue = (domainInput.value || '').trim().toLowerCase();
        const domainError = document.getElementById('domainError');

        domainError.style.display = 'none';
        domainError.textContent = '';
        domainInput.classList.remove('is-invalid');

        if (domainValue.includes('http://') || domainValue.includes('https://')) {
            e.preventDefault();
            domainError.textContent = "Please remove 'http://' or 'https://' from your Shopify domain.";
            domainError.style.display = 'block';
            domainInput.classList.add('is-invalid');
            domainInput.focus();
        }
    });
});
</script>
@endsection
