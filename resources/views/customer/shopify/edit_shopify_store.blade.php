@extends('layouts.customer')

@section('customer-content')
<div class="container">
    <h2>Edit Shopify Store</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fas fa-store me-2"></i>
            <h5 class="mb-0">Store Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('customer.shopify.stores.update', $store->id) }}">
                @csrf
                @method('PUT')

                <!-- Store Name Field -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Store Name</label>
                    <input type="text"
                           class="form-control @error('store_name') is-invalid @enderror"
                           name="store_name"
                           value="{{ old('store_name', $store->store_name) }}"
                           placeholder="Enter store name">
                    @error('store_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Shopify Domain Field -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Shopify Domain</label>
                    <small class="text-muted d-block mb-1">
                        Use <strong>yourstore.myshopify.com</strong> (no http/https)
                    </small>
                    <input type="text"
                           class="form-control @error('shopify_domain') is-invalid @enderror"
                           name="shopify_domain"
                           value="{{ old('shopify_domain', $store->shopify_domain) }}"
                           placeholder="yourstore.myshopify.com">
                    @error('shopify_domain')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Access Token Field -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Access Token</label>
                    <a href="https://kanteneo.com/blog/how-to-create-a-custom-app-access-token-in-shopify/"
                       target="_blank">How to generate?</a>
                    <input type="text"
                           class="form-control @error('access_token') is-invalid @enderror"
                           name="access_token"
                           value="{{ old('access_token', $store->access_token) }}"
                           placeholder="Paste your Shopify access token here">
                    @error('access_token')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Webhooks Secret Key Field -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Webhooks Secret Key</label>
                    <input type="text"
                           class="form-control @error('webhooks_secret_key') is-invalid @enderror"
                           name="webhooks_secret_key"
                           value="{{ old('webhooks_secret_key', $store->webhooks_secret_key) }}"
                           placeholder="Enter your webhooks secret key">
                    @error('webhooks_secret_key')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Store</button>
                    <a href="{{ route('customer.shopify.stores') }}" class="btn btn-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
