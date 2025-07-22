

@extends('layouts.master')

@section('titel', 'Customer | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Manage Customer</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Customer</li>
                <li class="breadcrumb-item active"><a href="#">Manage Customer</a></li>
            </ul>
        </div>
        <div class="">
            <a class="btn btn-primary" href="{{route('customer.create')}}"><i class="fa fa-plus"></i> Add New Customer</a>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                    @if(session()->has('message'))
                        <div class="alert alert-success">
                            {{ session()->get('message') }}
                        </div>
                    @endif
                    <div class="tile-body">
                        <div class="d-flex justify-content-end mb-3">
                            <a class="btn btn-success" href="{{ route('export.customers') }}">
                                <i class="fa fa-file-excel-o"></i> Export to Excel
                            </a>
                        </div>
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                            <tr>
                                <th>Customer </th>
                                <th>Address </th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Tax No.</th>
                                <th>Details</th>
                                <th>Credit Balance</th>
                                <th>Date Created</th>
                                <th>Date Updated</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $customers as $customer)
                            <tr>
                                <td>{{ $customer->name }} </td>
                                <td>{{ $customer->address }} </td>
                                <td>{{ $customer->mobile }} </td>
                                <td>{{ $customer->email }} </td>
                                <td>{{ $customer->tax }} </td>
                                <td>{{ $customer->details }} </td>
                                <td>{{ $customer->previous_balance }} </td>
                                <td>{{ $customer->created_at }} </td>
                                <td>{{ $customer->updated_at }} </td>
                                 <td>
                                    <a class="btn btn-primary btn-sm" href="{{route('customer.edit', $customer->id)}}"><i class="fa fa-edit" ></i></a>
                                    <button class="btn btn-danger btn-sm waves-effect" type="submit" onclick="deleteTag({{ $customer->id }})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $customer->id }}" action="{{ route('customer.destroy',$customer->id) }}" method="POST" style="display: none;">
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
    <script type="text/javascript">$('#sampleTable').DataTable();</script>
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
