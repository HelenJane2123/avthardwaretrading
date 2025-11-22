@extends('layouts.master')

@section('title', 'Invoice | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-file-text"></i> Add Invoice</h1>
                <p class="text-muted mb-0">Create a new invoice for customers.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Invoice</li>
                <li class="breadcrumb-item active">Add Invoice</li>
            </ul>
        </div>

        <div class="mb-3">
            <a class="btn btn-outline-primary" href="{{ route('invoice.index') }}">
                <i class="fa fa-list"></i> Manage Invoices
            </a>
        </div>

        {{-- Success Message --}}
        @if(session()->has('message'))
            <div class="alert alert-success mt-2">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <h3 class="tile-title mb-4"><i class="fa fa-file-text"></i> Invoice</h3>
                    <form method="POST" action="{{ route('invoice.store') }}">
                        @csrf
                        {{-- Customer Details --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Customer <span class="text-danger">*</span></label>
                                <select id="customerSelect" name="customer_id" class="form-control" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Invoice Due Date <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Invoice Number</label>
                                <input type="text" name="invoice_number" id="invoice_number" class="form-control" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mode of Payment <span class="text-danger">*</span></label>
                                <select name="payment_mode_id" id="payment_id" class="form-control" required>
                                    <option value="">-- Select Payment Mode --</option>
                                    @foreach($paymentModes as $mode)
                                        <option value="{{ $mode->id }}" data-term="{{ $mode->term }}">
                                            {{ $mode->name }} 
                                            @if($mode->term) ({{ $mode->term }} days) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                                <select id="discount_type" name="discount_type" class="form-control">
                                    <option value="" selected>Select Type of Discount</option>
                                    <option value="per_item" >Per Item</option>
                                    <option value="overall">Overall</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Salesman</label>
                               <select name="salesman" id="salesman_id" class="form-control" required>
                                    <option value="">-- Select Salesman --</option>
                                    @foreach($salesman as $salesmen)
                                        <option value="{{ $salesmen->id }}">
                                            {{ $salesmen->salesman_name }} 
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Customer Info --}}
                        <div id="customer-info" class="table-responsive mb-4 d-none">
                            <table class="table table-bordered">
                                <thead class="bg-primary text-white">
                                    <tr><th colspan="2"><i class="fa fa-building"></i> Customer Information</th></tr>
                                </thead>
                                <tbody>
                                    <tr><th>Customer Code</th><td id="info-customer-code"></td></tr>
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
                                            <select name="product_id[]" class="form-control productname">
                                                <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-code="{{ $product->product_code }}"
                                                        data-price="{{ $product->sales_price }}"
                                                        data-stock="{{ $product->remaining_stock }}"
                                                        data-unit="{{ $product->unit_id }}">
                                                        {{ $product->product_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="text-muted small selected-product-info mt-1"></div>                                            
                                        </td>
                                        <td>
                                            <select name="unit[]" class="form-control unit">
                                                <option value="">Select Unit</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="qty[]" class="form-control qty">
                                            <small class="text-muted available-stock"></small>
                                        </td>
                                        <td><input type="number" step="0.01" name="price[]" class="form-control price"></td>
                                        <td>
                                            <div class="discounts-wrapper">
                                                <div class="discount-row d-flex align-items-center gap-2 mb-2">
                                                    <select name="dis[0][]" class="form-control dis">
                                                        <option value="0">---Select Discount---</option>
                                                        @foreach($taxes as $tax)
                                                            <option value="{{$tax->name}}">{{$tax->name}} %</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-success btn-sm add-discount"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </div>
                                        </td>
                                        <td><input type="number" step="0.01" name="amount[]" class="form-control amount" readonly></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Tax / Discount</th>
                                        <td colspan="3"><input type="text" id="discount" name="discount_value" class="form-control"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Shipping</th>
                                        <td colspan="3"><input type="number" id="shipping" name="shipping_fee" class="form-control" value="0"></td>
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
                            <input type="hidden" name="discount_approved" id="discount_approved" value="0">
                            <label class="form-label">Comments / Special Instructions</label>
                            <textarea name="remarks" rows="3" class="form-control" placeholder="Enter any notes or delivery instructions..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-paper-plane"></i> Submit Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    {{-- Discount Approval Modal --}}
    <div id="discountApprovalModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Discount Approval Required</h5>
                    <button type="button" class="close" id="closeModal" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This discount requires admin approval. Please enter the admin password:</p>
                    <input type="password" id="adminPassword" class="form-control" placeholder="Enter admin password">
                    <small class="text-danger d-none" id="passwordError">Invalid password. Try again.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelModal" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="approveDiscount">Approve</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('.productname').select2({
                placeholder: "Select Product",
                allowClear: true,
                width: '400px'
            });
            $('#customerSelect').select2({
                placeholder: "Select Customer",
                allowClear: true,
                width: 'resolve'
            });
            // Disable all Add Discount buttons initially
            $('.add-discount').prop('disabled', true);

            $('.addRow').on('click', function() {
                addRow();
                calculateTotals();
            });
            const productOptions = `{!! 
                $products->map(function($product){
                    return '<option value="'.$product->id.'">'.$product->name.'</option>';
                })->implode('') 
            !!}`;

            let rowIndex = $('#po-body tr').length;

            function addRow() {
                let options = `<option value="">Select Product</option>`;

                @foreach($products as $product)
                    options += `<option value="{{ $product->id }}" 
                                    data-code="{{ $product->product_code }}" 
                                    data-price="{{ $product->sales_price }}" 
                                    data-stock="{{ $product->remaining_stock }}"
                                    data-unit="{{ $product->unit_id }}">
                                    {{ $product->product_name }}
                                </option>`;
                @endforeach
                const newRow = `<tr>
                    <td><input type="text" name="product_code[]" class="form-control code" readonly></td>
                    <td>
                        <select name="product_id[]" class="form-control productname">
                            ${options}
                        </select>
                        <div class="text-muted small selected-product-info mt-1"></div>
                    </td>
                    <td>
                        <select name="unit[]" class="form-control unit">
                            <option value="">Select Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="qty[]" class="form-control qty">
                        <small class="text-muted available-stock"></small>
                    </td>
                    <td><input type="text" name="price[]" class="form-control price"></td>
                    <td>
                        <div class="discounts-wrapper">
                            <div class="discount-row d-flex align-items-center gap-2 mb-2">
                                <select name="dis[${rowIndex}][]" class="form-control dis">
                                    <option value="0">---Select Discount---</option>
                                    @foreach($taxes as $tax)
                                        <option value="{{$tax->name}}">{{$tax->name}} %</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-success btn-sm add-discount">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                    <td><input type="text" name="amount[]" class="form-control amount" readonly></td>
                    <td><a class="btn btn-danger remove"><i class="fa fa-remove"></i></a></td>
                </tr>`;

                $('#po-body').append(newRow);

                // Re-initialize Select2 for the new row
                $('#po-body tr:last .productname').select2({
                    placeholder: "Select Product",
                    allowClear: true,
                    width: '400px'
                });

                // Apply same behavior based on current discount type
                const discountType = $('#discount_type').val();

                if (discountType === "per_item") {
                    // enable per-item discount controls
                    $('#po-body tr:last .dis').prop('disabled', false);
                    $('#po-body tr:last .add-discount').prop('disabled', false);
                } else if (discountType === "overall") {
                    // disable per-item discount controls
                    $('#po-body tr:last .dis').prop('disabled', true);
                    $('#po-body tr:last .add-discount').prop('disabled', true);
                } else {
                    // no type selected yet
                    $('#po-body tr:last .dis').prop('disabled', true);
                    $('#po-body tr:last .add-discount').prop('disabled', true);
                }
            }


            $(document).on('click', '.remove', function () {
                var l = $('tbody tr').length;
                if (l == 1) {
                    alert('You can\'t delete the last one');
                    calculateTotals();
                } else {
                    $(this).closest('tr').remove();
                    calculateTotals();
                }
            });

            $(document).on('change', '.dis', function() {
                const selectedVal = $(this).val();
                const addBtn = $(this).closest('.discount-row').find('.add-discount');
                if (selectedVal !== "0" && selectedVal !== "" && selectedVal !== null) {
                    addBtn.prop('disabled', false);
                } else {
                    addBtn.prop('disabled', true);
                }
                calculateTotals();
            });

            $(document).on('click', '.add-discount', function () {
                const wrapper = $(this).closest('.discounts-wrapper');
                const row = $(this).closest('tr');
                const rowIndex = row.index(); // get row number
                const newRow = `
                    <div class="discount-row d-flex align-items-center gap-2 mb-2">
                        <select name="dis[${rowIndex}][]" class="form-control dis">
                            <option value="0">---Select Discount---</option>
                            @foreach($taxes as $tax)
                                <option value="{{$tax->name}}">{{$tax->name}} %</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-success btn-sm add-discount">
                            <i class="fa fa-plus"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm remove-discount">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>`;
                wrapper.append(newRow);
            });
            // Remove a discount
            $(document).on('click', '.remove-discount', function() {
                $(this).closest('.discount-row').remove();
                calculateTotals();
            });

            // Populate Customer Information
            $('#customerSelect').on('change', function () {
                const customerId = $(this).val();
                console.log(customerId);
                if (!customerId) return;

                $.ajax({
                    url: '/customers/' + customerId, // plural now
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log("Customer API response:", data);
                        if (data && data.customer) {
                            const c = data.customer; // shortcut
                            $('#customer-info').removeClass('d-none');
                            $('#info-customer-code').text(c.customer_code || '');
                            $('#info-name').text(c.name || '');
                            $('#info-phone').text(c.mobile || '');
                            $('#info-email').text(c.email || '');
                            $('#info-address').text(c.address || '');
                        } else {
                            alert("Customer not found.");
                            $('#customer-info').addClass('d-none');
                        }
                    }
                });
            });

            // When user selects a product
            $(document).on('change', '.productname', function () {
                var $row = $(this).closest('tr');
                var selected = $(this).find(':selected');
                var productName = selected.text();
                var stock = parseInt(selected.data('stock')) || 0;
                var unitId = selected.data('unit') || '';
                var code = selected.data('code') || '';
                var price = parseFloat(selected.data('price')) || 0;

                // Fill data
                $row.find('.code').val(code);
                $row.find('.price').val(price);
                $row.find('.amount').val('');
                $row.find('.available-stock').text("Available: " + stock);
                $row.find('.qty').prop('readonly', false).val('').data('stock', stock);
                if (unitId) $row.find('.unit').val(unitId);

                let statusText = '';
                if (stock <= 0) {
                    statusText = `<span class="text-danger">Out of Stock</span>`;
                } else if (stock <= 5) {
                    statusText = `<span class="text-warning">Low Stock (${stock} left)</span>`;
                } else {
                    statusText = `<span class="text-success">In Stock (${stock} available)</span>`;
                }

                $row.find('.available-stock').html(statusText);
                // Show selected product name below dropdown
                $row.find('.selected-product-info').html(productName);

                calculateTotals();
            });

            // Auto-compute due date based on mode of payment
            $('#payment_id').on('change', function () {
                let selected = $(this).find(':selected');
                let term = parseInt(selected.data('term')) || 0; // get days from option
                let invoiceDate = $('input[name="invoice_date"]').val();

                if (invoiceDate) {
                    let d = new Date(invoiceDate);
                    d.setDate(d.getDate() + term); // add term days
                    let dueDate = d.toISOString().split('T')[0]; // format yyyy-mm-dd
                    $('input[name="due_date"]').val(dueDate);
                }
            });
            
            // Validate qty on input
            $(document).on('input', '.qty', function () {
                var $row = $(this).closest('tr');
                var originalStock = parseInt($row.find('.qty').data('stock')) || 0;
                var enteredQty = parseInt($(this).val()) || 0;

                // If no product selected yet, prevent typing
                if (!$row.find('.productname').val()) {
                    alert("Please select a product first!");
                    $(this).val('');
                    return;
                }

                // Validation: exceed stock
                if (enteredQty > originalStock) {
                    alert("Quantity exceeds available stock!");
                    $(this).val(originalStock);
                    enteredQty = originalStock;
                }

                // Update remaining stock display
                var remainingStock = originalStock - enteredQty;
                $row.find('.available-stock').text("Available: " + remainingStock);

                // Update amount
                var price = parseFloat($row.find('.price').val()) || 0;
                var amount = price * enteredQty;
                $row.find('.amount').val(amount.toFixed(2));

                calculateTotals();
            });
            
            // get the latest Invoice number in database
            let randomInvoice = 'DR-' + Math.floor(100000 + Math.random() * 900000);
            $('#invoice_number').val(randomInvoice);
        });

        let formPendingSubmit = null;
        $('form').on('submit', function(e) {
            e.preventDefault(); // stop default submit

            let hasError = false;
            let errorMessages = [];

            $('#po-body tr').each(function(index) {
                const $row = $(this);
                const productName = $row.find('.productname option:selected').text();
                const productId = $row.find('.productname').val();
                const stock = parseInt($row.find('.qty').data('stock')) || 0;
                const qty = parseInt($row.find('.qty').val()) || 0;

                // Skip empty rows
                if (!productId) return;

                // Out of stock
                if (stock <= 0) {
                    hasError = true;
                    errorMessages.push(`❌ ${productName} is out of stock.`);
                }

                // Exceeds stock
                if (qty > stock) {
                    hasError = true;
                    errorMessages.push(`⚠️ ${productName} quantity (${qty}) exceeds available stock (${stock}).`);
                }

                // Zero or negative qty
                if (qty <= 0) {
                    hasError = true;
                    errorMessages.push(`⚠️ ${productName} must have a quantity greater than 0.`);
                }
            });

            if (hasError) {
                alert("Cannot submit invoice:\n\n" + errorMessages.join("\n"));
                return false; // stop submit
            }

            // if all good, continue with submission
            this.submit();
        });

        // Disable all discount fields by default
        $('.dis, #discount').prop('disabled', true);

        $('#discount_type').trigger('change');
            let discountApprovalCount = 0;
            let pendingDiscountInput = null;

            var discountModal = new bootstrap.Modal(document.getElementById('discountApprovalModal'), {
                backdrop: 'static',
                keyboard: false
            });

            /*$(document).on("input", ".dis, #discount", function() {
                var val = parseFloat($(this).val()) || 0;

                if (val > 0) {
                    if (discountApprovalCount < 3) {
                        pendingDiscountInput = $(this); // remember which input triggered
                        if ($('#discountApprovalModal').is(':hidden')) { // only show if hidden
                            discountModal.show(); // <-- updated
                        }
                    } else {
                        alert("Maximum of 3 discount approvals reached.");
                        $(this).val(0);
                        calculateTotals();
                    }
                }
            });*/

            $('#approveDiscount').on('click', function () {
                let password = $('#adminPassword').val().trim();

                if (password === '') {
                    $('#passwordError').text('Password is required.').removeClass('d-none');
                    return;
                }

                $.ajax({
                    url: "{{ route('validate.admin.password') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        password: password
                    },
                    success: function (response) {
                        console.log('password'.response);
                        if(response.success){
                            $('#discount_approved').val(1);
                            discountModal.hide(); // hide modal
                            if(formPendingSubmit){ 
                                formPendingSubmit.submit(); 
                                formPendingSubmit = null; 
                            }
                        } else {
                            $('#passwordError').text('Invalid password.').removeClass('d-none');
                        }
                    },
                    error: function () {
                        $('#passwordError').text('Invalid password. Try again.').removeClass('d-none');
                    }
                });
            });

            // Cancel / close modal
            $('#closeModal, #cancelModal').click(function() {
                if (formPendingSubmit) {
                    // reset discount if modal canceled
                    $('.dis').val(0);
                    $('#discount').val(0);
                    calculateTotals();
                    formPendingSubmit = null;
                }
                discountModal.hide();
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

            $('#po-body tr').each(function(index) {
                let qty = parseFloat($(this).find('.qty').val()) || 0;
                let price = parseFloat($(this).find('.price').val()) || 0;
                let lineTotal = qty * price;

                // Apply per-item discounts
                $(this).find('.dis').each(function() {
                    let disText = $(this).find('option:selected').text();
                    let disValue = parseFloat(disText) || 0;
                    if (disValue > 0) {
                        lineTotal -= (lineTotal * disValue / 100);
                    }
                });

                $(this).find('.amount').val(lineTotal.toFixed(2));
                subtotal += lineTotal;
            });

            // Overall discount
            let discountType = $('#discount_type').val();
            let overallDis = parseFloat($('#discount').val()) || 0;
            let overallAmount = 0;

            if (discountType === 'overall' && overallDis > 0) {
                overallAmount = subtotal * (overallDis / 100);
            }

            let afterDiscount = subtotal - overallAmount;
            let shipping = parseFloat($('#shipping').val()) || 0;
            let other = parseFloat($('#other').val()) || 0;
            let grandTotal = afterDiscount + shipping + other;

            $('#subtotal').val(subtotal.toFixed(2));
            $('#grand_total').val(grandTotal.toFixed(2));
        }

        $(document).on('change input', '.dis, .qty, .price, #discount_type, #discount, #shipping, #other', function() {
            calculateTotals();
        });

        $('#discount_type').on('change', function () {
            const type = $(this).val();

            if (type === "per_item") {
                // Enable add buttons only for per-item discount
                $('.add-discount').prop('disabled', false);
                $('.dis').prop('disabled', false);
                $('#discount').prop('disabled', true).val(0); // disable overall field
            } else if (type === "overall") {
                // Disable add buttons when overall discount selected
                $('.add-discount').prop('disabled', true);
                $('.dis').prop('disabled', true).val(0); // disable per-item dropdowns
                $('#discount').prop('disabled', false);
            } else {
                // Disable everything if no type selected
                $('.add-discount').prop('disabled', true);
                $('.dis').prop('disabled', true).val(0);
                $('#discount').prop('disabled', true).val(0);
            }

            calculateTotals();
        });
                
    </script>

@endpush