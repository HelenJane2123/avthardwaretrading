@extends('layouts.master')

@section('title', 'Profile | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-user-circle"></i> Update Profile</h1>
                <p class="text-muted mb-0">Manage your account details, change your password, and refresh your profile picture.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Profile</li>
                <li class="breadcrumb-item active">Update</li>
            </ul>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <strong>Please fix the errors below.</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row mt-4">
            <div class="col-md-10 offset-md-1">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="fa fa-id-card"></i> Profile Settings</h5>
                            <small class="text-muted">Update your personal details, email, password, and profile photo.</small>
                        </div>
                        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 mb-4">
                                <div class="card border-secondary h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <img id="avatarPreview"
                                                 src="{{ asset('images/user/' . (Auth::user()->image ?: 'default-avatar.png')) }}"
                                                 alt="Profile Picture"
                                                 class="img-fluid rounded-circle"
                                                 style="width: 140px; height: 140px; object-fit: cover;">
                                        </div>
                                        <h4 class="mb-1">{{ Auth::user()->fullname }}</h4>
                                        <p class="text-muted mb-1">{{ Auth::user()->email }}</p>
                                        <p class="text-muted small mb-0">Member since {{ Auth::user()->created_at?->format('M d, Y') ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <form action="{{ route('update_profile', Auth::user()->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="Inputfname" class="form-label">First Name</label>
                                            <input value="{{ old('f_name', Auth::user()->f_name) }}"
                                                   name="f_name"
                                                   class="form-control @error('f_name') is-invalid @enderror"
                                                   id="Inputfname"
                                                   type="text"
                                                   placeholder="First name">
                                            @error('f_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="Inputlname" class="form-label">Last Name</label>
                                            <input value="{{ old('l_name', Auth::user()->l_name) }}"
                                                   name="l_name"
                                                   class="form-control @error('l_name') is-invalid @enderror"
                                                   id="Inputlname"
                                                   type="text"
                                                   placeholder="Last name">
                                            @error('l_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="InputEmail1" class="form-label">Email address</label>
                                            <input value="{{ old('email', Auth::user()->email) }}"
                                                   name="email"
                                                   class="form-control @error('email') is-invalid @enderror"
                                                   id="InputEmail1"
                                                   type="email"
                                                   placeholder="Enter your email">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-12 mb-4">
                                            <label for="InputImage" class="form-label">Profile Picture</label>
                                            <input id="InputImage"
                                                   class="form-control @error('image') is-invalid @enderror"
                                                   name="image"
                                                   type="file"
                                                   accept="image/*"
                                                   onchange="previewProfileImage(event)">
                                            @error('image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Upload a JPG, PNG, GIF, or SVG file up to 2MB.</small>
                                        </div>

                                        <div class="col-12">
                                            <hr>
                                            <h5 class="mb-3">Change Password</h5>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="InputPassword" class="form-label">Current Password</label>
                                            <input name="current_password"
                                                   class="form-control @error('current_password') is-invalid @enderror"
                                                   id="InputPassword"
                                                   type="password"
                                                   placeholder="Enter current password">
                                            @error('current_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="InputNewPassword" class="form-label">New Password</label>
                                            <input name="new_password"
                                                   class="form-control @error('new_password') is-invalid @enderror"
                                                   id="InputNewPassword"
                                                   type="password"
                                                   placeholder="Enter new password">
                                            @error('new_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="InputNewPasswordConfirmation" class="form-label">Confirm Password</label>
                                            <input name="new_password_confirmation"
                                                   class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                                   id="InputNewPasswordConfirmation"
                                                   type="password"
                                                   placeholder="Confirm new password">
                                            @error('new_password_confirmation')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                                            <a href="{{ route('home') }}" class="btn btn-secondary">Cancel</a>
                                            <button class="btn btn-primary" type="submit">Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @push('js')
    <script>
        function previewProfileImage(event) {
            const input = event.target;
            const preview = document.getElementById('avatarPreview');
            if (input.files && input.files[0]) {
                preview.src = URL.createObjectURL(input.files[0]);
            }
        }
    </script>
    @endpush
@endsection