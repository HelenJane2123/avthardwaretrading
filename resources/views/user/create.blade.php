@extends('layouts.master')

@section('title', 'Create User | ')

@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-user-plus"></i> Create User</h1>
            <p class="text-muted mb-0">Create a new user for system access.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">User</li>
            <li class="breadcrumb-item active">Add New User</li>
        </ul>
    </div>

    <div class="mb-3">
        <a class="btn btn-sm btn-outline-primary" href="{{ route('user.index') }}">
            <i class="fa fa-list"></i> Manage Users
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title">User Details</h3>

                @if(session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> {{ session('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="tile-body">
                    <form method="POST" action="{{ route('user.store') }}">
                        @csrf
                        <div class="row">
                            {{-- First Name --}}
                            <div class="form-group col-md-6">
                                <label class="control-label">First Name <span class="text-danger">*</span></label>
                                <input name="f_name" value="{{ old('f_name') }}" class="form-control form-control-sm @error('f_name') is-invalid @enderror" 
                                       type="text" placeholder="Enter first name" required autofocus>
                                @error('f_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            {{-- Last Name --}}
                            <div class="form-group col-md-6">
                                <label class="control-label">Last Name <span class="text-danger">*</span></label>
                                <input name="l_name" value="{{ old('l_name') }}" class="form-control form-control-sm @error('l_name') is-invalid @enderror" 
                                       type="text" placeholder="Enter last name" required>
                                @error('l_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="form-group col-md-6">
                                <label class="control-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" 
                                       class="form-control form-control-sm @error('email') is-invalid @enderror" placeholder="Enter email address" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            {{-- Contact --}}
                            <div class="form-group col-md-6">
                                <label class="control-label">Contact Number</label>
                                <input name="contact" value="{{ old('contact') }}" class="form-control form-control-sm @error('contact') is-invalid @enderror" 
                                       type="text" placeholder="Enter contact number">
                                @error('contact')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div class="form-group col-md-6">
                                <label class="control-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input name="password" id="passwordField" class="form-control form-control-sm @error('password') is-invalid @enderror" 
                                           type="password" placeholder="Enter or generate password" required>

                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="#passwordField" aria-label="Toggle password">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" type="button" id="generatePassword" data-target="#passwordField">
                                            <i class="fa fa-refresh"></i> Generate
                                        </button>
                                    </div>
                                </div>
                                @error('password')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            {{-- Role --}}
                            <div class="form-group col-md-3">
                                <label class="control-label">Role <span class="text-danger">*</span></label>
                                <select name="user_role" id="role" class="form-control form-control-sm @error('user_role') is-invalid @enderror" required>
                                    <option value="">Select Role</option>
                                    <option value="super_admin" {{ old('user_role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                    <option value="admin" {{ old('user_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="staff" {{ old('user_role') == 'staff' ? 'selected' : '' }}>Staff</option>
                                </select>
                                @error('user_role')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="form-group col-md-3">
                                <label class="control-label">Status <span class="text-danger">*</span></label>
                                <select name="user_status" id="status" class="form-control form-control-sm @error('user_status') is-invalid @enderror" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('user_status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('user_status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('user_status')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            {{-- Submit --}}
                            <div class="form-group col-md-12 mt-4">
                                <button class="btn btn-sm btn-success float-right" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i> Save User
                                </button>
                            </div>
                        </div>
                    </form>
                </div> {{-- tile-body --}}
            </div> {{-- tile --}}
        </div>
    </div>
</main>
@endsection

@push('js')
<script>
$(function () {
    // Toggle password visibility
    $(document).on('click', '.btn-toggle-password', function (e) {
        e.preventDefault();
        const target = $($(this).data('target'));
        const icon = $(this).find('i');

        if (target.attr('type') === 'password') {
            target.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            target.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Generate password
    $('#generatePassword').on('click', function () {
        const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*";
        const pass = Array.from({length: 12}, () => chars.charAt(Math.floor(Math.random() * chars.length))).join('');
        const input = $($(this).data('target'));

        input.val(pass).attr('type', 'text');
        $('.btn-toggle-password[data-target="' + $(this).data('target') + '"]').find('i')
            .removeClass('fa-eye').addClass('fa-eye-slash');
    });
});
</script>
@endpush
