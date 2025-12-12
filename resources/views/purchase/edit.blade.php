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

                        {{-- Supplier & Purchase Info --}}
                        <div class="row mb-4">
                            <div class="col-md-4 form-group">
                                <label>Supplier</label>
                                <select name="supplier_id" id="supplierSelect" class="form-control form-control-sm" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ $supplier->id == $purchase->supplier_id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Purchase Date</label>
                                <input type="date" name="date" class="form-control form-control-sm" value="{{ old('date', $purchase->date) }}" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>PO Number</label>
                                <input type="text" name="po_number" class="form-control form-control-sm" value="{{ $purchase->po_number }}" readonly>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Salesman</label>
                                <select name="salesman_id" class="form-control form-control-sm" required>
                                    <option value="">-- Select Salesman --</option>
                                    @foreach($salesman as $salesmen)
                                        <option value="{{ $salesmen->id }}" {{ $salesmen->id == $purchase->salesman_id ? 'selected' : '' }}>
                                            {{ $salesmen->salesman_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Mode of Payment</label>
                                <select name="payment_id" class="form-control form-control-sm" required>
                                    <option value="">-- Select Payment Mode --</option>
                                    @foreach($paymentModes as $mode)
                                        <option value="{{ $mode->id }}" {{ $mode->id == $purchase->payment_id ? 'selected' : '' }}>
                                            {{ $mode->name }} @if($mode->term) ({{ $mode->term }} days) @endif
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

                        {{-- Purchased Items --}}
                        <div class="tile mt-4">
                            <h4 class="tile-title"><i class="fa fa-list"></i> Purchased Items</h4>
                            <table class="table table-bordered align-middle">
                                <thead class="bg-dark text-white text-center">
                                    <tr>
                                        <th style="width: 20%">Product</th>
                                        <th style="width: 8%">Unit</th>
                                        <th style="width: 8%">Qty</th>
                                        <th style="width: 15%">Discounts</th>
                                        <th style="width: 12%">Unit Cost</th>
                                        <th style="width: 12%">Total Cost</th>
                                        <th style="width: 5%">
                                            <button type="button" class="btn btn-success btn-sm addRow"><i class="fa fa-plus"></i></button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="po-body">
                                    {{-- JS will populate existing items --}}
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Discount Type</th>
                                        <th colspan="2">
                                            <select id="discount_type" name="discount_type" class="form-control form-control-sm">
                                                <option value="all" {{ $purchase->discount_type == 'all' ? 'selected' : '' }}>All</option>
                                                <option value="overall" {{ $purchase->discount_type == 'overall' ? 'selected' : '' }}>Overall</option>
                                                <option value="per_item" {{ $purchase->discount_type == 'per_item' ? 'selected' : '' }}>Per Item</option>
                                            </select>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Tax/Discount</th>
                                        <td colspan="2"><input type="text" class="form-control form-control-sm" name="discount_value" id="discount" value="{{ $purchase->discount_value }}"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Shipping</th>
                                        <td colspan="2"><input type="number" class="form-control form-control-sm" name="shipping" id="shipping" value="{{ $purchase->shipping }}"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Other Charges</th>
                                        <td colspan="2"><input type="number" class="form-control form-control-sm" name="other_charges" id="other" value="{{ $purchase->other_charges }}"></td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <th colspan="5" class="text-end">Subtotal</th>
                                        <td colspan="2"><input type="text" class="form-control form-control-sm" name="subtotal" id="subtotal" value="{{ $purchase->subtotal }}" readonly></td>
                                    </tr>
                                    <tr class="fw-bold bg-secondary text-white">
                                        <th colspan="5" class="text-end">Grand Total</th>
                                        <td colspan="2"><input type="text" class="form-control form-control-sm" name="grand_total" id="grand_total" value="{{ $purchase->grand_total }}" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Remarks --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Comments / Special Instructions</label>
                            <textarea name="remarks" rows="3" class="form-control form-control-sm">{{ $purchase->remarks }}</textarea>
                        </div>

                        {{-- Submit --}}
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Update Purchase Order</button>
                            <a href="{{ route('purchase.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Cancel</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const units = @json($units);
const taxes = @json($taxesArray);
const purchaseItems = @json($purchaseItemsArray);
const supplierItems = @json($supplierItems);

$(document).ready(function(){
    $('.productname').select2({
        placeholder: "Select Product",
        allowClear: true,
        width: '400px'
    });
    $('#supplier_id').select2({
        placeholder: "Select Customer",
        allowClear: true,
        width: 'resolve'
    });
    // Initialize Select2
    // function initSelect2() {
    //     $('.purchaseproduct').select2({placeholder: "Select Product", allowClear:true, width:'resolve'});
    // }

    // Add row function
    function addRow(supplierItems = [], existingItem = null) {
        let options = '<option value="">Select Product</option>';
        supplierItems.forEach(item => {
            const selected = existingItem && existingItem.product_id == item.id ? 'selected' : '';
            console.log("test id", existingItem.product_id,item.id);
            options += `<option value="${item.id}" data-code="${item.item_code}" data-price="${item.item_price}" data-name="${item.item_description}" ${selected}>${item.item_description}</option>`;
        });

        const unitOptions = units.map(u => {
            const selected = existingItem && existingItem.unit_id == u.id ? 'selected' : '';
            return `<option value="${u.id}" ${selected}>${u.name}</option>`;
        }).join('');

        const disLessAdd = existingItem?.discount_less_add || 'less';
        const dis1 = existingItem?.dis1 || 0;
        const dis2 = existingItem?.dis2 || 0;
        const dis3 = existingItem?.dis3 || 0;

        const discountOptions = taxes.map(t => `<option value="${t.name}" ${t.name == dis1 ? 'selected':''}>${t.name}%</option>`).join('');
        const discount2Options = taxes.map(t => `<option value="${t.name}" ${t.name == dis2 ? 'selected':''}>${t.name}%</option>`).join('');
        const discount3Options = taxes.map(t => `<option value="${t.name}" ${t.name == dis3 ? 'selected':''}>${t.name}%</option>`).join('');

        const newRow = `<tr>
            <td>
                <input type="hidden" name="product_code[]" class="form-control code" value="${existingItem?.product_code || ''}">
                <select name="product_id[]" class="form-control form-control-sm" purchaseproduct">
                    ${options}
                </select>
                <small class="text-muted selected-product-name">${existingItem?.product_name || ''}</small>
            </td>
            <td><select name="unit[]" class="form-control form-control-sm"">${unitOptions}</select></td>
            <td><input type="number" name="qty[]" class="form-control form-control-sm qty" value="${existingItem?.qty || ''}" placeholder="0"></td>
            <td>
                <div class="row g-1">
                    <div class="col-8">
                        <select name="discount_less_add[]" class="form-control form-control-sm"">
                            <option value="less" ${disLessAdd=='less'?'selected':''}>Less (-)</option>
                            <option value="add" ${disLessAdd=='add'?'selected':''}>Add (+)</option>
                        </select>
                    </div>
                    <div class="col-8">
                        <select name="dis1[]" class="form-control form-control-sm""><option value="0">Discount 1 (%)</option>${discountOptions}</select>
                    </div>
                    <div class="col-8">
                        <select name="dis2[]" class="form-control form-control-sm""><option value="0">Discount 2 (%)</option>${discount2Options}</select>
                    </div>
                    <div class="col-8">
                        <select name="dis3[]" class="form-control form-control-sm" mt-1"><option value="0">Discount 3 (%)</option>${discount3Options}</select>
                    </div>
                </div>
            </td>
            <td><input type="number" step="0.01" name="price[]" class="form-control form-control-sm price" value="${existingItem?.unit_price || ''}" placeholder="0.00"></td>
            <td><input type="number" step="0.01" name="amount[]" class="form-control form-control-sm amount" value="${existingItem?.amount || ''}" readonly></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove"><i class="fa fa-trash"></i></button></td>
        </tr>`;

        $('#po-body').append(newRow);
        let $select = $('#po-body tr:last').find('.purchaseproduct');
        $select.select2({
            placeholder: "Select Product",
            allowClear: true,
            width: '500px'
        });
        if(existingItem) {
            $select.val(existingItem.product_id).trigger('change');
        }

       // initSelect2();
        calculateTotals();
    }

    // Populate existing items
    purchaseItems.forEach(item => addRow(supplierItems, item));

    // Add new empty row
    $(document).on('click', '.addRow', function(){ addRow(supplierItems); });

    // Remove row
    $(document).on('click', '.remove', function(){
        if($('#po-body tr').length === 1){ alert("Cannot delete last row"); return; }
        $(this).closest('tr').remove();
        calculateTotals();
    });

    // Recalculate totals
    $(document).on('input change', '.qty, .price, [name="dis1[]"], [name="dis2[]"], [name="dis3[]"], [name="discount_less_add[]"], #discount, #shipping, #other', function(){
        calculateTotals();
    });

    function calculateTotals(){
        let subtotal = 0;
        $('#po-body tr').each(function(){
            let qty = parseFloat($(this).find('.qty').val())||0;
            let price = parseFloat($(this).find('.price').val())||0;
            let type = $(this).find('[name="discount_less_add[]"]').val();
            let dis1 = parseFloat($(this).find('[name="dis1[]"]').val())||0;
            let dis2 = parseFloat($(this).find('[name="dis2[]"]').val())||0;
            let dis3 = parseFloat($(this).find('[name="dis3[]"]').val())||0;

            let lineTotal = qty * price;
            [dis1, dis2, dis3].forEach(d => {
                if(d>0){
                    lineTotal = (type==='less') ? lineTotal*(1-d/100) : lineTotal*(1+d/100);
                }
            });
            $(this).find('.amount').val(lineTotal.toFixed(2));
            subtotal += lineTotal;
        });

        let overallDiscount = 0;
        if($('#discount_type').val()==='overall'){
            overallDiscount = subtotal * (parseFloat($('#discount').val())||0)/100;
        }

        let shipping = parseFloat($('#shipping').val())||0;
        let other = parseFloat($('#other').val())||0;

        $('#subtotal').val(subtotal.toFixed(2));
        $('#grand_total').val((subtotal - overallDiscount + shipping + other).toFixed(2));
    }

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

    calculateTotals();
    updateDiscountFieldState();
    $('.productname').each(function() {
        updateProductDisplay($(this));
    });


});
</script>
@endpush
