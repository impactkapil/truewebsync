@extends('layouts.admin')

@section('admin-content')
    <div class="container mt-5">
        <h1>Edit Package</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.packages.update', $package->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="package_name" class="form-label">Package Name</label>
                <input type="text" name="package_name" class="form-control" id="package_name" value="{{ old('package_name', $package->package_name) }}" required>
            </div>

            <div class="mb-3">
                <label for="number_of_shops" class="form-label">Number of Shops</label>
                <input type="number" name="number_of_shops" class="form-control" id="number_of_shops" value="{{ old('number_of_shops', $package->number_of_shops) }}" min="1" required>
            </div>

            <div class="mb-3">
                <label for="number_of_products" class="form-label">Number of Products</label>
                <input type="number" name="number_of_products" class="form-control" id="number_of_products" value="{{ old('number_of_products', $package->number_of_products) }}" min="1" required>
            </div>

            <div class="mb-3">
                <label for="orders" class="form-label">Orders</label>
                <input type="number" name="orders" class="form-control" id="orders" value="{{ old('orders', $package->orders) }}" min="1" required>
            </div>

            <div class="mb-3">
                <label for="manage_customers" class="form-label">Manage Customers</label>
                <input type="number" name="manage_customers" class="form-control" id="manage_customers" value="{{ old('manage_customers', $package->manage_customers) }}" min="1" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price (Monthly)</label>
                <input type="number" name="price" class="form-control" id="price" value="{{ old('price', $package->price) }}" min="0" step="0.01" required>
            </div>

            <div class="mb-3">
                <label for="locations" class="form-label">Locations</label>
                <input type="number" name="locations" class="form-control" id="locations" value="{{ old('locations', $package->locations) }}" min="1" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="1" {{ old('status', $package->status) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $package->status) == 0 ? 'selected' : '' }}>Deactivated</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Package</button>
            <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
@endsection
