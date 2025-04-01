@extends('layouts.customer')

@section('title', 'Subscription Expired')

@section('customer-content')
<div class="container my-5">
    <div class="alert alert-warning text-center">
        <h2>Your subscription has expired or is inactive.</h2>
        <p>Please renew your subscription to continue accessing premium features.</p>
        <a href="{{ route('packages') }}" class="btn btn-primary">Renew Subscription</a>
    </div>
</div>
@endsection
