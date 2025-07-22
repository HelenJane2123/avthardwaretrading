@extends('layouts.master')

@section('titel', 'User | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Manage User</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Customer</li>
                <li class="breadcrumb-item active"><a href="#">Manage User</a></li>
            </ul>
        </div>
        <div class="">
            <a class="btn btn-primary" href="{{route('user.create')}}"><i class="fa fa-plus"></i> Add New User</a>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <table class="table table-hover table-bordered" id="userTable">
                            <thead>
                            <tr>
                                <th>Name </th>
                                <th>Email </th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <tr>
                                    <td>John Doe</td>
                                    <td>john.doe@example.com</td>
                                    <td>+1234567890</td> {{-- Added dummy contact --}}
                                    <td>
                                        <select class="form-control"> {{-- Using form-control for Bootstrap styling --}}
                                            <option value="super_admin">Super Admin</option>
                                            <option value="admin" selected>Admin</option>
                                            <option value="staff">Staff</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control"> {{-- Using form-control for Bootstrap styling --}}
                                            <option value="active" selected>Active</option>
                                            <option value="deactive">Deactive</option>
                                        </select>
                                    </td>
                                    <td class="text-center"> {{-- Centered actions --}}
                                        <a href="#" class="btn btn-info btn-sm mr-1" title="Edit"> {{-- Using Bootstrap buttons for consistent styling --}}
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-danger btn-sm" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @push('js')
    <script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">$('#userTable').DataTable();</script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    @endpush