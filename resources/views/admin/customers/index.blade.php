@extends('layouts.admin')

@section('title', 'Manage Customers')

@section('admin-content')
    <div class="container mt-5">
        <h1 class="mb-4">Manage Customers</h1>
        
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.customers.index') }}" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search by Name or Email" value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Deactivated</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <!-- Bulk Delete Form -->
        <form action="{{ route('admin.customers.bulkDelete') }}" method="POST">
            @csrf
            @method('DELETE')

            <div class="d-flex justify-content-between mb-3">
                <div>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete selected customers?');">Delete Selected</button>
                </div>
                <div>
                    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">Add New Customer</a>
                </div>
            </div>

            @if($customers->isEmpty())
                <p>No customers found. Please add a new customer.</p>
            @else
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Avatar</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Active Package</th> <!-- New Column -->
                            <th>Verify</th>
                            <th>Registered At</th>

                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $customer->id }}"></td>
                                <td>
                                    @if($customer->avatar)
                                        <img src="{{ asset('storage/' . $customer->avatar) }}" alt="{{ $customer->name }}" width="50" height="50" class="rounded-circle">
                                    @else
                                        <img src="{{ asset('images/default-avatar.png') }}" alt="Default Avatar" width="50" height="50" class="rounded-circle">
                                    @endif
                                </td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>
                                    @if($customer->status)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Deactivated</span>
                                    @endif
                                </td>
                                
                                <td>
                                    @if($customer->activePackage && $customer->activePackage->package)
                                        @php
                                            // Define package color mapping
                                            $packageName = $customer->activePackage->package->package_name;
                                            $badgeClass = '';

                                            switch(strtolower($packageName)) {
                                                case 'basic':
                                                    $badgeClass = 'bg-secondary';
                                                    break;
                                                case 'standard':
                                                    $badgeClass = 'bg-primary';
                                                    break;
                                                case 'premium':
                                                    $badgeClass = 'bg-success';
                                                    break;
                                                default:
                                                    $badgeClass = 'bg-info';
                                                    break;
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $packageName }}
                                        </span>
                                    @else
                                        <span class="text-muted">No Active Package</span>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->email_verified_at)
                                    <span class="badge bg-success">Verified <i class="fa fa-check"></i></span>
                                    @else
                                    <span class="badge bg-danger">Unverify <i class="fa fa-times"></i></span>   
                                    @endif     
                                </td>
                                <td>{{ $customer->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm btn-warning me-1">Edit</a>
                                    
                                    <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                    
                                    <form action="{{ route('admin.customers.toggleStatus', $customer->id) }}" method="POST" style="display:inline-block;" class="ms-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $customer->status ? 'btn-secondary' : 'btn-success' }}">
                                            {{ $customer->status ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination Links -->
                <div class="d-flex justify-content-center">
                    {{ $customers->links() }}
                </div>
            @endif
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        // Select/Deselect All Checkboxes
        document.getElementById('select-all').addEventListener('click', function(event) {
            const checkboxes = document.querySelectorAll('input[name="ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = event.target.checked);
        });
    </script>
@endsection
