@extends('layouts.master')

@section('title', 'Add Product | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-plus"></i> Add New Product</h1>
                <p class="text-muted mb-0">Create a new product and manage its details in your inventory.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Product</li>
                <li class="breadcrumb-item"><a href="#">Add Products</a></li>
            </ul>
        </div>

         @if(session()->has('message'))
            <div class="alert alert-success shadow-sm">
                <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm">
                <strong>Whoops!</strong> There were some problems with your input.
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

       
        <div class="mb-3">
            <a class="btn btn-sm btn-outline-primary" href="{{route('product.index')}}">
                <i class="fa fa-list"></i> Manage Products
            </a>
        </div>
        <div class="row mt-2">
            <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    @if(auth()->user()->user_role === 'super_admin')
                        <hr/>
                        <div class="col-md-12">
                            <div class="tile-body">
                                <h3 class="tile-title">Import Bulk Products</h3>
                                <small class="text-muted">Use this field only to import bulk product items and product supplier details</small>
                                <form action="{{ route('import.product') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <label>Select Excel File:</label>
                                    <input type="file" name="file" class="form-control" required><br/>
                                    <button type="submit" class="btn btn-primary">Import</button>
                                </form>
                            </div>
                        </div>
                        <hr/>
                    @endif
                    <h3 class="tile-title">
                        Product
                        <small class="text-muted d-block" style="font-size: 0.8rem;">
                            All fields marked with <span class="text-danger text-sm">*</span> are required.
                        </small>
                    </h3>
                    
                    <div class="tile-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{route('product.store')}}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <!-- Left Column: Image -->
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label">Image</label>
                                        <i class="fa fa-image fa-5x text-muted mb-2"></i>
                                        <div class="form-group mt-2">
                                            <label>Change Image</label>
                                            <input type="file" name="image" class="form-control form-control-sm @error('image') is-invalid @enderror">
                                        </div>
                                        <!-- <input name="image" class="form-control @error('image') is-invalid @enderror" type="file"> -->
                                        @error('image')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror

                                        {{-- Show current image if exists --}}
                                        @if(isset($product) && $product->image)
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/'.$product->image) }}" 
                                                    alt="Product Image" 
                                                    class="img-thumbnail" 
                                                    style="max-width: 100%; height: auto;">
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Right Column: Product Details -->
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="form-group col-md-8 position-relative">
                                            <label class="control-label">Product <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" id="product_name" name="product_name" class="form-control form-control-sm" readonly placeholder="Select product...">
                                                <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#productModal">Select</button>
                                                <!-- <div id="productSuggestions" class="list-group" style="display:none; position:absolute; z-index:1000;"></div> -->
                                            </div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label">Product Code</label>
                                            <input name="product_code" id="product_code" class="form-control form-control-sm" type="text" value="{{ $product_code }}" readonly>
                                            <!-- <input name="product_code" id="product_code" class="form-control" type="text" readonly> -->
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label">Supplier Product Code</label>
                                            <input name="supplier_product_code" id="supplier_product_code" class="form-control form-control-sm" type="text" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label fw-bold">Product Description</label>
                                            <textarea name="description"
                                                class="form-control form-control-sm"
                                                rows="2"></textarea>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label fw-bold">Volume Less</label>
                                            <textarea name="volume_less"
                                                class="form-control form-control-sm"
                                                rows="2"></textarea>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label fw-bold">Regular Less</label>
                                            <textarea name="regular_less"
                                                class="form-control form-control-sm"
                                                rows="2"></textarea>
                                        </div>
                                        <!-- <div class="form-group col-md-4">
                                            <label class="control-label">Serial Number</label>
                                            <input name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" type="number" placeholder="Enter Serial Number">
                                            @error('serial_number')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div> -->

                                        <div class="form-group col-md-4">
                                            <label class="control-label">Category <span class="text-danger">*</span></label>
                                            <select name="category_id" id="category_id" class="form-control form-control-sm">
                                                <option>---Select Category---</option>
                                                @foreach($categories as $category)
                                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <!-- <div class="form-group col-md-4">
                                            <label class="control-label">Model</label>
                                            <input name="model" class="form-control @error('model') is-invalid @enderror" type="text" placeholder="Enter Model">
                                            @error('model')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div> -->

                                        <div class="form-group col-md-4">
                                            <label class="control-label">Initial Quantity <span class="text-danger">*</span></label>
                                            <input name="quantity" class="form-control form-control-sm" type="number" min="0" placeholder="Enter Initial Stock">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label class="control-label">Remaining Stock</label>
                                            <input name="remaining_stock" id="remaining_stock" class="form-control form-control-sm" type="number" readonly>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label class="control-label">Selling Price <span class="text-danger">*</span></label>
                                            <input name="sales_price" step="0.01" id="sales_price" class="form-control form-control-sm @error('sales_price') is-invalid @enderror" type="number" placeholder="Enter Selling Price">
                                            @error('sales_price')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label">Unit <span class="text-danger">*</span></label>
                                            <select name="unit_id" id="unit_id" class="form-control form-control-sm">
                                                <option value="">---Select Unit---</option>
                                                @foreach($units as $unit)
                                                    <option value="{{$unit->id}}">{{$unit->name}}</option>
                                                @endforeach
                                            </select>
                                            @error('unit_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Supplier Section --}}
                            <div class="card mt-4">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fa fa-truck"></i> Supplier Details</h6>
                                    <small class="text-light">Selected supplier for the item will display the item's base price and net cost.</small>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 align-items-start">
                                        <!-- Supplier -->
                                        <div class="col-md-6">
                                            <label for="supplier_name" class="form-label">Supplier</label>
                                            <input type="text" id="supplier_name" name="supplier_name"
                                                class="form-control form-control-sm" autocomplete="off">
                                            <div id="supplierSuggestions" class="list-group shadow-sm"></div>
                                            <input type="hidden" id="supplier_id" name="supplier_id[]">
                                        </div>

                                        <!-- Base Price -->
                                        <div class="col-md-3">
                                            <label for="supplier_price" class="form-label">Base Price (Unit Cost)</label>
                                            <input type="number" id="supplier_price" name="supplier_price[]"
                                                class="form-control form-control-sm" readonly>
                                        </div>

                                        <!-- Net Cost + Discount -->
                                        <div class="col-md-3">
                                            <label for="net_cost" class="form-label">Net Cost</label>
                                            <input type="number" id="net_cost" name="net_price[]"
                                                class="form-control form-control-sm" readonly>

                                            <!-- Discount description -->
                                            <div id="discountDescription"
                                                class="mt-1 small fw-semibold"
                                                style="display:none;">
                                                <!-- Injected dynamically -->
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            {{-- Discount Section --}}
                            <div class="card mt-4">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fa fa-exchange-alt"></i> Discounts</h6>
                                    <small class="text-light">These discounts will be applied to the invoice creation.</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-md-2">
                                            <label class="control-label">Choose Discount Type</label>
                                            <select name="discount_type" class="form-control form-control-sm">
                                                <option value="less">Less (-)</option>
                                                <option value="add">Add (+)</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label class="control-label">Discount 1</label>
                                            <select name="discount_1" class="form-control form-control-sm">
                                                <option value="0">---Select Discount---</option>
                                                @foreach($taxes as $tax)
                                                    <option value="{{$tax->name}}">{{$tax->name}} %</option>
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
                                                    <option value="{{$tax->name}}">{{$tax->name}} %</option>
                                                @endforeach
                                            </select>
                                            @error('tax_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label class="control-label">Discount 3</label>
                                            <select name="discount_3" class="form-control form-control-sm">
                                                <option value="0">---Select Discount---</option>
                                                @foreach($taxes as $tax)
                                                    <option value="{{$tax->name}}">{{$tax->name}} %</option>
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
                            </div>
                            {{-- Adjustment Section --}}
                            <!-- <div class="card mt-4">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fa fa-exchange-alt"></i> Adjustments</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-sm" id="adjustmentTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Adjustment</th>
                                                <th>Adjustment Status</th>
                                                <th>Remarks</th>
                                                <th>New Initial Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input type="number" name="adjustment[]" class="form-control form-control-sm adjustment" value="0" min="0">
                                                </td>
                                                <td>
                                                    <select name="adjustment_status[]" class="form-control form-control-sm">
                                                        <option value="Return">Return</option>
                                                        <option value="Others">Others</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="adjustment_remarks[]" class="form-control form-control-sm" placeholder="Enter remarks">
                                                </td>
                                                <td>
                                                    <input type="number" name="new_initial_qty[]" class="form-control  form-control-sm new-initial-qty" readonly>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div> -->
                            <div class="form-group col-md-4 mt-4 align-self-end">
                                <button class="btn btn-sm btn-success" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Add Product</button>
                                <a href="{{ route('product.index') }}" class="btn btn-sm btn-secondary px-4">
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
     </main>
@endsection
@push('js')
    <script src="{{ asset('/') }}js/plugins/jquery.dataTables.min.js"></script>
    <script src="{{ asset('/') }}js/plugins/dataTables.bootstrap.min.js"></script>
    <script src="{{asset('/')}}js/multifield/jquery.multifield.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#category_id').select2({
                placeholder: "Select Category",
                allowClear: true,
                width: '280px'
            });
            $('#unit_id').select2({
                placeholder: "Select Unit",
                allowClear: true,
                width: '280px'
            });

            var maxField = 10; //Input fields increment limitation
            var addButton = $('.add_button'); //Add button selector
            var wrapper = $('.field_wrapper'); //Input field wrapper
            var fieldHTML = '<div><select name="supplier_id[]" class="form-control"><option class="form-control">Select Supplier</option>@foreach($suppliers as $supplier)<option value="{{$supplier->id}}">{{$supplier->name}}</option>@endforeach</select><input name="supplier_price[]" class="form-control" type="text" placeholder="Enter Sales Price"><a href="javascript:void(0);" class="remove_button btn btn-danger" title="Delete field"><i class="fa fa-minus"></i></a></div>'
            var x = 1; //Initial field counter is 1

            //Once add button is clicked
            $(addButton).click(function(){
                //Check maximum number of input fields
                if(x < maxField){
                    x++; //Increment field counter
                    $(wrapper).append(fieldHTML); //Add field html
                }
            });

            //Once remove button is clicked
            $(wrapper).on('click', '.remove_button', function(e){
                e.preventDefault();
                $(this).parent('div').remove(); //Remove field html
                x--; //Decrement field counter
            });

            $('#example-2').multifield({
                section: '.group',
                btnAdd:'#btnAdd-2',
                btnRemove:'.btnRemove'
            });

            // Product suggestions (autocomplete)
            $("#product_name").on("keyup", function () {
                let query = $(this).val().trim();

                if (query.length > 1) {
                    $.ajax({
                        url: "{{ route('products.suggest') }}",
                        type: "GET",
                        data: { query: query },
                        dataType: "json",
                        success: function (data) {
                            let list = Array.isArray(data) ? data : data.items;
                            let suggestions = "";

                            if (list && list.length > 0) {
                                list.forEach(function (item) {
                                    suggestions += `
                                        <div class="list-group-item list-group-item-action product-suggestion"
                                            data-item='${JSON.stringify(item)}'
                                            style="cursor:pointer;">
                                            ${item.item_description}
                                        </div>`;
                                });
                                $("#productSuggestions").html(suggestions).show();
                            } else {
                                $("#productSuggestions").hide();
                            }
                        },
                        error: function (xhr) {
                            console.error("AJAX Error:", xhr.responseText);
                        }
                    });
                } else {
                    $("#productSuggestions").hide();
                }
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
                            $("#sales_price").val(supplier.item_price);
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

            $(document).on("click", ".product-suggestion", function (e) {
                e.preventDefault();
                e.stopPropagation();

                let item = $(this).data("item");

                $("#product_name").val(item.item_description);
                $("#supplier_product_code").val(item.item_code);
                $("#productSuggestions").fadeOut(150);

                // Fetch supplier info
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

                            console.log("supplier name",supplier.name);
                            // Change PL → AV in product name only if supplier is 1st Tool Trading Inc
                            if (supplier.name.trim().toLowerCase().includes("1st tool")) {
                                let currentProductText = $("#product_name").val();
                                let updatedProductText = currentProductText.replace(/^PL/i, "AV");
                                console.log("updated product name",updatedProductText)
                                $("#product_name").val(updatedProductText);
                            }
                        } else {
                            alert("No suppliers found for this product");
                        }
                    },
                    error: function (xhr) {
                        console.error("Supplier AJAX Error:", xhr.responseText);
                    }
                });
            });
            $(document).on("click", function (e) {
                if (!$(e.target).closest("#product_name, #productSuggestions").length) {
                    $("#productSuggestions").fadeOut(150);
                }
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

            //--- start this should be in invoice
            // Function to calculate selling price after discounts 
            //const salesPriceInput = $('input[name="sales_price"]');

            // Store the original price as the base for discount calculation
            // function storeOriginalPrice() {
            //     if (!salesPriceInput.data('original')) {
            //         let val = parseFloat(salesPriceInput.val()) || 0;
            //         salesPriceInput.data('original', val);
            //     }
            // }

            // Apply compound discounts
            // function applyDiscounts() {
            //     // Keep original price
            //     storeOriginalPrice();
            //     let price = parseFloat(salesPriceInput.data('original')) || 0;

            //     // Only one discount type for all 3 discounts
            //     let type = $('select[name="discount_type"]').val(); // less or add

            //     // Apply 3 sequential discounts
            //     for (let i = 1; i <= 3; i++) {
            //         let d = parseFloat($(`select[name="discount_${i}"]`).val()) || 0;

            //         if (d > 0) {
            //             if (type === "less") {
            //                 price = price * (1 - d / 100);
            //             } else {
            //                 price = price * (1 + d / 100);
            //             }
            //         }
            //     }

            //     // Update sales price
            //     salesPriceInput.val(price.toFixed(2));
            // }

            
            // Trigger calculation when the user changes the price or selects discounts
            // salesPriceInput.on('input', function() {
            //     // Reset original price if user manually edits
            //     salesPriceInput.data('original', parseFloat(salesPriceInput.val()) || 0);
            // });

            // $('select[name="discount_1"], select[name="discount_2"], select[name="discount_3"]').on('change', applyDiscounts);
            //--end invoice price computation
        });
        // function generateProductCode() {
        //     const prefix = "AVT";
        //     const timestamp = Date.now().toString().slice(-6); // last 6 digits of timestamp
        //     const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        //     return prefix + timestamp + random;
        // }

        // Set it when the page loads
       // $('#product_code').val(generateProductCode());

         // Initial stock & remaining stock inputs
        const initialStockInput = $('input[name="quantity"]');
        const remainingStockInput = $('input[name="remaining_stock"]');

        // Auto-update remaining stock when initial changes
        initialStockInput.on('input', function() {
            remainingStockInput.val($(this).val());
            updateAllNewQty();
        });

        // Auto-update New Initial Qty when adjustment changes
        $('#adjustmentTable').on('input', '.adjustment', function() {
            updateRowNewQty($(this).closest('tr'));
        });

        function updateRowNewQty(row) {
            const initialQty = parseFloat(initialStockInput.val()) || 0;
            const remainingStock = parseFloat(remainingStockInput.val()) || 0;
            const adjustment = parseFloat(row.find('.adjustment').val()) || 0;

            const newInitialQty = remainingStock + adjustment;
            row.find('.new-initial-qty').val(newInitialQty);
        }

        function updateAllNewQty() {
            $('#adjustmentTable tbody tr').each(function() {
                updateRowNewQty($(this));
            });
        }

        // Initialize table values
        updateAllNewQty();
        
    </script>

@endpush



