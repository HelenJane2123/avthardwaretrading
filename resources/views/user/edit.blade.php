@extends('layouts.master')

@section('title', 'User | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Edit User</h1>
                <p class="text-muted mb-0">Update user information and system access details.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">User</li>
                <li class="breadcrumb-item"><a href="#">Edit User</a></li>
            </ul>
        </div>

        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="row">
            <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Edit User Form</h3>
                    <div class="tile-body">
                        <form class="row" method="POST" action="{{ route('user.update', $user->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group col-md-6">
                                <label class="control-label">User First Name</label>
                                <input value="{{ old('f_name', $user->f_name) }}" name="f_name" class="form-control @error('f_name') is-invalid @enderror" type="text" placeholder="Enter First Name">
                                @error('f_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="control-label">User Last Name</label>
                                <input value="{{ old('l_name', $user->l_name) }}" name="l_name" class="form-control @error('l_name') is-invalid @enderror" type="text" placeholder="Enter Last Name">
                                @error('l_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Email</label>
                                <input value="{{ old('email', $user->email) }}" name="email" class="form-control @error('email') is-invalid @enderror" type="email" placeholder="Enter Email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Contact</label>
                                <input value="{{ old('contact', $user->contact) }}" name="contact" class="form-control @error('contact') is-invalid @enderror" type="text" placeholder="Enter Contact Number">
                                @error('contact')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <!-- <div class="form-group col-md-6">
                                <label class="control-label">Password</label>
                                <input name="password" class="form-control @error('password') is-invalid @enderror" type="text" placeholder="Enter New Password" id="passwordField">
                                @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div> -->
                            <div class="form-group col-md-3">
                                <label class="control-label" for="role">Role</label>
                                <select name="user_role" id="role" class="form-control @error('user_role') is-invalid @enderror">
                                    <option value="">Select Role</option>
                                    <option value="super_admin" {{ old('user_role', $user->user_role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                    <option value="admin" {{ old('user_role', $user->user_role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="staff" {{ old('user_role', $user->user_role) == 'staff' ? 'selected' : '' }}>Staff</option>
                                </select>
                                @error('user_role')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="control-label" for="status">Status</label>
                                <select name="user_status" id="status" class="form-control @error('user_status') is-invalid @enderror">
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('user_status', $user->user_status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('user_status', $user->user_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('user_status')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 mt-3">
                                <button class="btn btn-success" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i>Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
