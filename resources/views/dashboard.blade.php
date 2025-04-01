@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Optional: Additional Sidebar or Nested Content -->
    </div>
    <div class="row">
        <!-- Dashboard Cards/Options -->
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-users fa-2x"></i>
                        <h5 class="mt-2">Users</h5>
                    </div>
                    <p class="card-text">Manage your application users.</p>
                    <a href="{{ route('users.index') }}" class="btn btn-light">View Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-cogs fa-2x"></i>
                        <h5 class="mt-2">Settings</h5>
                    </div>
                    <p class="card-text">Configure application settings.</p>
                    <a href="{{ route('settings') }}" class="btn btn-light">Manage Settings</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <div class="card-title">
                        <i class="fas fa-chart-line fa-2x"></i>
                        <h5 class="mt-2">Reports</h5>
                    </div>
                    <p class="card-text">View application reports and analytics.</p>
                    <a href="{{ route('reports') }}" class="btn btn-light">View Reports</a>
                </div>
            </div>
        </div>
        <!-- Add more cards as needed -->
    </div>
</div>
@endsection