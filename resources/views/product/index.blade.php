@extends('layouts.master')

@section('titel', 'Product | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <!-- Page Title -->
        <div class="app-title">
            <div>
                <h1><i class="fa fa-cubes"></i> Manage Inventory</h1>
                <p class="text-muted mb-0">View, add, update, or delete products to keep your inventory organized.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Product</li>
                <li class="breadcrumb-item active"><a href="#">Product Table</a></li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a class="btn btn-primary" href="{{route('product.create')}}">
                <i class="fa fa-plus"></i> Add Product
            </a>
            <a href="{{ route('export.products') }}" class="btn btn-success">
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </a>
        </div>

        <!-- Product Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    @if(session()->has('message'))
                        <div class="alert alert-success">
                            {{ session()->get('message') }}
                        </div>
                    @endif
                    <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Inventory List Records</h3>
                    <div class="tile-body">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Product Code</th>
                                    <th>Product</th>
                                    <th>Available Quantity</th>
                                    <th>Quantity on Hand</th>
                                    <th>Supplier Item Code</th>
                                    <th>Supplier</th>
                                    <th>Threshold</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($additional as $add)
                                    <tr>
                                        <td><span class="badge badge-info">{{ $add->product->product_code }}</span></td>
                                        <td>{{ $add->product->product_name }}</td>
                                        <td>{{ $add->product->quantity }}</td>
                                        <td>{{ $add->product->remaining_stock }}</td>
                                        <td>{{ $add->product->supplier_product_code }}</td>
                                        <td>{{ $add->supplier->name }}</td>
                                        <td>{{ $add->product->threshold }}</td>
                                        <td>
                                            @if ($add->product->status === 'In Stock')
                                                <span class="badge badge-success">{{ $add->product->status }}</span>
                                            @elseif ($add->product->status === 'Low Stock')
                                                <span class="badge badge-warning">{{ $add->product->status }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ $add->product->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <!-- View Button to Open Modal -->
                                                <button class="btn btn-info btn-sm view-btn"
                                                        data-toggle="modal"
                                                        data-target="#viewProductModal"
                                                        data-id="{{ $add->product->id }}">
                                                    <i class="fa fa-eye"></i>
                                                </button>

                                                <!-- Edit -->
                                                <a class="btn btn-primary btn-sm" href="{{ route('product.edit', $add->product->id) }}">
                                                    <i class="fa fa-edit"></i>
                                                </a>

                                                <!-- Delete -->
                                                <button class="btn btn-danger btn-sm" type="submit" onclick="deleteTag({{ $add->product->id }})">
                                                    <i class="fa fa-trash"></i>
                                                </button>

                                                <!-- Hidden Delete Form -->
                                                <form id="delete-form-{{ $add->product->id }}" 
                                                    action="{{ route('product.destroy', $add->product->id) }}" 
                                                    method="POST" style="display:none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details Modal -->
        <div class="modal fade" id="viewProductModal" tabindex="-1" role="dialog" aria-labelledby="viewProductLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="viewProductLabel">Product Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Full Product Details Will Be Loaded Here -->
                    <div id="product-details"></div>
                </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('js')
    <script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
    <script>
        $('#sampleTable').DataTable();

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
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    event.preventDefault();
                    document.getElementById('delete-form-'+id).submit();
                } else if (result.dismiss === swal.DismissReason.cancel) {
                    swal('Cancelled','Your data is safe :)','error');
                }
            })
        }
        
        $(document).on("click", ".view-btn", function () {
            let productId = $(this).data("id");
            $.ajax({
                url: "/product/" + productId,
                method: "GET",
                success: function (data) {
                    $("#product-details").html(data);
                },
                error: function () {
                    $("#product-details").html("<p class='text-danger'>Unable to load product details.</p>");
                }
            });
        });
    </script>
@endpush
