@extends('layouts.master')

@section('title', 'User| ')
@section('content')
@include('partials.header')
@include('partials.sidebar')
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-user-plus"></i> Create User</h1>
            <p class="text-muted mb-0">Create a new user for system access.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">User</li>
            <li class="breadcrumb-item"><a href="#">Add New User</a></li>
        </ul>
    </div>

    <div class="">
        <a class="btn btn-primary" href="{{ route('user.index') }}">
            <i class="fa fa-edit"> </i> Manage Users
        </a>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title">User</h3>
                @if(session()->has('message'))
                    <div class="alert alert-success">
                        {{ session()->get('message') }}
                    </div>
                @endif
                <div class="tile-body">
                    <form method="POST" action="{{ route('user.store') }}">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="control-label">Last Name</label>
                                <input name="l_name" class="form-control @error('f_name') is-invalid @enderror" type="text" placeholder="Enter Last Name">
                                @error('l_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">First Name</label>
                                <input name="f_name" class="form-control @error('l_name') is-invalid @enderror" type="text" placeholder="Enter First Name">
                                @error('f_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                             <div class="form-group col-md-6">
                                <label class="control-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">Contact</label>
                                <input name="contact" class="form-control @error('contact') is-invalid @enderror" type="text" placeholder="Enter Contact Number">
                                @error('contact')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                           <div class="form-group col-md-6">
                                <label class="control-label">Password</label>
                                <div class="input-group">
                                    <input name="password" id="passwordField" class="form-control password-field"
                                        type="password" placeholder="Enter New Password">

                                    <div class="input-group-append">
                                    <!-- toggle button references the input with data-target -->
                                    <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="#passwordField" aria-label="Toggle password">
                                        <i class="fa fa-eye"></i>
                                    </button>

                                    <!-- optional generate button (fills and shows the password) -->
                                    <button class="btn btn-outline-success" type="button" id="generatePassword" data-target="#passwordField">
                                        <i class="fa fa-refresh"></i> Generate
                                    </button>
                                    </div>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                                </div>


                            <div class="form-group col-md-3">
                                <label class="control-label" for="role">Role</label>
                                <select name="user_role" id="role" class="form-control @error('user_role') is-invalid @enderror">
                                    <option value="">Select Role</option> 
                                    <option value="super_admin">Super Admin</option>
                                    <option value="admin">Admin</option>
                                    <option value="staff">Staff</option>
                                </select>
                                @error('user_role')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            
                            <div class="form-group col-md-3">
                                <label class="control-label" for="status">Status</label>
                                <select name="user_status" id="status" class="form-control @error('user_status') is-invalid @enderror">
                                    <option value="">Select Status</option> <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('user_status')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                           
                            <div class="form-group col-md-12 mt-3">
                                <button class="btn btn-success float-right" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i> Add User Details
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
@push('js')
<script>
    $(function () {
        // Toggle password visibility (delegated handler)
        $(document).on('click', '.btn-toggle-password', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var targetSelector = $btn.data('target');
            var $input = $(targetSelector);

            if (!$input.length) {
            console.warn('Password toggle: target not found for', targetSelector);
            return;
            }

            var inputEl = $input.get(0);
            if (inputEl.type === 'password') {
            inputEl.type = 'text';
            $btn.find('i').removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
            inputEl.type = 'password';
            $btn.find('i').removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Generate password and show it
        $(document).on('click', '#generatePassword', function (e) {
            e.preventDefault();
            var length = 12;
            var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*";
            var pass = "";
            for (var i = 0; i < length; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
            }

            var targetSelector = $(this).data('target') || '#passwordField';
            var $input = $(targetSelector);
            if (!$input.length) {
            console.warn('Generate password: target not found for', targetSelector);
            return;
            }

            $input.val(pass);
            // show the generated password immediately
            $input.attr('type', 'text');

            // update toggle icon if there is a toggle for this input
            $('.btn-toggle-password[data-target="' + targetSelector + '"]').find('i')
            .removeClass('fa-eye').addClass('fa-eye-slash');
        });
        });

    document.getElementById('togglePassword').addEventListener('click', function () {
        let passwordField = document.getElementById('passwordField');
        let icon = this.querySelector('i');

        if (passwordField.type === "password") {
            passwordField.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>
@endpush
