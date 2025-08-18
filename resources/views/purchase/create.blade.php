@extends('layouts.master')

@section('title', 'Purchase | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Add Purchase</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Purchase</li>
                <li class="breadcrumb-item"><a href="#">Add Purchase</a></li>
            </ul>
        </div>


         <div class="row">
             <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Purchase Order</h3>
                    <div class="tile-body">
                        <!-- <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="btn btn-primary mb-3" onclick="printPurchaseOrder()">
                                <i class="fa fa-print"></i> Print Purchase Order
                            </button>
                        </div> -->
                        <form method="POST" action="{{ route('invoice.store') }}">
                            @csrf
                            <div class="row mb-4">
                                {{-- Supplier --}}
                                <div class="col-md-4 form-group">
                                    <label>Supplier</label>
                                    <select name="supplier_id" id="supplierSelect" class="form-control" required>
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Purchase Date --}}
                                <div class="col-md-3 form-group">
                                    <label>Purchase Date</label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                {{-- PO Number --}}
                                <div class="col-md-3 form-group">
                                    <label for="po_number">PO Number</label>
                                    <input type="text" name="po_number" id="po_number" class="form-control" readonly>
                                </div>

                                {{-- Salesman --}}
                                <div class="col-md-4 form-group">
                                    <label for="salesman">Salesman</label>
                                    <input type="text" name="salesman" id="salesman" class="form-control" placeholder="Enter salesman's name">
                                </div>

                                {{-- Payment Term --}}
                                <div class="col-md-3 form-group">
                                    <label for="payment_term">Payment Term</label>
                                    <select name="payment_term" id="payment_term" class="form-control">
                                        <option value="">Select Term</option>
                                        <option value="Cash">Cash</option>
                                        <option value="7 Days">7 Days</option>
                                        <option value="15 Days">15 Days</option>
                                        <option value="30 Days">30 Days</option>
                                    </select>
                                </div>
                            </div>
                            <div id="supplier-info" class="table-responsive mb-4" style="display: none;">
                                <table class="table table-bordered table-striped shadow-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th colspan="2" class="bg-primary text-white">
                                                <i class="fa fa-building"></i> Supplier Information
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>Supplier Code</th>
                                            <td id="info-supplier-code"></td>
                                        </tr>
                                        <tr>
                                            <th>Name</th>
                                            <td id="info-name"></td>
                                        </tr>
                                        <tr>
                                            <th>Phone</th>
                                            <td id="info-phone"></td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td id="info-email"></td>
                                        </tr>
                                        <tr>
                                            <th>Address</th>
                                            <td id="info-address"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Product Code</th>
                                        <th>Product</th>
                                        <th>Quantity Ordered</th>
                                        <th>Unit Price</th>
                                        <th>Discount (%)</th>
                                        <th>Amount</th>
                                        <th><a class="btn btn-success btn-sm addRow"><i class="fa fa-plus"></i></a></th>
                                    </tr>
                                </thead>
                                <tbody id="po-body">
                                    <tr>
                                        <td><input type="text" name="product_code[]" class="form-control code" readonly></td>
                                        <td>
                                            <select name="product_id[]" class="form-control productname">
                                                <!-- <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                        data-code="{{ $product->item_code }}" 
                                                        data-price="{{ $product->item_price }}">
                                                        {{ $product->item_code }} - {{ $product->item_description }}
                                                    </option>
                                                @endforeach -->
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="qty[]" class="form-control qty">
                                            <!-- <small class="text-muted stock-info"></small> -->
                                        </td>
                                        <td><input type="number" step="0.01" name="price[]" class="form-control price"></td>
                                        <td><input type="number" step="0.01" name="dis[]" class="form-control dis"></td> <!-- Tax will go here -->
                                        <td><input type="number" step="0.01" name="amount[]" class="form-control amount" readonly></td>
                                        <td><a class="btn btn-danger btn-sm remove"><i class="fa fa-remove"></i></a></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <input type="hidden" name="discount_type" id="hidden_discount_type">
                                        <input type="hidden" name="overall_discount" id="hidden_overall_discount">
                                        <input type="hidden" name="subtotal_value" id="hidden_subtotal">
                                        <input type="hidden" name="discount_value" id="hidden_discount_value">
                                        <input type="hidden" name="shipping_value" id="hidden_shipping">
                                        <input type="hidden" name="other_value" id="hidden_other">
                                        <input type="hidden" name="grand_total_value" id="hidden_grand_total">
                                        <th colspan="5" class="text-right">Discount Type</th>
                                        <th colspan="2">
                                            <select id="discount_type" class="form-control">
                                                <option value="per_item" selected>Per Item</option>
                                                <option value="overall">Overall</option>
                                            </select>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Tax/Discount</th>
                                        <td colspan="2"><input type="text" class="form-control" id="discount"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Shipping</th>
                                        <td colspan="2"><input type="number" class="form-control" id="shipping" value="0"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Other Charges</th>
                                        <td colspan="2"><input type="number" class="form-control" id="other" value="0"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Subtotal</th>
                                        <td colspan="2"><input type="text" class="form-control" id="subtotal" readonly></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Total</th>
                                        <td colspan="2"><input type="text" class="form-control" id="grand_total" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="form-group mt-3">
                                <label>Comments or Special Instructions</label>
                                <textarea name="comments" rows="4" class="form-control" placeholder="Enter any notes or delivery instructions..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3">Submit Purchase Order</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

@endsection
@push('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
    <script src="{{asset('/')}}js/multifield/jquery.multifield.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){

            $('.addRow').on('click', function() {
                if (!window.supplierItems || window.supplierItems.length === 0) {
                    alert('Please select a supplier first.');
                    return;
                }
                addRow(window.supplierItems);
                calculateTotals();
            });
            const productOptions = `{!! 
                $products->map(function($product){
                    return '<option value="'.$product->id.'">'.$product->name.'</option>';
                })->implode('') 
            !!}`;

            function addRow(supplierItems = []) {
                let options = '<option value="">Select Product</option>';

                supplierItems.forEach(function(item){
                    options += `<option value="${item.id}" data-code="${item.item_code}" data-price="${item.item_price}" data-dis="${item.item_amount || 0}">
                                   ${item.item_description}
                                </option>`;
                });

                const addRow = `<tr>
                    <td><input type="text" name="product_code[]" class="form-control code" readonly></td>
                    <td>
                        <select name="product_id[]" class="form-control productname">
                            ${options}
                        </select>
                    </td>
                    <td><input type="number" name="qty[]" class="form-control qty"></td>
                    <td><input type="text" name="price[]" class="form-control price"></td>
                    <td><input type="text" name="dis[]" class="form-control dis"></td>
                    <td><input type="text" name="amount[]" class="form-control amount" readonly></td>
                    <td><a class="btn btn-danger remove"><i class="fa fa-remove"></i></a></td>
                </tr>`;

                $('#po-body').append(addRow);
                // DISABLE discount input if overall is selected
                if ($('#discount_type').val() === 'overall') {
                    $('#po-body tr:last').find('.dis').prop('disabled', true);
                }
            }

            $('.remove').live('click', function () {
                var l =$('tbody tr').length;
                if(l==1){
                    alert('you cant delete last one');
                    calculateTotals();
                }
                else{
                    $(this).parent().parent().remove();
                }

            });

            //Populate Supplier Information
            let supplierProductOptions = '';
            $('#supplierSelect').on('change', function () {
                const supplierId = $(this).val();
                if (!supplierId) return;

                $.ajax({
                    url: '/supplier/' + supplierId + '/items',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // Store supplier items globally
                        window.supplierItems = data.items || [];
                         $('#supplier-info').show();
                        $('#info-supplier-code').text(data.supplier.supplier_code);
                        $('#info-name').text(data.supplier.name);
                        $('#info-address').text(data.supplier.address);
                        $('#info-phone').text(data.supplier.mobile);
                        $('#info-email').text(data.supplier.email);

                        // Update existing rows
                        $('.productname').each(function () {
                            let options = '<option value="">Select Product</option>';
                            window.supplierItems.forEach(function(item){
                                options += `<option value="${item.id}" data-code="${item.item_code}" data-price="${item.item_price}" data-dis="${item.item_amount || 0}">
                                               ${item.item_description}
                                            </option>`;
                            });
                            $(this).html(options);
                        });
                    }
                });
            });

            $(document).on('change', '.productname', function () {
                var $row = $(this).closest('tr');
                var selected = $(this).find(':selected');

                $row.find('.code').val(selected.data('code') || '');
                $row.find('.price').val(selected.data('price') || '');
                //$row.find('.dis').val(selected.data('dis') || '');

                calculateTotals(); // make sure this function exists
            });
            
            // get the latest PO number in database
            $.ajax({
                url: '/api/po/latest',
                method: 'GET',
                success: function (response) {
                    let newPoNumber;

                    if (response.po_number) {
                        // Extract the numeric part, increment, then pad again
                        const numPart = parseInt(response.po_number.replace('PO', ''), 10);
                        const nextNum = numPart + 1;
                        newPoNumber = 'PO' + nextNum.toString().padStart(4, '0');
                    } else {
                        // If no existing PO, start from PO0001
                        newPoNumber = 'PO0001';
                    }

                    $('#po_number').val(newPoNumber);
                },
                error: function () {
                    alert("Failed to generate PO number.");
                    $('#po_number').val('PO0001'); // fallback default
                }
            });
        });

        $('form').on('submit', function () {
            $('#hidden_discount_type').val($('#discount_type').val());
            $('#hidden_overall_discount').val($('#discount_type').val() === 'overall' ? $('#tax').val() : 0);
            $('#hidden_subtotal').val($('#subtotal').val());
            $('#hidden_discount_value').val($('#discount_type').val() === 'per_item' ? 0 : $('#tax').val());
            $('#hidden_shipping').val($('#shipping').val());
            $('#hidden_other').val($('#other').val());
            $('#hidden_grand_total').val($('#grand_total').val());
        });

        //Populate Product details
        $(document).on('change', 'select[name="supplier_id"]', function () {
            var supplierId = $(this).val();
            if (supplierId) {
                $.ajax({
                    url: '/supplier/' + supplierId + '/items',
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        // update all product dropdowns
                        $('.productname').each(function () {
                            var $dropdown = $(this);
                            $dropdown.empty().append('<option value="">Select Product</option>');

                            $.each(data.items, function (index, item) {
                                var $option = $('<option>', {
                                    value: item.id,
                                    text: item.item_description,
                                    'data-code': item.item_code,
                                    'data-price': item.item_price
                                });
                                $dropdown.append($option);
                            });
                        });
                    }
                });
            } else {
                $('.productname').empty().append('<option value="">Select Product</option>');
            }
        });
        function calculateTotals() {
            let subtotal = 0;
            let totalDiscount = 0;
            let discountType = $('#discount_type').val();
            
            // Calculate line totals
            $('#po-body tr').each(function () {
                const qty = parseFloat($(this).find('.qty').val()) || 0;
                const price = parseFloat($(this).find('.price').val()) || 0;
                const dis = parseFloat($(this).find('.dis').val()) || 0;

                let lineTotal = qty * price;

                if (discountType === 'per_item') {
                    let discountAmount = lineTotal * dis / 100;
                    lineTotal -= discountAmount;
                    totalDiscount += discountAmount;
                }

                $(this).find('.amount').val(lineTotal.toFixed(2));
                subtotal += lineTotal;
            });

            // Apply overall discount if selected
            if (discountType === 'overall') {
                const overallDiscountPercent = parseFloat($('#discount').val()) || 0;
                totalDiscount = subtotal * overallDiscountPercent / 100;
                subtotal -= totalDiscount;
            }

            // Apply tax
            const taxPercent = parseFloat($('#tax').val()) || 0;
            const taxAmount = subtotal * taxPercent / 100;

            const shipping = parseFloat($('#shipping').val()) || 0;
            const other = parseFloat($('#other').val()) || 0;

            const grandTotal = subtotal + taxAmount + shipping + other;

            // Update UI
            $('#subtotal').val(subtotal.toFixed(2));
            $('#grand_total').val(grandTotal.toFixed(2));

            // Update hidden fields for submission
            $('#hidden_discount_value').val(totalDiscount.toFixed(2));
            $('#hidden_subtotal').val(subtotal.toFixed(2));
            $('#hidden_grand_total').val(grandTotal.toFixed(2));
        }
         $(document).on('input', '.qty, .price, .dis, #discount, #tax, #shipping, #other', function() {
            calculateTotals();
        });
        $('#discount_type').on('change', function() {
            const type = $(this).val();
            if(type === 'overall') $('.dis').prop('disabled', true);
            else $('.dis').prop('disabled', false);
            calculateTotals();
        });
    </script>

@endpush



