@extends('layouts.customer')

@section('title', 'Payment Successful')

@section('customer-content')
<div class="container my-5">
    <div class="card p-4 shadow-sm">
        <h2 class="text-center mb-4">Payment Successful!</h2>
        <p class="text-center">Thank you for your subscription. Your payment has been processed successfully. Click <a href="{{ route('customer.subscription.manage') }}">here</a> to download receipt.</p>
        

            
       

        <div class="text-center mt-4">
            <a href="{{ route('customer.dashboard') }}" class="btn btn-secondary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endsection
