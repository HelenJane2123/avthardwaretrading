@extends('layouts.master')

@section('content')
<section class="login-wrapper d-flex">

    <!-- Left Side: Login -->
    <div class="login-left d-flex flex-column justify-content-center align-items-center">
        <div class="logo text-left w-100 px-4 d-flex align-items-center mb-4">
            <img src="{{ asset('images/avt_logo.png') }}" alt="Logo" style="height: 130px; margin-right: 15px;">
            <div>
                <h1 class="mb-0">AVT Hardware Trading</h1>
                <h5 class="mb-0">Wholesale of hardware, electricals, & plumbing supply etc.</h5>
            </div>
        </div>

        <div class="login-box px-4 w-100">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa fa-check-circle"></i>  {{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <form id="secureLoginForm" class="login-form" method="POST" action="{{ route('login') }}">
                @csrf
                {{-- Honeypot field (bot trap) --}}
                <input type="text" name="username_confirm" style="display:none">

                <h5 class="login-head mb-4">
                    <i class="fa fa-lg fa-fw fa-user"></i> LOG IN
                </h5>

                {{-- Email --}}
                <div class="form-group">
                    <label for="email" class="control-label">Email <span class="text-danger">*</span></label>
                    <input id="email" type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                        placeholder="Enter your email address">

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="form-group position-relative">
                    <label for="password" class="control-label">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password" required autocomplete="current-password" placeholder="Enter your password">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#password">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Stay signed in --}}
                <div class="form-group">
                    <div class="utility">
                        <div class="animated-checkbox">
                            <label>
                                <input type="checkbox" name="remember" id="remember">
                                <span class="label-text">Stay signed in</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Optional reCAPTCHA --}}
                {{-- <div class="form-group">
                    {!! NoCaptcha::display() !!}
                    @error('g-recaptcha-response')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div> --}}

                {{-- Submit button --}}
                <div class="form-group btn-container">
                    <button id="loginBtn" class="btn btn-primary btn-block" type="submit">
                        <i class="fa fa-sign-in fa-lg fa-fw"></i> LOG IN
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Side -->
    <div class="login-right d-flex align-items-center justify-content-center">
        <div class="analytics-text text-white text-center">
            <h2>Track your Sales</h2>
            <p>Real-time Inventory Monitoring & Analytics</p>
            <div class="hero-right-image">
                <img src="{{ asset('images/data-analysis.png') }}" alt="Sales Analytics" class="img-fluid mt-4">
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
$(function () {
    // Password visibility toggle
    $('.toggle-password').on('click', function () {
        const input = $($(this).data('target'));
        const icon = $(this).find('i');
        const isHidden = input.attr('type') === 'password';

        input.attr('type', isHidden ? 'text' : 'password');
        icon.toggleClass('fa-eye fa-eye-slash');

        // Auto-hide after 10 seconds for security
        if (isHidden) {
            setTimeout(() => {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }, 10000);
        }
    });

    // Disable login button during submission
    $('#secureLoginForm').on('submit', function () {
        $('#loginBtn').prop('disabled', true)
                      .html('<i class="fa fa-spinner fa-spin"></i> Logging in...');
    });
});
</script>
@endpush
