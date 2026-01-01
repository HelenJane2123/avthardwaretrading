@extends('layouts.master')

@section('title', 'Edit Product | ')

@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-edit"></i> Edit Inventory</h1>
            <p class="text-muted mb-0">Update the details of an existing product in your inventory.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Products</li>
            <li class="breadcrumb-item active">Edit</li>
        </ul>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
            <button type="button" class="close" data-dismiss="alert">×</button>
        </div>
    @endif

    <div class="mb-3">
        <a class="btn btn-outline-primary" href="{{ route('product.index') }}">
            <i class="fa fa-list"></i> Manage Products
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile shadow-sm rounded">
                <h3 class="tile-title border-bottom pb-2">Edit Product Form</h3>
                <div class="tile-body">
                    <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Basic Info -->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">Basic Information</h6>
                            </div>
                            <div class="card-body row">
                                <!-- LEFT SIDE IMAGE -->
                                <div class="col-md-3 text-center">
                                    @if($product->image)
                                        <img src="{{ asset('/images/product/' . $product->image) }}"
                                             class="img-thumbnail mb-2" 
                                             style="max-width: 200px; max-height: 200px;">
                                    @else
                                        <i class="fa fa-image fa-5x text-muted mb-2"></i>
                                    @endif
                                    <input type="file" name="image" class="form-control mt-2">
                                </div>

                                <!-- RIGHT SIDE FIELDS -->
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label class="fw-bold">Product Name</label>
                                            <div class="input-group">
                                                <input type="text" id="product_name" name="product_name" class="form-control form-control-sm" readonly value="{{ $product->product_name }}">
                                                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#productModal">Select</button>
                                                <!-- <div id="productSuggestions" class="list-group" style="display:none; position:absolute; z-index:1000;"></div> -->
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold">Product Code</label>
                                            <input type="text" class="form-control form-control-sm" value="{{ $product->product_code }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold">Supplier Product Code</label>
                                            <input type="text" class="form-control form-control-sm" name="supplier_product_code" 
                                                id="supplier_product_code" value="{{ $product->supplier_product_code }}" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                                <label class="control-label fw-bold">Product Description</label>
                                                <textarea name="description"
                                                    class="form-control form-control-sm"
                                                    rows="2">{{ $product->description }}</textarea>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label fw-bold">Volume Less</label>
                                            <textarea name="volume_less"
                                                class="form-control form-control-sm"
                                                rows="2">{{ $product->volume_less }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-2">
                                        <div class="form-group col-md-4">
                                            <label class="control-label fw-bold">Regular Less</label>
                                            <textarea name="regular_less"
                                                class="form-control form-control-sm"
                                                rows="2">{{ $product->regular_less }}</textarea>
                                        </div>
                                        <!-- <div class="col-md-3">
                                            <label>Serial Number</label>
                                            <input type="text" name="serial_number" class="form-control" value="{{ $product->serial_number }}">
                                        </div> -->
                                        <div class="col-md-3">
                                            <label>Category</label>
                                            <select name="category_id" class="form-control form-control-sm" required>
                                                <option value="">--Select--</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!-- <div class="col-md-3">
                                            <label>Model</label>
                                            <input type="text" name="model" class="form-control" value="{{ $product->model }}">
                                        </div> -->
                                        <div class="col-md-3">
                                            <label>Unit</label>
                                            <select name="unit_id" class="form-control form-control-sm">
                                                <option>--Select--</option>
                                                @foreach($units as $unit)
                                                    <option value="{{$unit->id}}" {{ $product->unit_id == $unit->id ? 'selected' : '' }}>
                                                        {{$unit->name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Section -->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">Stock Information</h6>
                            </div>
                            <div class="card-body row g-3">
                                <div class="col-md-2">
                                    <label>Initial Quantity</label>
                                    <input type="number" name="quantity" class="form-control form-control-sm" value="{{ $product->quantity }}">
                                </div>
                                <div class="col-md-2">
                                    <label>Remaining Stock</label>
                                    <input type="number" name="remaining_stock" class="form-control form-control-sm" value="{{ $product->remaining_stock }}" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label>Selling Price</label>
                                    <input type="number" step="0.01" name="sales_price" class="form-control form-control-sm" value="{{ $product->sales_price }}" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <label class="me-2">Status: </label>
                                    <span class="badge 
                                        @if($product->status == 'In Stock') bg-success
                                        @elseif($product->status == 'Low Stock') bg-warning
                                        @else bg-danger
                                        @endif">
                                        {{ $product->status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                         <!-- Supplier Section -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fa fa-truck"></i> Supplier Details</h6>
                                <small class="text-light">Selected supplier for the item will display the item's base price and net cost.</small>
                            </div>
                            <!-- <button type="button" class="btn btn-sm btn-success add_button">
                                <i class="fa fa-plus"></i> Add Supplier
                            </button> -->
                            <div class="card-body">
                                <div class="field_wrapper">
                                    @foreach ($product->productSuppliers as $ps)
                                        @php
                                            $si = $ps->supplierItem;
                                        @endphp

                                        <div class="row g-3 align-items-start">

                                            <div class="col-md-6">
                                                <label>Supplier</label>
                                                <input type="text" class="form-control form-control-sm"
                                                    value="{{ $ps->supplier->name }}" readonly>
                                            </div>

                                            <div class="col-md-3">
                                                <label>Base Price</label>
                                                <input type="number" class="form-control form-control-sm"
                                                    value="{{ $si->item_price ?? '' }}" readonly>
                                            </div>

                                            <div class="col-md-3">
                                                <label>Net Cost</label>
                                                <input type="number" class="form-control form-control-sm"
                                                    value="{{ $si->net_price ?? '' }}" readonly>

                                                @if($si && ($si->discount_less_add || $si->discount_1 || $si->discount_2 || $si->discount_3))
                                                    <div class="mt-1 small fw-semibold">
                                                        Discounts:
                                                        <span class="text-danger fw-bold">
                                                            {{ strtoupper($si->discount_less_add) }}
                                                        </span>
                                                        @foreach([$si->discount_1, $si->discount_2, $si->discount_3] as $d)
                                                            @if($d)
                                                                <span class="text-primary">{{ $d }}%</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fa fa-exchange-alt"></i> Discounts</h6>
                                <small class="text-light">These discounts will be applied to the invoice creation.</small>
                            </div>
                            <div class="card-body row g-3">
                                <div class="form-group col-md-2">
                                    <label class="control-label">Choose Discount Type</label>
                                    <select name="discount_type" class="form-control form-control-sm">
                                        <option value="less" {{ $product->discount_type == 'less' ? 'selected' : '' }}>Less (-)</option>
                                        <option value="add" {{ $product->discount_type == 'add' ? 'selected' : '' }}>Add (+)</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="control-label">Discount 1</label>
                                    <select name="discount_1" class="form-control form-control-sm">
                                        <option value="0">---Select Discount---</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{$tax->name}}" {{ $product->discount_1 == $tax->name ? 'selected' : '' }}>
                                                {{$tax->name}} %
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tax_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="control-label">Discount 2</label>
                                    <select name="discount_2" class="form-control form-control-sm">
                                        <option value="0">---Select Discount---</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{$tax->name}}" {{ $product->discount_2 == $tax->name ? 'selected' : '' }}>
                                                {{$tax->name}} %
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tax_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="control-label">Discount 2</label>
                                    <select name="discount_3" class="form-control form-control-sm">
                                        <option value="0">---Select Discount---</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{$tax->name}}" {{ $product->discount_3 == $tax->name ? 'selected' : '' }}>
                                                {{$tax->name}} %
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tax_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Adjustments</h5>
                            </div>
                            <div class="card-body">
                                <button type="button"
                                        class="btn btn-primary"
                                        data-toggle="modal"
                                        data-target="#addAdjustmentModal">
                                    <i class="fa fa-plus"></i> Add Adjustment
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="adjustmentTable">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Date</th>
                                                <th>Adjustment</th>
                                                <th>Status</th>
                                                <th>New Remaining Stock</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($product->adjustments as $adj)
                                                <tr>
                                                    <td>{{ $adj->created_at->format('M d, Y') }}</td>
                                                    <td>{{ $adj->adjustment }}</td>
                                                    <td>{{ $adj->adjustment_status }}</td>
                                                    <td>{{ $adj->new_initial_qty }}</td>
                                                    <td>{{ $adj->remarks }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- Submit -->
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fa fa-save"></i> Update Product
                            </button>
                            <a href="{{ route('product.index') }}" class="btn btn-secondary px-4">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
     <!-- Product Selection Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="productModalLabel">Select Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-hover" id="productTable">
                <thead>
                    <tr>
                    <th>Item Code</th>
                    <th>Description</th>
                    <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Product rows will be loaded here via AJAX -->
                </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
     <!-- Adjustment Modal -->
    <div class="modal fade" id="addAdjustmentModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="addAdjustmentForm"
                action="{{ route('product.adjustment.store', $product->id) }}"
                method="POST">
                @csrf

                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">Add Product Adjustment</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Adjustment Quantity</label>
                            <input type="number" name="adjustment" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="adjustment_status" class="form-control" required>
                                <option value="Return">Return</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection


@push('js')
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
<script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<script type="text/javascript">
    const suppliers = @json($suppliers);
    $(document).ready(function () {
        var maxField = 10;
        var addButton = $('.add_button');
        var wrapper = $('.field_wrapper');
        var x = {{ count($product->productSuppliers ?? []) }};

        // Function to generate supplier select options
        function generateSupplierOptions() {
            let options = '<option value="">Select Supplier</option>';
            suppliers.forEach(function (sup) {
                options += `<option value="${sup.id}">${sup.name}</option>`;
            });
            return options;
        }

        // Function to return new row HTML
        function getFieldHTML() {
            return `
                <div class="row mb-2">
                    <div class="col-md-5">
                        <select name="supplier_id[]" class="form-control" required>
                            ${generateSupplierOptions()}
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="supplier_price[]" class="form-control" placeholder="Purchase Price" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <button type="button" class="btn btn-danger remove-supplier">Delete</button>
                    </div>
                </div>
            `;
        }

        // Add new supplier row
        $(addButton).click(function () {
            if (x < maxField) {
                x++;
                $(wrapper).append(getFieldHTML());
            }
        });

        // Remove supplier row
        $(wrapper).on('click', '.remove-supplier', function (e) {
            e.preventDefault();
            $(this).closest('.row').remove();
            x--;
        });
       
       // Select inputs using jQuery
        const $initialStockInput = $('input[name="quantity"]');
        const $remainingStockInput = $('input[name="remaining_stock"]');

        // Function to update New Initial Qty for a row
        function updateRowNewQty($row) {
            const remainingStock = parseFloat($remainingStockInput.val()) || 0;
            const adjustment = parseFloat($row.find('.adjustment').val()) || 0;
            const newInitialQty = remainingStock + adjustment;
            $row.find('.new-initial-qty').val(newInitialQty);
        }

        // Initialize all adjustment rows on page load
        $('#adjustmentTable tbody tr').each(function() {
            updateRowNewQty($(this));
        });

        // When initial quantity changes
        $initialStockInput.on('input', function() {
            const initialVal = parseFloat($(this).val()) || 0;
            $remainingStockInput.val(initialVal);

            // Update all adjustment rows
            $('#adjustmentTable tbody tr').each(function() {
                updateRowNewQty($(this));
            });

            // Update product status badge
            const threshold = initialVal <= 10 ? 1 : Math.floor(initialVal * 0.2);
            let status = 'In Stock';
            if (initialVal === 0) status = 'Out of Stock';
            else if (initialVal <= threshold) status = 'Low Stock';

            const $badge = $('.badge');
            $badge.text(status).removeClass('bg-success bg-warning bg-danger');

            if (status === 'In Stock') $badge.addClass('bg-success');
            else if (status === 'Low Stock') $badge.addClass('bg-warning');
            else if (status === 'Out of Stock') $badge.addClass('bg-danger');
        });

        // When adjustment input changes
        $('#adjustmentTable').on('input', '.adjustment', function() {
            const $row = $(this).closest('tr');
            updateRowNewQty($row);
        });

        // initialize on page load
        //updateRemainingStock();
        
        // Product suggestions (autocomplete)
        $("#product_name").on("keyup", function () {
            let query = $(this).val();
            if (query.length > 1) {
                $.ajax({
                    url: "{{ route('products.suggest') }}",
                    type: "GET",
                    data: { query: query },
                    dataType: "json",
                    success: function (data) {
                        console.log("RAW DATA:", data);
                        console.log("IS ARRAY:", Array.isArray(data));

                        let suggestions = "";
                        let list = Array.isArray(data) ? data : data.items;

                        if (!list) {
                            console.error("No array found in response:", data);
                            return;
                        }

                        list.forEach(function (item) {
                            suggestions += `<a href="#" class="list-group-item list-group-item-action product-suggestion"
                                                data-code="${item.item_code}" 
                                                data-name="${item.item_description}">
                                                ${item.item_code} - ${item.item_description}
                                            </a>`;
                        });

                        $("#productSuggestions").html(suggestions).show();
                    },
                    error: function (xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                    }
                });
            } else {
                $("#productSuggestions").hide();
            }
        });

        $(document).on("click", ".product-suggestion", function (e) {
            e.preventDefault();

            let code = $(this).data("code");
            let name = $(this).data("name");

            $("#product_name").val(name);
            $("#supplier_product_code").val(code);
            $("#productSuggestions").hide();

            $.ajax({
                url: "{{ route('products.suppliers') }}",
                type: "GET",
                data: { item_code: code },
                success: function (data) {
                    console.log("Suppliers:", data); // 🔹 Check in console

                    if (Array.isArray(data) && data.length > 0) {
                        let supplier = data[0]; // just take first one
                        $("#supplier_id").val(supplier.id);
                        $("#supplier_name").val(supplier.name);
                        $("#supplier_price").val(supplier.item_price);
                    } else {
                        alert("No suppliers found for this product");
                    }
                },
                error: function (xhr) {
                    console.error("Supplier AJAX Error:", xhr.responseText);
                }
            });
        });

        //modal will open when searching for product
        $('#productModal').on('show.bs.modal', function () {
            $.ajax({
                url: "{{ route('products.list') }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    console.log("Products loaded:", data);
                    let tbody = '';
                    data.forEach(function(item) {
                        tbody += `
                            <tr>
                                <td>${item.item_code}</td>
                                <td>${item.item_description}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary select-product"
                                        data-item='${JSON.stringify(item)}'>Select</button>
                                </td>
                            </tr>`;
                    });
                    $('#productTable tbody').html(tbody);

                    // Initialize DataTable or destroy first if already exists
                    if ($.fn.DataTable.isDataTable('#productTable')) {
                        $('#productTable').DataTable().destroy();
                    }

                    $('#productTable').DataTable({
                        pageLength: 10, // show 10 rows per page
                        lengthChange: false, // hide rows per page dropdown
                    });
                },
                error: function(xhr) {
                    console.error('Error loading products:', xhr.responseText);
                }
            });
        });

        // Handle selecting a product from the modal
        $(document).on('click', '.select-product', function() {
            let item = $(this).data('item');
            $('#product_name').val(item.item_description);
            //$('#product_code').val(item.item_code);
            $('#supplier_product_code').val(item.item_code); // optional
                $.ajax({
                url: "{{ route('products.suppliers') }}",
                type: "GET",
                data: { item_code: item.item_code },
                success: function (data) {
                    if (Array.isArray(data) && data.length > 0) {
                        let supplier = data[0];
                        $("#supplier_id").val(supplier.id);
                        $("#supplier_name").val(supplier.name);
                        $("#supplier_price").val(supplier.item_price);
                        $("#net_cost").val(supplier.net_price);

                        console.log("supplier name",supplier.name);
                        // Change PL → AV in product name only if supplier is 1st Tool Trading Inc
                        if (supplier.name.trim().toLowerCase().includes("1st tool")) {
                            let currentProductText = $("#product_name").val();
                            let updatedProductText = currentProductText.replace(/^PL/i, "AV");
                            console.log("updated product name",updatedProductText)
                            $("#product_name").val(updatedProductText);
                        }

                        const discounts = [];
                        if (supplier.discount_less_add) discounts.push(supplier.discount_less_add);
                        if (supplier.discount_1) discounts.push(supplier.discount_1 + '% ' );
                        if (supplier.discount_2) discounts.push(supplier.discount_2 + '% ' );
                        if (supplier.discount_3) discounts.push(supplier.discount_3 + '% ' );

                        updateDiscountDescription(discounts);
                    } else {
                        alert("No suppliers found for this product");
                    }
                },
                error: function (xhr) {
                    console.error("Supplier AJAX Error:", xhr.responseText);
                }
            });
            $('#productModal').modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        });

        function updateDiscountDescription(discounts) {
            const desc = document.getElementById('discountDescription');
            if (discounts && discounts.length > 0) {
                // Highlight "LESS" or "ADD" in uppercase and discount percentages
                const formatted = discounts.map(d => {
                    if (d.toLowerCase().startsWith('less')) {
                        return '<span style="text-transform:uppercase; color:#d9534f;">' + d + '</span>';
                    } else if (d.toLowerCase().startsWith('add')) {
                        return '<span style="text-transform:uppercase; color:#5cb85c;">' + d + '</span>';
                    } else {
                        return '<span style="color:#0275d8;">' + d + '</span>';
                    }
                }).join(', ');
                desc.innerHTML = 'Discounts: ' + formatted;
                desc.style.display = 'block';
            } else {
                desc.style.display = 'none';
            }
        }
        const adjustmentTable = $('#adjustmentTable').DataTable();

        // Reset form when modal opens
        $('#addAdjustmentModal').on('show.bs.modal', function () {
            $('#addAdjustmentForm')[0].reset();
        });

        $('#addAdjustmentModal').on('hidden.bs.modal', function () {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });

        // Submit adjustment via AJAX
        $('#addAdjustmentForm').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const url = form.attr('action');

            $.ajax({
                type: 'POST',
                url: url,
                data: form.serialize(),
                success: function (response) {
                    const adj = response.adjustment;

                    // Close modal
                    $('#addAdjustmentModal').modal('hide');

                    // Add row to DataTable
                    adjustmentTable.row.add([
                        adj.created_at,
                        adj.adjustment,
                        adj.adjustment_status,
                        adj.new_initial_qty,
                        adj.remarks ?? ''
                    ]).draw(false);

                    // Update remaining stock field
                    $('input[name="remaining_stock"]').val(adj.new_initial_qty);

                    Swal.fire({
                        icon: 'success',
                        title: 'Adjustment Added',
                        text: 'The stock adjustment was saved successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Something went wrong while saving the adjustment.',
                    });
                }
            });
        });
    });
</script>
@endpush
