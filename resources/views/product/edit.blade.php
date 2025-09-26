@extends('layouts.master')

@section('title', 'Edit Product | ')

@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-edit"></i> Edit Product</h1>
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
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">Basic Information</h5>
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
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="fw-bold">Product Name</label>
                                            <input type="text" name="product_name" class="form-control" id="product_name" 
                                                value="{{ $product->product_name }}" required>
                                            <div id="productSuggestions" class="list-group position-absolute w-100 shadow" style="display:none; z-index:1000;"></div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold">Product Code</label>
                                            <input type="text" class="form-control" value="{{ $product->product_code }}" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold">Supplier Product Code</label>
                                            <input type="text" class="form-control" name="supplier_product_code" 
                                                id="supplier_product_code" value="{{ $product->supplier_product_code }}" readonly>
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-2">
                                        <div class="col-md-3">
                                            <label>Serial Number</label>
                                            <input type="text" name="serial_number" class="form-control" value="{{ $product->serial_number }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Category</label>
                                            <select name="category_id" class="form-control" required>
                                                <option value="">--Select--</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>Model</label>
                                            <input type="text" name="model" class="form-control" value="{{ $product->model }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Unit</label>
                                            <select name="unit_id" class="form-control">
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
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">Stock Information</h5>
                            </div>
                            <div class="card-body row g-3">
                                <div class="col-md-3">
                                    <label>Initial Quantity</label>
                                    <input type="number" name="quantity" class="form-control" value="{{ $product->quantity }}">
                                </div>
                                <div class="col-md-3">
                                    <label>Remaining Stock</label>
                                    <input type="number" name="remaining_stock" class="form-control" value="{{ $product->remaining_stock }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label>Selling Price</label>
                                    <input type="number" step="0.01" name="sales_price" class="form-control" value="{{ $product->sales_price }}" required>
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
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Supplier Details</h5>
                                <button type="button" class="btn btn-sm btn-success add_button">
                                    <i class="fa fa-plus"></i> Add Supplier
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="field_wrapper">
                                    @if ($product->productSuppliers && count($product->productSuppliers) > 0)
                                        @foreach ($product->productSuppliers as $supplierItem)
                                            <div class="row mb-2">
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" value="{{ $supplierItem->supplier->name ?? '' }}" readonly>
                                                    <input type="hidden" name="supplier_id[]" value="{{ $supplierItem->supplier_id }}">
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="number" name="supplier_price[]" class="form-control" 
                                                        placeholder="Purchase Price" value="{{ $supplierItem->price }}" required>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-center">
                                                    <button type="button" class="btn btn-danger remove-supplier">Delete</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
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
</main>
@endsection


@push('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
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
        //Update status of product based on qty
         $('input[name="quantity"]').on('input', function(){
            const qty = parseInt($(this).val());
            const threshold = qty <= 10 ? 1 : Math.floor(qty * 0.2);
            $('input[readonly][value="{{ $product->threshold }}"]').val(threshold); // optional live display

            let status = 'In Stock';
            if (qty === 0) status = 'Out of Stock';
            else if (qty <= threshold) status = 'Low Stock';

            const badge = $('.badge');
            badge.text(status);
            badge.removeClass('badge-success badge-warning badge-danger');

            if(status === 'In Stock') badge.addClass('badge-success');
            if(status === 'Low Stock') badge.addClass('badge-warning');
            if(status === 'Out of Stock') badge.addClass('badge-danger');
        });

        const initialStockInput = document.querySelector('input[name="quantity"]');
        const remainingStockInput = document.querySelector('input[name="remaining_stock"]');

        if (initialStockInput && remainingStockInput) {
            initialStockInput.addEventListener('input', function () {
                remainingStockInput.value = this.value;
            });
        }

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
    });
</script>
@endpush
