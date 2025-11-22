@extends('layouts.master')

@section('title', 'Edit Invoice | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-file-text"></i> Edit Invoice</h1>
            <p class="text-muted mb-0">Update the invoice details.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Invoice</li>
            <li class="breadcrumb-item active">Edit Invoice</li>
        </ul>
    </div>

    <div class="mb-3">
        <a class="btn btn-outline-primary" href="{{ route('invoice.index') }}">
            <i class="fa fa-list"></i> Manage Invoices
        </a>
    </div>

    {{-- Success Message --}}
    @if(session()->has('message'))
        <div class="alert alert-success">{{ session()->get('message') }}</div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <h3 class="tile-title mb-4"><i class="fa fa-edit"></i> Edit Invoice</h3>

                <form method="POST" action="{{ route('invoice.update', $invoice->id) }}">
                    @csrf
                    @method('PUT')

                    {{-- Customer Details --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select id="customerSelect" name="customer_id" class="form-control" disabled>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                        {{ $invoice->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="customer_id" value="{{ $invoice->customer_id }}">

                        <div class="col-md-3">
                            <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control" 
                                value="{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Invoice Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" 
                                value="{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control" 
                                value="{{ $invoice->invoice_number }}" readonly>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="salesman">Salesman</label>
                            <select name="salesman" id="salesman_id" class="form-control" required>
                                <option value="">-- Select Salesman --</option>
                                @foreach($salesman as $salesmen)
                                    <option value="{{ $salesmen->id }}" 
                                        {{ $salesmen->id == $invoice->salesman ? 'selected' : '' }}>
                                        {{ $salesmen->salesman_name }} 
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mode of Payment <span class="text-danger">*</span></label>
                            <select name="payment_mode_id" class="form-control" required>
                                <option value="">-- Select Payment Mode --</option>
                                @foreach($paymentModes as $mode)
                                    <option value="{{ $mode->id }}" 
                                        data-term="{{ $mode->term }}" 
                                        {{ $invoice->payment_mode_id == $mode->id ? 'selected' : '' }}>
                                        {{ $mode->name }} 
                                        @if($mode->term) ({{ $mode->term }} days) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select id="discount_type" name="discount_type" class="form-control">
                                <option value="">Select Type of Discount</option>
                                <option value="per_item" {{ $invoice->discount_type == 'per_item' ? 'selected' : '' }}>Per Item</option>
                                <option value="overall" {{ $invoice->discount_type == 'overall' ? 'selected' : '' }}>Overall</option>
                            </select>
                        </div>
                    </div>

                    {{-- Customer Info --}}
                    <div id="customer-info" class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th colspan="2">
                                        <i class="fa fa-building"></i> Customer Information
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>Customer Code</th>
                                    <td>{{ $invoice->customer->customer_code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $invoice->customer->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $invoice->customer->mobile ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $invoice->customer->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ $invoice->customer->address ?? '-' }}</td>
                                </tr>
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
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td>
                                            <input type="text" name="product_code[]" class="form-control code"
                                                value="{{ $item->product->product_code ?? '' }}" readonly>
                                        </td>
                                        <td>
                                            <select name="product_id[]" class="form-control productname">
                                                <option value="">Select Product</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}"
                                                        data-code="{{ $p->product_code }}"
                                                        data-price="{{ $p->sales_price }}"
                                                        data-stock="{{ $p->remaining_stock }}"
                                                        data-unit="{{ $p->unit_id }}"
                                                        {{ $item->product_id == $p->id ? 'selected' : '' }}>
                                                        {{ $p->product_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="text-muted small selected-product-info mt-1"></div>      
                                        </td>
                                        <td>
                                            <select name="unit[]" class="form-control unit">
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}"
                                                        {{ $item->unit_id == $unit->id ? 'selected' : '' }}>
                                                        {{ $unit->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="qty[]" class="form-control qty" value="{{ $item->qty }}"></td>
                                        <td><input type="number" step="0.01" name="price[]" class="form-control price" value="{{ $item->price }}"></td>
                                        <td>
                                        <div class="discounts-wrapper">
                                            @php
                                                $discounts = $item->discounts ?? collect();
                                            @endphp

                                            @foreach($discounts as $discount)
                                                <div class="discount-row d-flex align-items-center gap-2 mb-2">
                                                    <select name="dis[{{ $loop->parent->index }}][]" class="form-control dis">
                                                        <option value="">---Select Discount---</option>
                                                        @foreach($taxes as $tax)
                                                            <option value="{{ $tax->name }}" {{ $discount->discount_value == $tax->name ? 'selected' : '' }}>
                                                                {{ $tax->name }} %
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-danger btn-sm remove-discount">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                </div>
                                            @endforeach

                                            {{-- Add new discount button --}}
                                            <button type="button" class="btn btn-success btn-sm add-discount">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                        <td><input type="number" step="0.01" name="amount[]" class="form-control amount" value="{{ $item->amount }}" readonly></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm remove"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="5" class="text-end">Tax / Discount</th>
                                    <td colspan="3"><input type="text" id="discount" name="discount_value" class="form-control" value="{{ $invoice->discount_value }}"></td>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-end">Shipping</th>
                                    <td colspan="3"><input type="number" id="shipping" name="shipping_fee" class="form-control" value="{{ $invoice->shipping_fee }}"></td>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-end">Other Charges</th>
                                    <td colspan="3"><input type="number" id="other" name="other_charges" class="form-control" value="{{ $invoice->other_charges }}"></td>
                                </tr>
                                <tr class="fw-bold">
                                    <th colspan="5" class="text-end">Subtotal</th>
                                    <td colspan="3"><input type="text" id="subtotal" name="subtotal" class="form-control" value="{{ $invoice->subtotal }}" readonly></td>
                                </tr>
                                <tr class="fw-bold bg-secondary text-white">
                                    <th colspan="5" class="text-end">Grand Total</th>
                                    <td colspan="3"><input type="text" id="grand_total" name="grand_total" class="form-control" value="{{ $invoice->grand_total }}" readonly></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Remarks --}}
                    <div class="form-group mb-4">
                        <label class="form-label">Comments / Special Instructions</label>
                        <textarea name="remarks" rows="3" class="form-control">{{ $invoice->remarks }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Update Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Discount Approval Modal --}}
    <div id="discountApprovalModalUpdate" class="modal fade" tabindex="-1" role="dialog">
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
</main>
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
                        <div class="discount-row mb-2">
                            <select name="dis[${rowIndex}][]" class="form-control dis">
                                <option value="0">---Select Discount---</option>
                                @foreach($taxes as $tax)
                                    <option value="{{$tax->name}}">{{$tax->name}} %</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-success btn-sm add-discount" disabled>
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </td>
                    <td><input type="text" name="amount[]" class="form-control amount" readonly></td>
                    <td><a class="btn btn-danger remove"><i class="fa fa-trash"></i></a></td>
                </tr>`;

                $('#po-body').append(newRow);
                // Re-initialize Select2 for the new row
                $('#po-body tr:last .productname').select2({
                    placeholder: "Select Product",
                    allowClear: true,
                    width: '400px'
                });
                toggleDiscountControls(); // <- ensure correct button state when new row is added
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
                var productName = selected.val() ? selected.text().trim() : ''; 
                var stock = selected.data('stock') || 0;
                var unitId = selected.data('unit') || '';

                $row.find('.code').val(selected.data('code') || '');
                $row.find('.price').val(selected.data('price') || '');
                $row.find('.qty').val('');
                $row.find('.available-stock').text("Available: " + stock);

                // set unit dropdown automatically
                if (unitId) {
                    $row.find('.unit').val(unitId);
                }
                // store original stock in input
                $row.find('.qty').data('original-stock', stock);
                var productSelected = $(this).val();
                if (productSelected) {
                    // enable discount field only if a product is chosen
                    $row.find('.dis').prop('disabled', false);
                } else {
                    // disable again if product removed
                    $row.find('.dis').prop('disabled', true).val(0);
                    calculateTotals();
                }
                // Show selected product name below dropdown
                $row.find('.selected-product-info').text(productName);
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
                var originalStock = parseInt($row.find('.qty').data('original-stock')) || 0;
                var enteredQty = parseInt($(this).val()) || 0;

                if (enteredQty > originalStock) {
                    alert("Quantity exceeds available stock!");
                    $(this).val(originalStock);
                    enteredQty = originalStock;
                }

                // Update displayed available stock
                var remainingStock = originalStock - enteredQty;
                $row.find('.available-stock').text("Available: " + remainingStock);

                calculateTotals(); // recalc totals after change
            });
            
            // get the latest Invoice number in database
            let randomInvoice = 'DR-' + Math.floor(100000 + Math.random() * 900000);
            $('#invoice_number').val(randomInvoice);
        });

        let formPendingSubmit = null;
        $('form').on('submit', function(e) {
            e.preventDefault(); // always prevent immediate submit

            // --- keep your hidden fields update here ---
            const type = $('#discount_type').val();
            $('#hidden_discount_type').val(type);
            $('#hidden_overall_discount').val(type === 'overall' ? ($('#discount').val() || 0) : 0);
            $('#hidden_subtotal').val($('#subtotal').val() || 0);
            $('#hidden_shipping').val($('#shipping').val() || 0);
            $('#hidden_other').val($('#other').val() || 0);
            $('#hidden_grand_total').val($('#grand_total').val() || 0);

            // Check if any discount exists
            const hasPerItemDiscount = $('.dis').toArray().some(inp => parseFloat($(inp).val()) > 0);
            const overallDiscount = parseFloat($('#discount').val()) || 0;

            // if (hasPerItemDiscount || overallDiscount > 0) {
            //     formPendingSubmit = this; // store form for later submission
            //     $("#adminPassword").val("");
            //     $("#passwordError").addClass("d-none");
            //     discountModal.show(); // <-- show modal here
            // } else {
                this.submit(); // no discount → submit immediately
            // }
        });

        // Disable all discount fields by default
        $('.dis, #discount').prop('disabled', true);

        $('#discount_type').trigger('change');

            let discountApprovalCount = 0;
            let pendingDiscountInput = null;

            var discountModal = new bootstrap.Modal(document.getElementById('discountApprovalModalUpdate'), {
                backdrop: 'static',
                keyboard: false
            });

            /*$(document).on("input", ".dis, #discount", function() {
                var val = parseFloat($(this).val()) || 0;

                if (val > 0) {
                    if (discountApprovalCount < 3) {
                        pendingDiscountInput = $(this); // remember which input triggered
                        if ($('#discountApprovalModalUpdate').is(':hidden')) { // only show if hidden
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

        $(document).on('click', '.add-discount', function () {
            const wrapper = $(this).closest('.discounts-wrapper');
            const index = wrapper.closest('tr').index();
            const newRow = `
                <div class="discount-row mb-2">
                    <select name="dis[${index}][]" class="form-control dis">
                        <option value="">---Select Discount---</option>
                        @foreach($taxes as $tax)
                            <option value="{{ $tax->name }}">{{ $tax->name }} %</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-danger btn-sm remove-discount mt-1">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            `;
            $(this).before(newRow);
        });
        $('.productname').each(function() {
            var $row = $(this).closest('tr');
            var selected = $(this).find(':selected');
            var productName = selected.val() ? selected.text().trim() : '';
            $row.find('.selected-product-info').text(productName);

            // Optional: set stock and unit if needed
            $row.find('.available-stock').text("Available: " + (selected.data('stock') || 0));
            if (selected.data('unit')) {
                $row.find('.unit').val(selected.data('unit'));
            }
        });

        // Remove a discount row
        $(document).on('click', '.remove-discount', function () {
            $(this).closest('.discount-row').remove();
            calculateTotals();
        });

        function toggleDiscountControls() {
            const discountType = $('#discount_type').val();

            if (discountType === '') {
                // disable all discount fields & add buttons if type not selected
                $('.dis').prop('disabled', true);
                $('.add-discount').prop('disabled', true);
            } else if (discountType === 'overall') {
                // disable per-item discounts if overall discount
                $('.dis').prop('disabled', true);
                $('.add-discount').prop('disabled', true);
            } else if (discountType === 'per_item') {
                // enable per-item discounts
                $('.dis').prop('disabled', false);
                $('.add-discount').prop('disabled', false);
            }
        }
        function calculateTotals() {
            let subtotal = 0;

            // 1. Loop through line items (apply per-item discounts first)
            $('#po-body tr').each(function() {
                let qty   = parseFloat($(this).find('.qty').val()) || 0;
                let price = parseFloat($(this).find('.price').val()) || 0;
                let dis   = parseFloat($(this).find('.dis').val()) || 0; // per-item %

                let lineTotal = price * qty;

                if (dis > 0) {
                    lineTotal -= (lineTotal * dis / 100);
                }

                $(this).find('.amount').val(lineTotal.toFixed(2));
                subtotal += lineTotal;
            });

            // 2. Apply overall discount
            let overallType  = $('#discount_type').val(); // "overall", "fixed", or ""
            let overallValue = parseFloat($('#discount').val()) || 0;
            let discountAmount = 0;

            if (overallValue > 0) {
                if (overallType === 'overall') {
                    // percentage discount
                    discountAmount = subtotal * (overallValue / 100);
                } else if (overallType === 'per_item') {
                    // fixed peso discount
                    discountAmount = overallValue;
                } else {
                    // no type selected → default to percentage
                    discountAmount = subtotal * (overallValue / 100);
                }
            }

            let afterDiscount = subtotal - discountAmount;

            // 3. Add shipping and other charges
            let shipping = parseFloat($('#shipping').val()) || 0;
            let other    = parseFloat($('#other').val()) || 0;

            let grandTotal = afterDiscount + shipping + other;

            // 4. Update fields
            $('#subtotal').val(subtotal.toFixed(2));
            $('#hidden_overall_discount').val(discountAmount.toFixed(2));
            $('#grand_total').val(grandTotal.toFixed(2));
        }

        $(document).on('input change', '.qty, .price, .dis, #discount, #shipping, #other, #discount_type', calculateTotals);

        $('#discount_type').on('change', function() {
            const type = $(this).val();

            if (type === 'overall') {
                $('#discount').prop('disabled', false);
                $('.dis').prop('disabled', true).val(0);
            } else if (type === 'per_item') {
                $('#discount').prop('disabled', true).val(0);
                $('.dis').prop('disabled', false);
            } else {
                // No type selected
                $('#discount').prop('disabled', true).val(0);
                $('.dis').prop('disabled', true).val(0);
            }

            toggleDiscountControls(); // 👈 ensure button states update properly
            calculateTotals();
        });
                
    </script>

@endpush