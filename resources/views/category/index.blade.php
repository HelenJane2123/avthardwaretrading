

@extends('layouts.master')

@section('titel', 'Category | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Category List</h1>
                <p class="text-muted mb-0">View, update, or delete existing categories to keep your system organized.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Category</li>
                <li class="breadcrumb-item active"><a href="#">Manage Category</a></li>
            </ul>
        </div>
        <div class="">
            <a class="btn btn-sm btn-primary" href="{{route('category.create')}}"><i class="fa fa-plus"></i> Add New Category</a>
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
                    <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Category Records</h3>
                    <div class="tile-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="sampleTable">
                                <thead class="thead-dark medium">
                                    <tr>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="medium">
                                    @foreach( $categories as $category)
                                        <tr id="row-{{ $category->id }}">
                                            <td>{{ $category->name }}</td>
                                            @if($category->status)
                                            <td>Active</td>
                                                @else
                                                <td>Inactive</td>
                                            @endif
                                            <td>
                                                <a class="btn btn-primary btn-sm" href="{{route('category.edit', $category->id)}}"><i class="fa fa-edit" ></i></a>
                                                <button class="btn btn-danger btn-sm" type="button" 
                                                        onclick="deleteTag('{{ $category->id }}', '{{ $category->name }}')">
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
        function deleteTag(categoryId, name) {
            Swal.fire({
                title: 'Delete "' + name + '"?',
                text: "This action cannot be undone!",
                type: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(function(result) {
                if (result.value) { 
                    fetch('{{ route("category.destroy", ":id") }}'.replace(':id', categoryId), {
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
                                var row = document.getElementById('row-' + productId);
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
