@extends('layouts.master')

@section('title', 'Customer | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-users"></i> Manage Customers</h1>
                <p class="text-muted mb-0">View, add, edit, and manage all customers</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Customer</li>
                <li class="breadcrumb-item active">Manage</li>
            </ul>
        </div>

        <div class="d-flex justify-content-between mb-3">
            <a class="btn btn-primary shadow-sm" href="{{ route('customer.create') }}">
                <i class="fa fa-plus"></i> Add Customer
            </a>
            <a class="btn btn-success shadow-sm" href="{{ route('export.customers') }}">
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </a>
        </div>

        @if(session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="tile shadow-sm rounded">
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-center align-middle" id="sampleTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>Customer Code</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Tax No.</th>
                                <th>Details</th>
                                <th>Credit Balance</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($customers as $customer)
                            <tr>
                                <td>{{ $customer->customer_code }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->address }}</td>
                                <td>{{ $customer->mobile }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->tax }}</td>
                                <td>{{ $customer->details }}</td>
                                <td>{{ number_format($customer->previous_balance, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($customer->created_at)->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($customer->updated_at)->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a class="btn btn-sm btn-primary" 
                                           href="{{ route('customer.edit', $customer->id) }}" 
                                           title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" 
                                                type="button" 
                                                onclick="deleteTag({{ $customer->id }})"
                                                title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $customer->id }}" 
                                          action="{{ route('customer.destroy',$customer->id) }}" 
                                          method="POST" 
                                          class="d-none">
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
    </main>
@endsection

@push('js')
    <script src="{{ asset('/') }}js/plugins/jquery.dataTables.min.js"></script>
    <script src="{{ asset('/') }}js/plugins/dataTables.bootstrap.min.js"></script>
    <script>
        $('#sampleTable').DataTable({
            "order": [[ 0, "desc" ]],
            "pageLength": 10,
            "responsive": true
        });
    </script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script>
        function deleteTag(id) {
            swal({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fa fa-check"></i> Yes, delete it!',
                cancelButtonText: '<i class="fa fa-times"></i> Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    event.preventDefault();
                    document.getElementById('delete-form-' + id).submit();
                } else if (result.dismiss === swal.DismissReason.cancel) {
                    swal('Cancelled', 'Your data is safe :)', 'error')
                }
            })
        }
    </script>
@endpush
