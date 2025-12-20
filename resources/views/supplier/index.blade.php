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
        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle"></i> {{ session()->get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <!-- Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Supplier Records</h3>
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
                                        <th>Status</th>
                                        <th>Date Created</th>
                                        <th>Date Updated</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($suppliers as $supplier)
                                        <tr id="row-{{ $supplier->id }}">
                                            <td><span class="badge badge-info">{{ $supplier->supplier_code }}</span></td>
                                            <td>{{ $supplier->name }}</td>
                                            <td>{{ $supplier->address }}</td>
                                            <td>{{ $supplier->mobile }}</td>
                                            <td>{{ $supplier->email }}</td>
                                            <td>{{ $supplier->tax }}</td>
                                            <td>{{ $supplier->details }}</td>
                                            <td>
                                                <span class="badge {{ $supplier->status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $supplier->status == 1 ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
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
                                                    <button class="btn btn-danger btn-sm" type="button" 
                                                            onclick="deleteTag('{{ $supplier->id }}','{{ $supplier->name }}')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
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
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script type="text/javascript">
        $('#sampleTable').DataTable({
            pageLength: 10,
            responsive: true
        });
        function deleteTag(supplierId, supplierName) {
            Swal.fire({
                title: 'Delete "' + supplierName + '"?',
                text: "This action cannot be undone!",
                type: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(function(result) {
                if (result.value) { 
                    fetch('{{ route("supplier.destroy", ":id") }}'.replace(':id', supplierId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire({
                            title: data.status === 'success' ? 'Deleted!' : 'Error!',
                            text: data.message,
                            type: data.status === 'success' ? 'success' : 'error'
                        }).then(function() {
                            if (data.status === 'success') {
                                // Remove row dynamically
                                var row = document.getElementById('row-' + salesmanId);
                                if (row) row.remove();
                            }
                        });
                    })
                    .catch(function() {
                        Swal.fire('Error', 'Something went wrong!', 'error');
                    });
                }
            });
        }
    </script>
@endpush
