@extends('layouts.master')

@section('titel', 'Supplier | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <!-- Page Title & Breadcrumb -->
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="fa fa-industry"></i> Manage Suppliers</h1>
                <p class="text-muted small">View, add, edit, and manage all suppliers</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Supplier</li>
                <li class="breadcrumb-item active"><a href="#">Manage Supplier</a></li>
            </ul>
        </div>

        <!-- Add Button -->
        <div class="mb-3 text-right">
            <a class="btn btn-primary shadow-sm" href="{{ route('supplier.create') }}">
                <i class="fa fa-plus-circle"></i> Add Supplier
            </a>
        </div>

        <!-- Flash Message -->
        @if(session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <div class="tile-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped" id="sampleTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Supplier Code</th>
                                        <th>Supplier Name</th>
                                        <th>Address</th>
                                        <th>Contact</th>
                                        <th>Email</th>
                                        <th>Tax</th>
                                        <th>Details</th>
                                        <th>Date Created</th>
                                        <th>Date Updated</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suppliers as $supplier)
                                    <tr>
                                        <td><span class="badge badge-info">{{ $supplier->supplier_code }}</span></td>
                                        <td>{{ $supplier->name }}</td>
                                        <td>{{ $supplier->address }}</td>
                                        <td>{{ $supplier->mobile }}</td>
                                        <td>{{ $supplier->email }}</td>
                                        <td>{{ $supplier->tax }}</td>
                                        <td>{{ $supplier->details }}</td>
                                        <td>{{\Carbon\Carbon::parse($supplier->created_at)->format('M d, Y')}}</td>
                                        <td>{{ \Carbon\Carbon::parse($supplier->updated_at)->format('M d, Y') }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-sm btn-info" href="{{ route('supplier.supplier-products', $supplier->id) }}" title="View Products">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a class="btn btn-sm btn-primary" href="{{ route('supplier.edit', $supplier->id) }}" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="deleteTag({{ $supplier->id }})" title="Delete">
                                                    <i class="fa fa-trash-o"></i>
                                                </button>
                                                <form id="delete-form-{{ $supplier->id }}" action="{{ route('supplier.destroy', $supplier->id) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <a class="btn btn-sm btn-warning" href="{{ route('supplier.supplier-products.export', $supplier->id) }}" title="Export">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> <!-- table responsive -->
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('js')
    <script type="text/javascript" src="{{ asset('js/plugins/jquery.dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/plugins/dataTables.bootstrap.min.js') }}"></script>
    <script type="text/javascript">
        $('#sampleTable').DataTable({
            pageLength: 10,
            responsive: true
        });
    </script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script type="text/javascript">
        function deleteTag(id) {
            swal({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    event.preventDefault();
                    document.getElementById('delete-form-' + id).submit();
                } else if (result.dismiss === swal.DismissReason.cancel) {
                    swal('Cancelled', 'The supplier record is safe 🙂', 'error');
                }
            })
        }
    </script>
@endpush
