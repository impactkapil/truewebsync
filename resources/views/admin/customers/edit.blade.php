@extends('layouts.admin')

@section('title', 'Edit Customer')

@section('admin-content')
    <div class="container-fluid mt-4">
        <h1 class="mb-4">Edit Customer</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $customer->name) }}" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $customer->email) }}" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password (Leave blank to keep unchanged)</label>
                <input type="password" name="password" class="form-control" id="password">
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
            </div>

            <div class="mb-3">
                <label for="avatar" class="form-label">Avatar (Optional)</label>
                <input type="file" name="avatar" class="form-control" id="avatar" accept="image/*">
                @if($customer->avatar)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $customer->avatar) }}" alt="{{ $customer->name }}" width="100" height="100" class="rounded-circle">
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select" required>
                    <option value="1" {{ old('status', $customer->status) == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $customer->status) == '0' ? 'selected' : '' }}>Deactivated</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Customer</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Back to Customers</a>
        </form>
    </div>
@endsection
