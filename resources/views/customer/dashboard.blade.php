@extends('layouts.customer')

@section('customer-content')
<div class="container">
    <h2>Welcome, {{ Auth::guard('customer')->user()->name }} Dashboard</h2>
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Your Stores</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $storeCount }}</h5>
                    <p class="card-text">Total Stores Added</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Selected Products</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $productCount }}</h5>
                    <p class="card-text">Total Products Selected</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Your Package</div>
                <div class="card-body">
                    <h5 class="card-title">{{ $packageName }}</h5>
                    <p class="card-text">Currently Active Package</p>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <h3>Stores and Products Over Time</h3>
            <canvas id="myChart" height="100"></canvas>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartLabels = @json($chartLabels); // e.g. ["2025-01-01","2025-01-02"]
    const storeData   = @json($storeChartData); // e.g. [2,1,0,3,...]
    const productData = @json($productChartData); // e.g. [5,0,1,2,...]

    const ctx = document.getElementById('myChart').getContext('2d');

    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Stores Added',
                    data: storeData,
                    borderColor: 'rgba(54, 162, 235, 1)',  // Blue
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.1
                },
                {
                    label: 'Products Selected',
                    data: productData,
                    borderColor: 'rgba(255, 99, 132, 1)', // Pink/Red
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            }
        }
    });
});
</script>
@endsection
