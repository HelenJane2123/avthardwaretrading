@extends('layouts.master')

@section('titel', 'User | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Manage User</h1>
                <p class="text-muted mb-0">View, edit, or remove users with system access.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">User</li>
                <li class="breadcrumb-item active"><a href="#">Manage User</a></li>
            </ul>
        </div>
        <div class="">
            <a class="btn btn-primary" href="{{route('user.create')}}"><i class="fa fa-plus"></i> Add New User</a>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                    @if(session()->has('message'))
                        <div class="alert alert-success">
                            {{ session()->get('message') }}
                        </div>
                    @endif
                        <h3 class="tile-title mb-3"><i class="fa fa-table"></i> User Records</h3>
                        <table class="table table-hover table-bordered" id="userTable">
                            <thead class="thead-dark">
                            <tr>
                                <th> First Name </th>
                                <th> Last Name </th>
                                <th> Email </th>
                                <th>Image</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Date Created</th>
                                <th>Date Updated</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $users as $user)
                            <tr>
                                <td>{{ $user->f_name }} </td>
                                <td>{{ $user->l_name }} </td>
                                <td>{{ $user->email }} </td>
                                <td>{{ $user->image }} </td>
                                <td>{{ $user->contact }} </td>
                                <td>{{ $user->user_role }} </td>
                                <td>{{ $user->user_status }} </td>
                                <td>{{ $user->created_at }} </td>
                                <td>{{ $user->updated_at }} </td>
                                 <td>
                                    <a class="btn btn-primary btn-sm" href="{{route('user.edit', $user->id)}}"><i class="fa fa-edit" ></i></a>
                                    <button class="btn btn-danger btn-sm waves-effect" type="submit" onclick="deleteTag({{ $user->id }})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $user->id }}" action="{{ route('user.destroy',$user->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>



@endsection

@push('js')
    <script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">$('#userTable').DataTable();</script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script type="text/javascript">
        function deleteTag(id) {
            swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    event.preventDefault();
                    document.getElementById('delete-form-'+id).submit();
                } else if (
                    // Read more about handling dismissals
                    result.dismiss === swal.DismissReason.cancel
                ) {
                    swal(
                        'Cancelled',
                        'Your data is safe :)',
                        'error'
                    )
                }
            })
        }
    </script>
@endpush
