@extends('layouts.master')

@section('content')
<section class="login-wrapper d-flex">
    <!-- Left Side: Login -->
    <div class="login-left d-flex flex-column justify-content-center align-items-center">
       <div class="logo text-left w-100 px-4 d-flex align-items-center mb-4">
            <img src="{{ asset('images/samplelogo.png') }}" alt="Logo" style="height: 130px; margin-right: 15px;">
            <div>
                <h1 class="mb-0">AVT Hardware Trading</h1>
                <h5 class="mb-0">Sales and Inventory System</h5>
            </div>
        </div>
        <div class="login-box px-4 w-100">
            <form class="login-form" method="POST" action="{{ route('login') }}">
                @csrf
                <h5 class="login-head mb-4"><i class="fa fa-lg fa-fw fa-user"></i>LOG IN</h5>
                <div class="form-group">
                    <label class="control-label">Email</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="control-label">Password</label>
                    <input id="password" type="password"
                        class="form-control @error('password') is-invalid @enderror" name="password" required
                        autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <div class="utility">
                        <div class="animated-checkbox">
                            <label>
                                <input type="checkbox"><span class="label-text">Stay Signed in</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group btn-container">
                    <button class="btn btn-primary btn-block" type="submit">
                        <i class="fa fa-sign-in fa-lg fa-fw"></i>LOG IN
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Side: Analytics or Image -->
    <div class="login-right d-flex align-items-center justify-content-center">
        <div class="analytics-text text-white text-center">
            <h2>Track your Sales</h2>
            <p>Real-time Inventory Monitoring & Analytics</p>
            <img src="{{ asset('images/data-analysis.png') }}" alt="Sales Analytics" class="img-fluid mt-4">
        </div>
    </div>
</section>
@endsection
