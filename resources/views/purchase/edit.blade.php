@extends('layouts.master')

@section('title', 'Purchase | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-edit"></i> Edit Purchase</h1>
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
                                <input type="text" name="salesman" id="salesman" 
                                    class="form-control" value="{{ old('salesman', $purchase->salesman) }}">
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
                        <div id="supplier-info" class="table-responsive mb-4" style="display: block;">
                            <table class="table table-bordered table-striped shadow-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th colspan="2" class="bg-primary text-white">
                                            <i class="fa fa-building"></i> Supplier Information
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><th>Supplier Code</th><td>{{ $purchase->supplier->supplier_code }}</td></tr>
                                    <tr><th>Name</th><td>{{ $purchase->supplier->name }}</td></tr>
                                    <tr><th>Phone</th><td>{{ $purchase->supplier->mobile }}</td></tr>
                                    <tr><th>Email</th><td>{{ $purchase->supplier->email }}</td></tr>
                                    <tr><th>Address</th><td>{{ $purchase->supplier->address }}</td></tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Items --}}
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
                                                    {{ $supplierItem->id == $item->supplier_item_id ? 'selected' : '' }}>
                                                    {{ $supplierItem->item_description }}
                                                </option>
                                            @endforeach
                                        </select>
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
                            <tfoot>
                                {{-- same as your create.blade.php --}}
                                <tr>
                                    <th colspan="5" class="text-right">Discount Type</th>
                                    <th colspan="2">
                                        <select id="discount_type" name="discount_type" class="form-control">
                                            <option value="per_item" {{ $purchase->discount_type == 'per_item' ? 'selected' : '' }}>Per Item</option>
                                            <option value="overall" {{ $purchase->discount_type == 'overall' ? 'selected' : '' }}>Overall</option>
                                        </select>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-right">Tax/Discount</th>
                                    <td colspan="2"><input type="text" class="form-control" name="discount_value" id="discount" value="{{ $purchase->overall_discount }}"></td>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-right">Shipping</th>
                                    <td colspan="2"><input type="number" class="form-control" name="shipping" id="shipping" value="{{ $purchase->shipping }}"></td>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-right">Other Charges</th>
                                    <td colspan="2"><input type="number" class="form-control" name="other_charges" id="other" value="{{ $purchase->other_charges }}"></td>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-right">Subtotal</th>
                                    <td colspan="2"><input type="text" class="form-control" name="subtotal" id="subtotal" value="{{ $purchase->subtotal }}" readonly></td>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-right">Grand Total</th>
                                    <td colspan="2"><input type="text" class="form-control" name="grand_total" id="grand_total" value="{{ $purchase->grand_total }}" readonly></td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="form-group mt-3">
                            <label>Comments or Special Instructions</label>
                            <textarea name="remarks" rows="4" class="form-control">{{ $purchase->remarks }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Update Purchase Order</button>
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
    // Add new row dynamically
    $('.addRow').on('click', function() {
        if (!supplierItems || supplierItems === 0) {
            alert('Please select a supplier first.');
            return;
        }
        addRow(supplierItems);
        calculateTotals();
    });

    function addRow(supplierItems = []) {
        let options = '<option value="">Select Product</option>';
        supplierItems.forEach(function(item){
            options += `<option value="${item.id}" data-code="${item.item_code}" data-price="${item.item_price}">
                           ${item.item_description}
                        </option>`;
        });

        const addRow = `<tr>
            <td><input type="text" name="product_code[]" class="form-control code" readonly></td>
            <td>
                <select name="product_id[]" class="form-control productname">${options}</select>
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

        $('#po-body').append(addRow);
        if ($('#discount_type').val() === 'overall') {
            $('#po-body tr:last').find('.dis').prop('disabled', true);
        }
    }

    // Remove row
    $(document).on('click', '.remove', function () {
        var l = $('tbody tr').length;
        if(l==1){
            alert('you can\'t delete the last row');
            calculateTotals();
        }
        else{
            $(this).parent().parent().remove();
            calculateTotals();
        }
    });

    // Change product
    $(document).on('change', '.productname', function () {
        var $row = $(this).closest('tr');
        var selected = $(this).find(':selected');
        $row.find('.code').val(selected.data('code') || '');
        $row.find('.price').val(selected.data('price') || '');
        calculateTotals();
    });

    // Calculate totals on input
    $(document).on('input', '.qty, .price, .dis, #discount, #shipping, #other', function() {
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
        }
        calculateTotals();
    });

    // initial calculation
    calculateTotals();

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

            if (discountType === 'per_item' && disP > 0) {
                const lineDiscAmt = lineBase * disP / 100;
                totalDiscount += lineDiscAmt;
                lineNet = lineBase - lineDiscAmt;
            }
            $(this).find('.amount').val(lineNet.toFixed(2));
        });

        if (discountType === 'overall') {
            const overallPct = parseFloat($('#discount').val()) || 0;
            totalDiscount = baseSubtotal * overallPct / 100;
        }

        const shipping = parseFloat($('#shipping').val()) || 0;
        const other    = parseFloat($('#other').val())    || 0;
        const grandTotal  = (baseSubtotal - totalDiscount) + shipping + other;

        $('#subtotal').val(baseSubtotal.toFixed(2));
        $('#grand_total').val(grandTotal.toFixed(2));

        // update hidden fields
        $('#hidden_subtotal').val(baseSubtotal.toFixed(2));
        $('#hidden_discount_value').val(totalDiscount.toFixed(2));
        $('#hidden_grand_total').val(grandTotal.toFixed(2));
    }
});
</script>
@endpush
