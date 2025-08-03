@extends('layouts.master')

@section('title', 'Product | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i>Edit Product</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Product</li>
                <li class="breadcrumb-item"><a href="#">Edit Product</a></li>
            </ul>
        </div>

        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

        <div>
            <a class="btn btn-primary" href="{{ route('product.index') }}">
                <i class="fa fa-edit"></i> Manage Products
            </a>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Edit Product Form</h3>
                    <div class="tile-body">
                        <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Basic Info -->
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Product Code</label>
                                    <input type="text" class="form-control" value="{{ $product->product_code }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label>Serial Number</label>
                                    <input type="text" name="serial_number" class="form-control" value="{{ $product->serial_number }}">
                                </div>
                                <div class="col-md-3">
                                    <label>Category</label>
                                    <select name="category_id" class="form-control" required>
                                        <option value="">---Select Category---</option>
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
                            </div>

                            <!-- Stock and Pricing -->
                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <label>Product</label>
                                    <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                                </div>
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
                            </div>

                            <!-- More Details -->
                            <div class="row mt-3">
                                <div class="col-md-2">
                                    <label>Unit</label>
                                    <select name="unit_id" class="form-control">
                                        <option value="">---Select Unit---</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}" {{ $product->unit_id == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Discount</label>
                                    <select name="tax_id" class="form-control">
                                        <option value="">---Select Discount---</option>
                                        @foreach ($taxes as $tax)
                                            <option value="{{ $tax->id }}" {{ $product->tax_id == $tax->id ? 'selected' : '' }}>
                                                {{ $tax->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Threshold</label>
                                    <input type="number" class="form-control" value="{{ $product->threshold }}" readonly>
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <label class="mr-2">Status: </label>
                                    <span class="badge 
                                        @if($product->status == 'In Stock') badge-success
                                        @elseif($product->status == 'Low Stock') badge-warning
                                        @else badge-danger
                                        @endif">
                                        {{ $product->status }}
                                    </span>
                                </div>                                      
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-2">
                                    <label>Image</label>
                                    <input type="file" name="image" class="form-control">
                                    @if($product->image)
                                        <img src="{{ asset('/images/product/' . $product->image) }}" width="80" class="mt-2">
                                    @endif
                                </div>
                            </div>
                            <!-- Supplier Section -->
                            <div class="card mt-4">
                                <div class="field_wrapper">
                                    @if ($product->productSuppliers && count($product->productSuppliers) > 0)
                                        @foreach ($product->productSuppliers as $supplier)
                                            <div class="row mb-2">
                                                <div class="col-md-5">
                                                    <select name="supplier_id[]" class="form-control" required>
                                                        <option value="">Select Supplier</option>
                                                        @foreach ($suppliers as $sup)
                                                            <option value="{{ $sup->id }}" {{ $supplier->supplier_id == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="number" name="supplier_price[]" class="form-control" placeholder="Purchase Price" value="{{ $supplier->price }}" required>
                                                </div>
                                                <div class="col-md-2 d-flex align-items-center">
                                                    <button type="button" class="btn btn-danger remove-supplier">Delete</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <button type="button" class="btn btn-sm btn-primary add_button">Add Supplier</button>
                            </div>

                            <button type="submit" class="btn btn-primary mt-4">Update Product</button>
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
    });
</script>
@endpush
