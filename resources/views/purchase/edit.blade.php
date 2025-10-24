@extends('layouts.master')

@section('title', 'Purchase | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-edit"></i> Edit Purchase</h1>
            <p class="text-muted mb-0">Update supplier details or modify items in the purchase order.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Purchase</li>
            <li class="breadcrumb-item"><a href="#">Edit Purchase</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <h3 class="tile-title">Edit Purchase Order</h3>
                <div class="tile-body">
                    <form method="POST" action="{{ route('purchase.update', $purchase->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            {{-- Supplier --}}
                            <div class="col-md-4 form-group">
                                <label>Supplier</label>
                                <select name="supplier_id" id="supplierSelect" class="form-control" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                            {{ $supplier->id == $purchase->supplier_id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Purchase Date --}}
                            <div class="col-md-3 form-group">
                                <label>Purchase Date</label>
                                <input type="date" name="date" class="form-control" 
                                    value="{{ old('date', $purchase->date) }}" required>
                            </div>

                            {{-- PO Number --}}
                            <div class="col-md-3 form-group">
                                <label for="po_number">PO Number</label>
                                <input type="text" name="po_number" id="po_number" 
                                    class="form-control" value="{{ $purchase->po_number }}" readonly>
                            </div>
                             {{-- Salesman --}}
                            <div class="col-md-4 form-group">
                                <label for="salesman">Salesman</label>
                                <select name="salesman_id" id="salesman_id" class="form-control" required>
                                <option value="">-- Select Salesman --</option>
                                @foreach($salesman as $salesmen)
                                    <option value="{{ $salesmen->id }}" 
                                        {{ $salesmen->id == $purchase->salesman_id ? 'selected' : '' }}>
                                        {{ $salesmen->salesman_name }} 
                                    </option>
                                @endforeach
                            </select>
                            </div>

                            {{-- Payment Term --}}
                            <div class="form-group">
                                <label for="payment_id">Mode of Payment</label>
                                <select name="payment_id" id="payment_id" class="form-control" required>
                                    <option value="">-- Select Payment Mode --</option>
                                    @foreach($paymentModes as $mode)
                                        <option value="{{ $mode->id }}" 
                                            {{ $mode->id == $purchase->payment_id ? 'selected' : '' }}>
                                            {{ $mode->name }} 
                                            @if($mode->term) ({{ $mode->term }} days) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Supplier Info --}}
                        <div id="supplier-info" class="tile mt-3">
                            <h4 class="tile-title"><i class="fa fa-building"></i> Supplier Information</h4>
                            <div class="tile-body table-responsive">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr><th>Supplier Code</th><td>{{ $purchase->supplier->supplier_code }}</td></tr>
                                        <tr><th>Name</th><td>{{ $purchase->supplier->name }}</td></tr>
                                        <tr><th>Phone</th><td>{{ $purchase->supplier->mobile }}</td></tr>
                                        <tr><th>Email</th><td>{{ $purchase->supplier->email }}</td></tr>
                                        <tr><th>Address</th><td>{{ $purchase->supplier->address }}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Items --}}
                        <div class="tile mt-4">
                            <h4 class="tile-title"><i class="fa fa-list"></i> Purchased Items</h4>
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Product Code</th>
                                        <th>Product</th>
                                        <th>Unit</th>
                                        <th>Quantity Ordered</th>
                                        <th>Unit Price</th>
                                        <th>Discount (%)</th>
                                        <th>Amount</th>
                                        <th><a class="btn btn-success btn-sm addRow"><i class="fa fa-plus"></i></a></th>
                                    </tr>
                                </thead>
                                <tbody id="po-body">
                                    @foreach($purchase->items as $item)
                                    <tr>
                                        <td>
                                            <input type="text" name="product_code[]" class="form-control code" 
                                                value="{{ $item->supplierItem->item_code }}" readonly>
                                        </td>
                                        <td>
                                            <select name="product_id[]" class="form-control productname">
                                                <option value="">Select Product</option>
                                                @foreach($supplierItems as $supplierItem)
                                                    <option value="{{ $supplierItem->id }}"
                                                            data-code="{{ $supplierItem->item_code }}"
                                                            data-price="{{ $supplierItem->item_price }}"
                                                            data-name="{{ $supplierItem->item_description }}"
                                                            {{ $supplierItem->id == $item->supplier_item_id ? 'selected' : '' }}>
                                                        {{ $supplierItem->item_description }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted d-block product-display mt-1"></small>
                                        </td>
                                        <td>
                                            <select name="unit[]" class="form-control unit">
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}" 
                                                        {{ $unit->id == $item->unit ? 'selected' : '' }}>
                                                        {{ $unit->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="qty[]" class="form-control qty" value="{{ $item->qty }}"></td>
                                        <td><input type="text" name="price[]" class="form-control price" value="{{ $item->unit_price }}"></td>
                                        <td><input type="text" name="dis[]" class="form-control dis" value="{{ $item->discount }}"></td>
                                        <td><input type="text" name="amount[]" class="form-control amount" value="{{ $item->amount }}" readonly></td>
                                        <td><a class="btn btn-danger btn-sm remove"><i class="fa fa-remove"></i></a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    {{-- same as your create.blade.php --}}
                                    <tr>
                                        <th colspan="5" class="text-end">Discount Type</th>
                                        <th colspan="2">
                                            <select id="discount_type" name="discount_type" class="form-control">
                                                <option value="all" {{ $purchase->discount_type == 'all' ? 'selected' : '' }}>All</option>
                                                <option value="overall" {{ $purchase->discount_type == 'overall' ? 'selected' : '' }}>Overall</option>
                                                <option value="per_item" {{ $purchase->discount_type == 'per_item' ? 'selected' : '' }}>Per Item</option>
                                            </select>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Tax/Discount</th>
                                        <td colspan="2"><input type="text" class="form-control" name="discount_value" id="discount" value="{{ $purchase->discount_value }}"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Shipping</th>
                                        <td colspan="2"><input type="number" class="form-control" name="shipping" id="shipping" value="{{ $purchase->shipping }}"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Other Charges</th>
                                        <td colspan="2"><input type="number" class="form-control" name="other_charges" id="other" value="{{ $purchase->other_charges }}"></td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <th colspan="5" class="text-end">Subtotal</th>
                                        <td colspan="2"><input type="text" class="form-control" name="subtotal" id="subtotal" value="{{ $purchase->subtotal }}" readonly></td>
                                    </tr>
                                    <tr class="fw-bold bg-secondary text-white">
                                        <th colspan="5" class="text-end">Grand Total</th>
                                        <td colspan="2"><input type="text" class="form-control" name="grand_total" id="grand_total" value="{{ $purchase->grand_total }}" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        {{-- Remarks --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Comments / Special Instructions</label>
                            <textarea name="remarks" rows="3" class="form-control">{{ $purchase->remarks }}</textarea>
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> Update Purchase Order
                            </button>
                            <a href="{{ route('purchase.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Cancel
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script src="{{asset('/')}}js/multifield/jquery.multifield.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

    let supplierItems = @json($supplierItems);

    // Add Row
    $('.addRow').on('click', function() {
        if (!supplierItems || supplierItems.length === 0) {
            alert('Please select a supplier first.');
            return;
        }
        addRow(supplierItems);
        calculateTotals();
    });

    function addRow(supplierItems = []) {
        let options = '<option value="">Select Product</option>';
        supplierItems.forEach(function(item) {
            options += `<option value="${item.id}" 
                                data-code="${item.item_code}" 
                                data-price="${item.item_price}" 
                                data-name="${item.item_description}">
                            ${item.item_description}
                        </option>`;
        });

        const newRow = `<tr>
            <td><input type="text" name="product_code[]" class="form-control code" readonly></td>
            <td>
                <select name="product_id[]" class="form-control productname">${options}</select>
                <small class="text-muted d-block product-display mt-1"></small>
            </td>
            <td>
                <select name="unit[]" class="form-control unit">
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

        $('#po-body').append(newRow);
    }

    // Remove row
    $(document).on('click', '.remove', function () {
        var l = $('tbody tr').length;
        if(l==1){
            alert('You can\'t delete the last row');
            calculateTotals();
        } else {
            $(this).closest('tr').remove();
            calculateTotals();
        }
    });

    // Product change
    function updateDiscountFieldState() {
        const type = $('#discount_type').val();

        if (type === 'all') {
            // Enable both
            $('.dis').prop('disabled', false);
            $('#discount').prop('disabled', false);
        }
        else if (type === 'overall') {
            // Enable only overall discount
            $('.dis').prop('disabled', true);
            $('#discount').prop('disabled', false);
        } 
        else if (type === 'per_item') {
            // Enable only per-item discounts
            $('.dis').prop('disabled', false);
            $('#discount').prop('disabled', true).val('');
        } 
        calculateTotals();
    }

    // When user changes discount type
    $(document).on('change', '#discount_type', function() {
        updateDiscountFieldState();
    });

    function updateProductDisplay($select) {
        const selected = $select.find(':selected');
        const $row = $select.closest('tr');
        const name = selected.data('name') || '';
        const code = selected.data('code') || '';
        const price = selected.data('price') || '';

        $row.find('.product-display').text(name);
        $row.find('.code').val(code);
        $row.find('.price').val(price);

        calculateTotals();
    }

    $(document).on('change', '.productname', function() {
        updateProductDisplay($(this));
    });

    // Recalculate totals when any field changes
    $(document).on('input', '.qty, .price, .dis, #discount, #shipping, #other', function() {
        calculateTotals();
    });

    // Calculate Totals
    function calculateTotals() {
        let subtotal = 0;
        let perItemDiscountTotal = 0;

        const discountType = $('#discount_type').val();

        // 1️⃣ Calculate per-item totals if enabled (either "per_item" or "all")
        $('#po-body tr').each(function () {
            const qty   = parseFloat($(this).find('.qty').val())   || 0;
            const price = parseFloat($(this).find('.price').val()) || 0;
            const disP  = parseFloat($(this).find('.dis').val())   || 0;

            const lineBase = qty * price;
            let lineDisc = 0;

            if (discountType === 'per_item' || discountType === 'all') {
                lineDisc = (disP > 0) ? (lineBase * disP / 100) : 0;
            }

            const lineNet = lineBase - lineDisc;
            subtotal += lineBase;
            perItemDiscountTotal += lineDisc;

            $(this).find('.amount').val(lineNet.toFixed(2));
        });

        // 2️⃣ Apply overall discount if enabled (either "overall" or "all")
        let overallDiscount = 0;
        if (discountType === 'overall' || discountType === 'all') {
            const overallPct = parseFloat($('#discount').val()) || 0;
            const baseForOverall = (discountType === 'all') 
                ? (subtotal - perItemDiscountTotal) 
                : subtotal;
            overallDiscount = baseForOverall * overallPct / 100;
        }

        // 3️⃣ Add shipping & other charges
        const shipping = parseFloat($('#shipping').val()) || 0;
        const other    = parseFloat($('#other').val())    || 0;

        // 4️⃣ Compute grand total
        const grandTotal = (subtotal - perItemDiscountTotal - overallDiscount) + shipping + other;

        // 5️⃣ Update fields
        $('#subtotal').val(subtotal.toFixed(2));
        $('#grand_total').val(grandTotal.toFixed(2));
    }

    calculateTotals();
    updateDiscountFieldState();

    $('.productname').each(function() {
        updateProductDisplay($(this));
    });
});
</script>
@endpush
