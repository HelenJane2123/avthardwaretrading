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
            <a class="btn btn-outline-primary" href="{{route('product.index')}}">
                <i class="fa fa-list"></i> Manage Products
            </a>
        </div>
        <div class="row mt-2">

            <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Product</h3>
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
                                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
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
                                        <div class="form-group col-md-4 position-relative">
                                            <label class="control-label">Product</label>
                                            <input type="text" id="product_name" name="product_name" class="form-control" placeholder="Search product...">
                                            <div id="productSuggestions" class="list-group" style="display:none; position:absolute; z-index:1000;"></div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label">Product Code</label>
                                            <input name="product_code" id="product_code" class="form-control" type="text" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label">Supplier Product Code</label>
                                            <input name="supplier_product_code" id="supplier_product_code" class="form-control" type="text" readonly>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label class="control-label">Serial Number</label>
                                            <input name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" type="number" placeholder="Enter Serial Number">
                                            @error('serial_number')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label class="control-label">Category</label>
                                            <select name="category_id" class="form-control">
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

                                        <div class="form-group col-md-4">
                                            <label class="control-label">Model</label>
                                            <input name="model" class="form-control @error('model') is-invalid @enderror" type="text" placeholder="Enter Model">
                                            @error('model')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        

                                        <div class="form-group col-md-4">
                                            <label class="control-label">Initial Quantity</label>
                                            <input name="quantity" class="form-control" type="number" min="0" placeholder="Enter Initial Stock">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label class="control-label">Remaining Stock</label>
                                            <input name="remaining_stock" id="remaining_stock" class="form-control" type="number" readonly>
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label class="control-label">Selling Price</label>
                                            <input name="sales_price" class="form-control @error('sales_price') is-invalid @enderror" type="number" placeholder="Enter Selling Price">
                                            @error('sales_price')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label class="control-label">Unit</label>
                                            <select name="unit_id" class="form-control">
                                                <option>---Select Unit---</option>
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

                                        <!-- <div class="form-group col-md-4">
                                            <label class="control-label">Discount</label>
                                            <select name="tax_id" class="form-control">
                                                <option value="0">---Select Discount---</option>
                                                @foreach($taxes as $tax)
                                                    <option value="{{$tax->id}}">{{$tax->name}} %</option>
                                                @endforeach
                                            </select>
                                            @error('tax_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                            @enderror
                                        </div> -->
                                    </div>
                                </div>
                            </div>
                           {{-- Supplier Section --}}
                            <div class="card mt-4">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fa fa-truck"></i> Supplier Details</h6>
                                </div>
                                <div class="card-body">
                                    <div id="example-2" class="content">
                                        <div class="group row g-3 align-items-end">
                                            <div class="col-md-6">
                                                <label for="supplier_name" class="form-label">Supplier</label>
                                                <input type="text" id="supplier_name" name="supplier_name" 
                                                    class="form-control" autocomplete="off">
                                                <div id="supplierSuggestions" class="list-group shadow-sm"></div>
                                                <input type="hidden" id="supplier_id" name="supplier_id[]">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="supplier_price" class="form-label">Supplier Item Price</label>
                                                <input type="number" id="supplier_price" name="supplier_price[]" 
                                                    class="form-control" placeholder="Purchase Price">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-4 mt-4 align-self-end">
                                <button class="btn btn-success" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>Add Product</button>
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
    <script src="{{asset('/')}}js/multifield/jquery.multifield.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
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
        function generateProductCode() {
            const prefix = "AVT";
            const timestamp = Date.now().toString().slice(-6); // last 6 digits of timestamp
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            return prefix + timestamp + random;
        }

        // Set it when the page loads
        $('#product_code').val(generateProductCode());

        const initialStockInput = document.querySelector('input[name="quantity"]');
        const remainingStockInput = document.querySelector('input[name="remaining_stock"]');

        if (initialStockInput && remainingStockInput) {
            initialStockInput.addEventListener('input', function () {
                remainingStockInput.value = this.value;
            });
        }
    </script>

@endpush



