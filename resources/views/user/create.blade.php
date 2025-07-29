@extends('layouts.master')

@section('title', 'User| ')
@section('content')
@include('partials.header')
@include('partials.sidebar')
<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-edit"></i> Add New User</h1>
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
                                <input name="l_name" class="form-control @error('f_name') is-invalid @enderror" type="text" placeholder="Enter First Name">
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
                                <input name="password" class="form-control" type="text" placeholder="Enter New Password" id="passwordField">
                                @error('paasword')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <script>
                                // This script will run once the page is loaded
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Get the input field by its ID
                                    const passwordInput = document.getElementById('passwordField');

                                    // Check if the input field exists before trying to set its value
                                    if (passwordInput) {
                                        // Set the value of the input field
                                        passwordInput.value = 'MyTestPassword123'; // Replace with your desired test password
                                    }
                                });
                            </script>

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
