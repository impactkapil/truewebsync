@extends('frontend.layouts.app')

@section('title', 'Customer Login')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <h2 class="mb-4 text-center">Customer Login</h2>

                <!-- Display Success or Error Messages -->
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('customer.login.submit') }}" method="POST">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               required>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-3 form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="remember"
                               name="remember"
                            {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100">Login</button>

                    <!-- Forgot Password Link (Optional) -->
                    <div class="mt-3 text-center">
                        <a href="{{ route('password.request') }}">Forgot Your Password?</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
@endsection
