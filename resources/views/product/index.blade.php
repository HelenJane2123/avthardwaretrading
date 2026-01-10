@extends('layouts.master')

@section('titel', 'Product | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <!-- Page Title -->
        <div class="app-title d-flex justify-content-between align-items-center">
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
            <a class="btn btn-sm btn-primary" href="{{route('product.create')}}">
                <i class="fa fa-plus"></i> Add Product
            </a>
            <a href="{{ route('export.products') }}" class="btn btn-sm btn-success">
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
        <!-- Product Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Inventory List Records</h3>
                    <div class="tile-body">
                        <div class="d-flex justify-content-end align-items-center mb-3 flex-wrap gap-2">
                            <!-- Filter by Supplier -->
                            <div class="mr-2">
                                <label for="filterSupplier" class="me-2 mb-0">Filter by Supplier:</label>
                                <select id="filterSupplier" class="form-control form-control-sm d-inline-block w-auto">
                                    <option value="">All Suppliers</option>
                                </select>
                            </div>

                            <!-- Filter by Product Status -->
                            <div class="mr-2">
                                <label for="filterProdStatus" class="me-2 mb-0">Filter by Product Status:</label>
                                <select id="filterProdStatus" class="form-control form-control-sm d-inline-block w-auto">
                                    <option value="">All Status</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="productTable">
                                <thead class="thead-dark medium">
                                    <tr>
                                        <th>Product Code</th>
                                        <th>Product</th>
                                        <th>Initial Quantity</th>
                                        <th>Quantity on Hand</th>
                                        <th>Sales Price</th>
                                        <th>Supplier Item Code</th>
                                        <th>Supplier</th>
                                        <th>Threshold</th>
                                        <th>Status</th>
                                        <th>Date Created</th>
                                        <th>Date Updated</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="medium">
                                    @foreach($additional as $add)
                                        <tr id="row-{{ $add->product->id }}">
                                            <td><span class="badge badge-info">{{ $add->product->product_code }}</span></td>
                                            <td>{{ $add->product->product_name }}</td>
                                            <td>{{ $add->product->quantity }}</td>
                                            <td>{{ $add->product->remaining_stock }}</td>
                                            <td>{{ $add->product->sales_price }}</td>
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
                                            <td>{{ $add->product->created_at->format('F d, Y') }}</td>
                                            <td>{{ $add->product->updated_at->format('F d, Y') }}</td>
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
                                                    <button class="btn btn-danger btn-sm" type="button" 
                                                            onclick="deleteTag('{{ $add->product->id }}', '{{ $add->product->product_name }}')">
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
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script>
        var invoiceTable = $('#productTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 10,
            "responsive": true
        });

        // Populate Supplier dropdown from table data
        var supplierColumnIndex = 6; // Supplier column
        var suppliers = invoiceTable.column(supplierColumnIndex).data().unique().sort();

        suppliers.each(function(d) {
            $('#filterSupplier').append('<option value="' + d + '">' + d + '</option>');
        });

        // Filter table based on selection
        $('#filterSupplier').on('change', function() {
            var val = $(this).val();
            invoiceTable.column(supplierColumnIndex).search(val ? '^' + val + '$' : '', true, false).draw();
        });
        $('#filterSupplier').on('change', function() {
            var supplierId = $(this).val();
            table.column(0).search(supplierId).draw();
        });

        // Populate Product Status dropdown from table data
        var statusColumnIndex = 8; // Status column
        var statuses = invoiceTable.column(statusColumnIndex).data().unique().sort();

        statuses.each(function(d) {
            // Strip HTML and get only the text inside the span
            var cleanStatus = $('<div>').html(d).text();
            $('#filterProdStatus').append('<option value="' + cleanStatus + '">' + cleanStatus + '</option>');
        });

        // Filter table based on selection
        $('#filterProdStatus').on('change', function() {
            var val = $(this).val();
            // Use regex search to match the text inside the span
            invoiceTable.column(statusColumnIndex)
                .search(val ? '^' + val + '$' : '', true, false)
                .draw();
        });


        function deleteTag(productId, productName) {
            Swal.fire({
                title: 'Delete "' + productName + '"?',
                text: "This action cannot be undone!",
                type: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(function(result) {
                if (result.value) { 
                    fetch('{{ route("product.destroy", ":id") }}'.replace(':id', productId), {
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
