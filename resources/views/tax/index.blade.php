

@extends('layouts.master')

@section('titel', 'Tax | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Manage Ta/Discount</h1>
                <p class="text-muted mb-0">View, update, or delete existing discounts.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Tax</li>
                <li class="breadcrumb-item active"><a href="#">Manage Tax</a></li>
            </ul>
        </div>
        <div class="">
            <a class="btn btn-sm btn-primary" href="{{route('tax.create')}}"><i class="fa fa-plus"> </i> Add New Tax</a>
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
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Discount Records</h3>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="sampleTable">
                                <thead class="thead-dark medium">
                                <tr>
                                    <th>Tax </th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody class="medium">
                                    @foreach( $taxes as $tax)
                                        <tr id="row-{{ $tax->id }}">
                                            <td>{{ $tax->name }} %</td>
                                            @if($tax->status)
                                            <td>Active</td>
                                                @else
                                                <td>Inactive</td>
                                            @endif
                                            <td>
                                                <a class="btn btn-primary btn-sm" href="{{route('tax.edit', $tax->id)}}"><i class="fa fa-edit" ></i></a>
                                                <button class="btn btn-danger btn-sm" type="button" 
                                                        onclick="deleteTag('{{ $tax->name }}')">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
        function deleteTag(name) {
            Swal.fire({
                title: 'Delete this disocunt?',
                text: "This action cannot be undone!",
                type: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(function(result) {
                if (result.value) { 
                    fetch('{{ route("tax.destroy", ":name") }}'.replace(':name', name), {
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
                                var row = document.getElementById('row-' + discountId);
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
