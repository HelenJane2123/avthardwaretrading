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
           <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session()->has('reset_success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle"></i> {!! session('reset_success') !!}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="row">
            <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Edit User Form</h3>
                    <div class="tile-body">
                        @if(auth()->user()->user_role === 'super_admin')
                            <div class="mb-4 p-3 border rounded bg-light">
                                <h5 class="mb-2"><i class="fa fa-lock text-warning"></i> Reset User Password</h5>
                                <p class="mb-3 text-muted">
                                    Click the button below to reset this user’s password. A <strong>temporary password</strong> will be generated.
                                    Make sure to <strong>copy and share it</strong> with the user so they can log in.
                                </p>
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                    <i class="fa fa-refresh"></i> Reset Password
                                </button>
                            </div>
                        @endif

                        {{-- User Update Form --}}
                        <form class="row" method="POST" action="{{ route('user.update', $user->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group col-md-6">
                                <label class="control-label">User First Name</label>
                                <input value="{{ old('f_name', $user->f_name) }}" name="f_name" class="form-control form-control-sm @error('f_name') is-invalid @enderror" type="text" placeholder="Enter First Name">
                                @error('f_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">User Last Name</label>
                                <input value="{{ old('l_name', $user->l_name) }}" name="l_name" class="form-control form-control-sm @error('l_name') is-invalid @enderror" type="text" placeholder="Enter Last Name">
                                @error('l_name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Email</label>
                                <input value="{{ old('email', $user->email) }}" name="email" class="form-control form-control-sm @error('email') is-invalid @enderror" type="email" placeholder="Enter Email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="control-label">Contact</label>
                                <input value="{{ old('contact', $user->contact) }}" name="contact" class="form-control form-control-sm @error('contact') is-invalid @enderror" type="text" placeholder="Enter Contact Number">
                                @error('contact')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-3">
                                <label class="control-label" for="role">Role</label>
                                <select name="user_role" id="role" class="form-control form-control-sm @error('user_role') is-invalid @enderror">
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
                                <select name="user_status" id="status" class="form-control form-control-sm @error('user_status') is-invalid @enderror">
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('user_status', $user->user_status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('user_status', $user->user_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('user_status')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12 mt-3">
                                <button class="btn btn-sm btn-success" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i>Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
<!-- Reset Password Confirmation Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="resetPasswordModalLabel"><i class="fa fa-refresh"></i> Confirm Reset Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to reset this user’s password? <br>
        A <strong>temporary password</strong> will be generated and shown after confirmation.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form action="{{ route('user.resetPassword', $user->id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-warning">
                Yes, Reset Password
            </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyTempPassword() {
            const field = document.getElementById('tempPasswordField');
            field.select();
            field.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(field.value);
            
            // Bootstrap-style feedback
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                Password copied to clipboard!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(alertDiv);

            setTimeout(() => alertDiv.remove(), 3000);
        }
    </script>
@endpush

