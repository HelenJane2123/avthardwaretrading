@extends('layouts.master')

@section('title', 'Purchase | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-shopping-cart"></i> Add Purchase</h1>
                <p class="text-muted mb-0">Create a new purchase order and add supplier items.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Purchase</li>
                <li class="breadcrumb-item active">Add Purchase</li>
            </ul>
        </div>

        <div class="mb-3">
            <a class="btn btn-outline-primary" href="{{ route('purchase.index') }}">
                <i class="fa fa-list"></i> Manage Purchases
            </a>
        </div>

        {{-- Success Message --}}
        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <h3 class="tile-title mb-4"><i class="fa fa-file-text"></i> Purchase Order</h3>

                    <form method="POST" action="{{ route('purchase.store') }}">
                        @csrf
                        {{-- Supplier & Order Details --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplierSelect" class="form-control" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">PO Number</label>
                                <input type="text" name="po_number" id="po_number" class="form-control" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Salesman</label>
                               <select name="salesman_id" id="salesman_id" class="form-control" required>
                                    <option value="">-- Select Salesman --</option>
                                    @foreach($salesman as $salesmen)
                                        <option value="{{ $salesmen->id }}">
                                            {{ $salesmen->salesman_name }} 
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mode of Payment <span class="text-danger">*</span></label>
                                <select name="payment_id" id="payment_id" class="form-control" required>
                                    <option value="">-- Select Payment Mode --</option>
                                    @foreach($paymentModes as $mode)
                                        <option value="{{ $mode->id }}">
                                            {{ $mode->name }} 
                                            @if($mode->term) ({{ $mode->term }} days) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Supplier Info --}}
                        <div id="supplier-info" class="table-responsive mb-4 d-none">
                            <table class="table table-bordered">
                                <thead class="bg-primary text-white">
                                    <tr><th colspan="2"><i class="fa fa-building"></i> Supplier Information</th></tr>
                                </thead>
                                <tbody>
                                    <tr><th>Supplier Code</th><td id="info-supplier-code"></td></tr>
                                    <tr><th>Name</th><td id="info-name"></td></tr>
                                    <tr><th>Phone</th><td id="info-phone"></td></tr>
                                    <tr><th>Email</th><td id="info-email"></td></tr>
                                    <tr><th>Address</th><td id="info-address"></td></tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Product List --}}
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>Product Code</th>
                                        <th>Product</th>
                                        <th>Unit</th>
                                        <th>Qty Ordered</th>
                                        <th>Unit Price</th>
                                        <th>Discount (%)</th>
                                        <th>Amount</th>
                                        <th class="text-center">
                                            <button type="button" class="btn btn-success btn-sm addRow">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="po-body">
                                    <tr>
                                        <td><input type="text" name="product_code[]" class="form-control code" readonly></td>
                                        <td>
                                            <select name="product_id[]" class="form-control purchaseproduct"></select>
                                            <small class="text-muted d-block mt-1 selected-product-name" style="font-size: 0.85rem;"></small>
                                        </td>
                                        <td>
                                            <select name="unit[]" class="form-control unit">
                                                <option value="">Select Unit</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="qty[]" class="form-control qty"></td>
                                        <td><input type="number" step="0.01" name="price[]" class="form-control price"></td>
                                        <td><input type="number" step="0.01" name="dis[]" class="form-control dis"></td>
                                        <td><input type="number" step="0.01" name="amount[]" class="form-control amount" readonly></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Discount Type</th>
                                        <td colspan="3">
                                            <select id="discount_type" name="discount_type" class="form-control">
                                                <option value="all" selected>All</option>
                                                <option value="per_item" >Per Item</option>
                                                <option value="overall">Overall</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Tax / Discount</th>
                                        <td colspan="3"><input type="text" id="discount" name="discount_value" class="form-control"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Shipping</th>
                                        <td colspan="3"><input type="number" id="shipping" name="shipping" class="form-control" value="0"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Other Charges</th>
                                        <td colspan="3"><input type="number" id="other" name="other_charges" class="form-control" value="0"></td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <th colspan="5" class="text-end">Subtotal</th>
                                        <td colspan="3"><input type="text" id="subtotal" name="subtotal" class="form-control" readonly></td>
                                    </tr>
                                    <tr class="fw-bold bg-secondary text-white">
                                        <th colspan="5" class="text-end">Grand Total</th>
                                        <td colspan="3"><input type="text" id="grand_total" name="grand_total" class="form-control" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Remarks --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Comments / Special Instructions</label>
                            <textarea name="remarks" rows="3" class="form-control" placeholder="Enter any notes or delivery instructions..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-paper-plane"></i> Submit Purchase Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
    <script src="{{asset('/')}}js/multifield/jquery.multifield.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('.purchaseproduct').select2({
                placeholder: "Select Product",
                allowClear: true,
                width: '400px'
            });
            $('#supplier_id').select2({
                placeholder: "Select Customer",
                allowClear: true,
                width: 'resolve'
            });
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
                        <select name="product_id[]" class="form-control purchaseproduct">
                            ${options}
                        </select>
                        <small class="text-muted d-block mt-1 selected-product-name" style="font-size: 0.85rem;"></small>
                    </td>
                    <td>
                        <select name="unit[]" class="form-control unit">
                            <option value="">Select Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="qty[]" class="form-control qty"></td>
                    <td><input type="text" name="price[]" class="form-control price"></td>
                    <td><input type="text" name="dis[]" class="form-control dis"></td>
                    <td><input type="text" name="amount[]" class="form-control amount" readonly></td>
                    <td><a class="btn btn-danger remove"><i class="fa fa-remove"></i></a></td>
                </tr>`;

                $('#po-body').append(addRow);
                $('.purchaseproduct').select2({
                    placeholder: "Select Product",
                    allowClear: true,
                    width: '400px'
                });

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
                        $('.purchaseproduct').each(function () {
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

            $(document).on('change', '.purchaseproduct', function () {
                var $row = $(this).closest('tr');
                var selected = $(this).find(':selected');

                const code  = selected.data('code') || '';
                const price = selected.data('price') || '';
                const name  = selected.text().trim() || '';

                $row.find('.code').val(code);
                $row.find('.price').val(price);
                $row.find('.selected-product-name').text(name);

                calculateTotals(); // make sure this function exists
            });
            
            // get the latest PO number in database
            $.ajax({
                url: '/api/po/latest',
                method: 'GET',
                success: function (response) {
                    console.log("Latest PO response:", response);
                    let newPoNumber;

                    if (response.po_number) {
                        const numPart = parseInt(response.po_number.replace('PO', ''), 10);
                        const nextNum = numPart + 1;
                        newPoNumber = 'PO' + nextNum.toString().padStart(4, '0');
                    } else {
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

        $('.purchaseproduct').each(function() {
            const $row = $(this).closest('tr');
            const selected = $(this).find(':selected');
            const name = selected.text().trim() || '';
            $row.find('.selected-product-name').text(name);
        });

        $('form').on('submit', function () {
            const type = $('#discount_type').val();
            $('#hidden_discount_type').val(type);

            // store overall discount PERCENT only when overall mode
            $('#hidden_overall_discount').val(type === 'overall' ? ($('#discount').val() || 0) : 0);

            // these are already set inside calculateTotals, but keep them fresh
            $('#hidden_subtotal').val($('#subtotal').val() || 0);
            $('#hidden_shipping').val($('#shipping').val() || 0);
            $('#hidden_other').val($('#other').val() || 0);
            $('#hidden_grand_total').val($('#grand_total').val() || 0);
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
                        $('.purchaseproduct').each(function () {
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
                $('.purchaseproduct').empty().append('<option value="">Select Product</option>');
            }

            $('#discount_type').trigger('change');
        });

        function calculateTotals() {
            let baseSubtotal = 0;
            let totalDiscount = 0;
            const discountType = $('#discount_type').val();

            $('#po-body tr').each(function () {
                const qty   = parseFloat($(this).find('.qty').val())   || 0;
                const price = parseFloat($(this).find('.price').val()) || 0;
                const disP  = parseFloat($(this).find('.dis').val())   || 0;

                const lineBase = qty * price;
                baseSubtotal += lineBase;

                let lineNet = lineBase;

                // Per-item discount
                if ((discountType === 'per_item' || discountType === 'all') && disP > 0) {
                    const lineDiscAmt = lineBase * disP / 100;
                    totalDiscount += lineDiscAmt;
                    lineNet -= lineDiscAmt;
                }

                $(this).find('.amount').val(lineNet.toFixed(2));
            });

            // Overall discount
            if (discountType === 'overall' || discountType === 'all') {
                const overallPct = parseFloat($('#discount').val()) || 0;
                const overallDiscAmt = baseSubtotal * overallPct / 100;
                totalDiscount += overallDiscAmt;
            }

            const shipping = parseFloat($('#shipping').val()) || 0;
            const other    = parseFloat($('#other').val())    || 0;

            const taxableBase = (baseSubtotal - totalDiscount) + shipping + other;
            const grandTotal  = taxableBase;

            $('#subtotal').val(baseSubtotal.toFixed(2));
            $('#grand_total').val(grandTotal.toFixed(2));

            // Hidden backend values if you have them
            $('#hidden_subtotal').val(baseSubtotal.toFixed(2));
            $('#hidden_discount_value').val(totalDiscount.toFixed(2));
            $('#hidden_grand_total').val(grandTotal.toFixed(2));
        }

        $(document).on('input', '.qty, .price, .dis, #discount, #tax, #shipping, #other', function() {
            calculateTotals();
        });

        $('#discount_type').on('change', function() {
            const type = $(this).val();

            if (type === 'overall') {
                $('#discount').prop('disabled', false);
                $('.dis').prop('disabled', true).val(0);
            } else if (type === 'per_item') {
                $('#discount').prop('disabled', true).val(0);
                $('.dis').prop('disabled', false);
            } else if (type === 'all') {
                $('#discount').prop('disabled', false);
                $('.dis').prop('disabled', false);
            }

            calculateTotals();
        });
        calculateTotals();
    </script>

@endpush