@extends('layouts.admin')

@section('admin-content')
    <div class="container mt-5">
        <h1>Manage Packages</h1>
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary mb-3">Add New Package</a>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($packages->isEmpty())
            <p>No packages found. Please add a new package.</p>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Package Name</th>
                        <th>No. of Shops</th>
                        <th>No. of Products</th>
                        <th>Orders</th>
                        <th>Manage Customers</th>
                        <th>Price (Monthly)</th>
                        <th>Locations</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $package)
                        <tr>
                            <td>{{ $package->package_name }}</td>
                            <td>{{ $package->number_of_shops }}</td>
                            <td>{{ $package->number_of_products }}</td>
                            <td>{{ $package->orders }}</td>
                            <td>{{ $package->manage_customers }}</td>
                            <td>${{ number_format($package->price, 2) }}</td>
                            <td>{{ $package->locations }}</td>
                            <td>
                                @if($package->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Deactivated</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.packages.edit', $package->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                
                                <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this package?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                                
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
