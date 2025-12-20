@extends('layouts.master')

@section('title', 'Salesman | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-users"></i> Manage Salesman</h1>
                <p class="text-muted mb-0">View, add, edit, and manage all salesman</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Salesman</li>
                <li class="breadcrumb-item active">Manage</li>
            </ul>
        </div>

        <div class="d-flex justify-content-between mb-3">
            <a class="btn btn-primary shadow-sm" href="{{ route('salesmen.create') }}">
                <i class="fa fa-plus"></i> Add Salesman
            </a>
            <a class="btn btn-success shadow-sm" href="{{ route('export.salesman') }}">
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
        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle"></i> {{ session()->get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="tile shadow-sm rounded">
            <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Salesman Records</h3>
            <div class="tile-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-center align-middle" id="sampleTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>Salesman Code</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($salesmen as $salesman)
                            <tr id="row-{{ $salesman->id }}">
                                <td><span class="badge badge-info">{{ $salesman->salesman_code }}</span></td>
                                <td>{{ $salesman->salesman_name }}</td>
                                <td>{{ $salesman->address }}</td>
                                <td>{{ $salesman->phone }}</td>
                                <td>{{ $salesman->email }}</td>
                                <td>
                                    <span class="badge {{ $salesman->status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $salesman->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($salesman->created_at)->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($salesman->updated_at)->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a class="btn btn-sm btn-primary" 
                                           href="{{ route('salesmen.edit', $salesman->id) }}" 
                                           title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm" type="button" 
                                                    onclick="deleteTag('{{ $salesman->id }}','{{ $salesman->salesman_name }}')">
                                                <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
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
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script>
        $('#sampleTable').DataTable({
            "order": [[ 0, "desc" ]],
            "pageLength": 10,
            "responsive": true
        });
        function deleteTag(salesmanId, salesmanName) {
            Swal.fire({
                title: 'Delete "' + salesmanName + '"?',
                text: "This action cannot be undone!",
                type: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(function(result) {
                if (result.value) { 
                    fetch('{{ route("salesmen.destroy", ":id") }}'.replace(':id', salesmanId), {
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
