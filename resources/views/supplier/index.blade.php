

@extends('layouts.master')

@section('titel', 'Supplier | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Manage Supplier</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Supplier</li>
                <li class="breadcrumb-item active"><a href="#">Manage Supplier</a></li>
            </ul>
        </div>
        <div class="">
            <a class="btn btn-primary" href="{{route('supplier.create')}}"><i class="fa fa-plus"></i> Add Supplier</a>
        </div>

        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                            <tr>
                                <th>Supplier Code</th>
                                <th>Supplier Name</th>
                                <th>Address </th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Tax</th>
                                <th>Details</th>
                                <th>Date Created</th>
                                <th>Date Updated</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $suppliers as $supplier)
                            <tr>
                                <td>{{ $supplier->supplier_code }} </td>
                                <td>{{ $supplier->name }} </td>
                                <td>{{ $supplier->address }} </td>
                                <td>{{ $supplier->mobile }} </td>
                                <td>{{ $supplier->email }} </td>
                                <td>{{ $supplier->tax }} </td>
                                <td>{{ $supplier->details }} </td>
                                <td>{{ $supplier->created_at }} </td>
                                <td>{{ $supplier->updated_at }} </td>
                                <td>
                                    <a class="btn btn-info btn-sm" href="{{ route('supplier.supplier-products', $supplier->id) }}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a class="btn btn-primary btn-sm" href="{{ route('supplier.edit', $supplier->id) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm waves-effect" type="submit" onclick="deleteTag({{ $supplier->id }})">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                    <form id="delete-form-{{ $supplier->id }}" action="{{ route('supplier.destroy', $supplier->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <a class="btn btn-warning btn-sm" href="{{ route('supplier.supplier-products.export', $supplier->id) }}">
                                        <i class="fa fa-download"></i>
                                    </a>
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
        function viewProducts(supplierId) {
            $('#productModal').modal('show');
            $('#productModalContent').html('Loading...');

            $.ajax({
                url: '/supplier/' + supplierId + '/products/ajax',
                type: 'GET',
                success: function (response) {
                    $('#productModalContent').html(response);
                },
                error: function () {
                    $('#productModalContent').html('<div class="alert alert-danger">Failed to load products.</div>');
                }
            });
        }
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
