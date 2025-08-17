

@extends('layouts.master')

@section('titel', 'Product | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Product Table</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Product</li>
                <li class="breadcrumb-item active"><a href="#">Product Table</a></li>
            </ul>
        </div>
        <div class="">
            <a class="btn btn-primary" href="{{route('product.create')}}"><i class="fa fa-plus"></i> Add Product</a>
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
                            <a href="{{ route('export.products') }}" class="btn btn-success mb-2">
                                <i class="fa fa-download"></i> Export to Excel
                            </a>
                        </div>  
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th>Product Code</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Supplier Item Code</th>
                                    <th>Supplier</th>
                                    <th>Threshold</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($additional as $add)
                                    <tr>
                                        <td>{{ $add->product->product_code }}</td>
                                        <td>{{ $add->product->product_name }}</td>
                                        <td>{{ $add->product->quantity }}</td>
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
                                        <td>
                                            <!-- View Button to Open Modal -->
                                            <button class="btn btn-info btn-sm view-btn"
                                                    data-toggle="modal"
                                                    data-target="#viewProductModal"
                                                    data-id="{{ $add->product->id }}">
                                                <i class="fa fa-eye"></i>
                                            </button>

                                            <!-- Edit & Delete -->
                                            <a class="btn btn-primary btn-sm" href="{{ route('product.edit', $add->product->id) }}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <button class="btn btn-danger btn-sm" type="submit" onclick="deleteTag({{ $add->product->id }})">
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
        <!-- Product Details Modal -->
        <!-- View Product Modal -->
        <div class="modal fade" id="viewProductModal" tabindex="-1" role="dialog" aria-labelledby="viewProductLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewProductLabel">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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
        
        $(document).on("click", ".view-btn", function () {
            let productId = $(this).data("id");

            $.ajax({
                url: "/product/" + productId, // Create route to fetch product
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
