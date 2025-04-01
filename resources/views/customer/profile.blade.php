@extends('layouts.customer')

@section('customer-content')
    <div class="container mt-5">
        <h1>Customer Profile</h1>
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">{{ $customer->name }}</h5>
                <p class="card-text"><strong>Email:</strong> {{ $customer->email }}</p>

                <a href="{{ route('customer.settings') }}" class="btn btn-primary">Edit Profile</a>
            </div>
        </div>
    </div>
@endsection
