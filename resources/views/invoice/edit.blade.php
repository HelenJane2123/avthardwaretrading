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
                            <select id="customerSelect" name="customer_id" class="form-control form-control-sm" disabled>
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
                            <!-- Display -->
                            <input type="text"
                                id="invoice_date_display"
                                class="form-control form-control-sm"
                                value="{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('F d, Y') }}"
                                required>

                            <!-- Stored -->
                            <input type="hidden"
                                name="invoice_date"
                                id="invoice_date"
                                value="{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Invoice Due Date <span class="text-danger">*</span></label>
                            <!-- Display -->
                            <input type="text"
                                id="due_date_display"
                                class="form-control form-control-sm"
                                value="{{ \Carbon\Carbon::parse($invoice->due_date)->format('F d, Y') }}"
                                required>

                            <!-- Stored -->
                            <input type="hidden"
                                name="due_date"
                                id="due_date"
                                value="{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" name="invoice_number" class="form-control form-control-sm" 
                                value="{{ $invoice->invoice_number }}" readonly>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="salesman">Salesman</label>
                            <select name="salesman" id="salesman_id" class="form-control form-control-sm" required>
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
                            <select name="payment_mode_id" class="form-control form-control-sm" required>
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
                            <select id="discount_type" name="discount_type" class="form-control form-control-sm">
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
                                    <th style="width: 45%">Product</th>
                                    <th style="width: 8%">Unit</th>
                                    <th style="width: 8%">Qty</th>
                                    <th style="width: 20%">Discount (%)</th>
                                    <th style="width: 10%">Unit Price</th>
                                    <th style="width: 12%">Total Price</th>
                                    <th class="text-center">
                                        <button type="button" class="btn btn-success btn-sm addRow">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="po-body">
                                {{-- JS will populate existing items --}}
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
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Select Product</h5>
                <button type="button" class="btn btn-close" data-bs-dismiss="modal">x</button>
            </div>
            <div class="modal-body">
                <!-- <input type="text" id="productSearch" class="form-control mb-3" placeholder="Search product..."> -->
                <div class="mb-2">
                    <label for="filterSupplier" class="form-label">Filter by Supplier</label>
                    <select id="filterSupplier" class="form-control form-control-sm">
                        <option value="">-- All Suppliers --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="productTable">
                        <thead>
                            <tr>
                                <th>Product Code</th>
                                <th>Supplier Product Code</th>
                                <th>Supplier Name</th>
                                <th>Name</th>
                                <th>Unit Cost</th>
                                <th>Price</th>
                                <th>Quantity on Hand</th>
                                <th>Unit</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr data-id="{{ $product->id }}"
                                    data-code="{{ $product->product_code }}"
                                    data-name="{{ $product->product_name }}"
                                    data-price="{{ $product->sales_price }}"
                                    data-stock="{{ $product->remaining_stock }}"
                                    data-unit="{{ $product->unit_id }}"
                                    data-supplier="{{ optional($product->supplierItems->first())->supplier_id }}"
                                    data-discounttype="{{ $product->discount_type }}"
                                    data-discount1="{{ $product->discount_1 }}"
                                    data-discount2="{{ $product->discount_2 }}"
                                    data-discount3="{{ $product->discount_3 }}"
                                    data-baseprice="{{ optional($product->supplierItems->first())->item_price }}">
                                    <td>{{ $product->product_code }}</td>
                                    <td>{{ $product->supplier_product_code }}</td>
                                    <td>{{ optional($product->supplierItems->first())->supplier->name }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ optional($product->supplierItems->first())->item_price }}</td>
                                    <td>{{ $product->sales_price }}</td>
                                    <td>{{ $product->remaining_stock }}</td>
                                    <td>{{ $product->unit->name }}</td>
                                    <td>
                                    <button type="button" class="btn btn-success btn-sm select-this">Select</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('js')
@push('js')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script type="text/javascript">
    const invoiceItems = @json($invoice->items);
    const products = @json($products);
    const taxes = @json($taxes);
    const units = @json($units);

    console.log('Loaded taxes items:', taxes);

    $(document).ready(function(){
        function toYMD(date) {
        const d = new Date(date);
            return d.getFullYear() + '-' +
                String(d.getMonth() + 1).padStart(2, '0') + '-' +
                String(d.getDate()).padStart(2, '0');
        }

        $('#invoice_date_display').datepicker({
            dateFormat: 'MM dd, yy',
            onSelect: function(dateText) {
                $('#invoice_date').val(toYMD(dateText));
            }
        });

        $('#due_date_display').datepicker({
            dateFormat: 'MM dd, yy',
            onSelect: function(dateText) {
                $('#due_date').val(toYMD(dateText));
            }
        });
        let currentRow = null;
        let productTable = null;

        // Initialize select2 for customer
        $('#customerSelect').select2({
            placeholder: "Select Customer",
            allowClear: true,
            width: 'resolve'
        });

        // Load existing invoice items
        loadInvoiceItems();

        // Open modal when search button clicked
        $(document).on('click', '.select-product-btn', function() {
            currentRow = $(this).closest('tr'); // remember which row opened the modal
            $('#productModal').modal('show');

            if (!productTable) {
                productTable = $('#productTable').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    searching: true,
                    ordering: true,
                    info: false,
                    autoWidth: false
                });
            }

            $('#filterSupplier').on('change', function () {
                let supplierId = $(this).val();
                
                // Use column().search() if supplier is a column, or use a custom filter
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        if (!supplierId) return true; // show all if no filter
                        let rowSupplier = $('#productTable').find('tr:eq(' + (dataIndex + 1) + ')').data('supplier');
                        return rowSupplier == supplierId;
                    }
                );
                productTable.draw();
                $.fn.dataTable.ext.search.pop(); // remove after draw to avoid stacking filters
            });
        });

        // When selecting a product from modal
        $(document).on('click', '.select-this', function() {
            let tr = $(this).closest('tr');
            let id = tr.data('id');
            let code = tr.data('code');
            let name = tr.data('name');
            let price = tr.data('price');
            let stock = tr.data('stock');
            let unit = tr.data('unit');
            let basePrice = tr.data('baseprice');

            let duplicate = false;
            $('input.product_id').each(function() {
                if ($(this).val() == id && this !== currentRow.find('.product_id')[0]) {
                    duplicate = true;
                }
            });

            if (duplicate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Duplicate Product',
                    text: 'This product has already been selected.',
                    confirmButtonColor: '#ff9f43',
                });
                return;
            }

            // Update row fields
            currentRow.find('.productname').val(name);
            currentRow.find('.product_id').val(id);
            currentRow.find('.code').val(code);
            currentRow.find('.price').val(price);
            currentRow.find('.qty')
                .val('')
                .prop('readonly', false)
                .data('original-stock', stock); 
            currentRow.find('.available-stock').text("Available: " + stock);
            currentRow.find('.unit').val(unit);
            currentRow.find('.selected-product-info').text(name);
            currentRow.find('.show-base-price').html("Unit Cost: " + basePrice);

            // Update available stock display
            currentRow.find('.available-stock').text("Available: " + stock);

            $('#productModal').modal('hide');
            calculateTotals();
        });

        // Add new row
        $(document).on('click', '.addRow', function() {
            $("#po-body").append(generateRow());
        });

        // Remove row
        $(document).on('click', '.remove', function () {
            let rowCount = $('#po-body tr').length;
            if (rowCount === 1) {
                alert("You can't delete the last row");
            } else {
                $(this).closest('tr').remove();
                calculateTotals();
            }
        });
        $('#productModal .btn-close').on('click', function() {
            $('#productModal').modal('hide'); // jQuery fallback
        });
        // Quantity input validation
        $(document).on('input', '.qty', function () {
            let $row = $(this).closest('tr');
            let originalStock = parseInt($row.find('.qty').data('original-stock')) || 0;
            let enteredQty = parseInt($(this).val()) || 0;

            if (enteredQty > originalStock) {
                Swal.fire({
                    icon: "warning",
                    title: "Insufficient Stock",
                    text: "Quantity exceeds the available stock!",
                    confirmButtonColor: "#ff9f43",
                });

                $(this).val(originalStock);
                enteredQty = originalStock;
            }

            let remainingStock = originalStock - enteredQty;
            $row.find('.available-stock').text("Available: " + remainingStock);
            calculateTotals();
        });

        // Calculate totals when price, qty, or discounts change
        $(document).on('input change', '.qty, .price, select[name="discount_less_add[]"], select[name="dis1[]"], select[name="dis2[]"], select[name="dis3[]"]', function() {
            calculateTotals();
        });

        // Discount type change
        $('#discount_type').on('change', function() {
            toggleDiscountControls();
            calculateTotals();
        });

        function toggleDiscountControls() {
            const type = $('#discount_type').val();
            if (type === 'overall') {
                $('#discount').prop('disabled', false);
                $('.dis').prop('disabled', true).val(0);
            } else if (type === 'per_item') {
                $('#discount').prop('disabled', true).val(0);
                $('.dis').prop('disabled', false);
            } else {
                $('#discount').prop('disabled', true).val(0);
                $('.dis').prop('disabled', true).val(0);
            }
        }

        function calculateTotals() {
            let subtotal = 0;

            $('#po-body tr').each(function() {
                const $row = $(this);
                const qty = parseFloat($row.find('.qty').val()) || 0;
                let price = parseFloat($row.find('.price').val()) || 0;
                let lineTotal = qty * price;

                // Per-item discount type
                const discountType = $row.find('select[name="discount_less_add[]"]').val() || 'less';

                // Apply per-item discounts
                $row.find('.dis').each(function() {
                    const d = parseFloat($(this).val()) || 0;
                    if (discountType === 'less') lineTotal -= (lineTotal * d / 100);
                    else lineTotal += (lineTotal * d / 100);
                });

                $row.find('.amount').val(lineTotal.toFixed(2));
                subtotal += lineTotal;
            });

            // Overall discount
            const overallType = $('#discount_type').val();
            let overallDis = parseFloat($('#discount').val()) || 0;
            if (overallType === 'overall' && overallDis > 0) {
                subtotal = subtotal - (subtotal * overallDis / 100);
            }

            const shipping = parseFloat($('#shipping').val()) || 0;
            const other = parseFloat($('#other').val()) || 0;
            const grandTotal = subtotal + shipping + other;

            $('#subtotal').val(subtotal.toFixed(2));
            $('#grand_total').val(grandTotal.toFixed(2));
        }

        function loadInvoiceItems() {
            if (!invoiceItems || invoiceItems.length === 0) return;
            invoiceItems.forEach(item => $("#po-body").append(generateRow(item)));
            calculateTotals();
        }

        function generateRow(item = null) {
            const productId = item?.product_id || '';
            const productCode = item?.product?.product_code || '';
            const productName = item?.product?.product_name || '';
            const qty = item?.qty || '';
            const price = item?.price || '';
            const amount = item?.amount || '';
            const disLessAdd = item?.discount_less_add || 'less';
            const dis1 = item?.discount_1 || 0;
            const dis2 = item?.discount_2 || 0;
            const dis3 = item?.discount_3 || 0;
            const unitId = item?.unit_id || '';
            const stock = item?.product?.remaining_stock || 0;

            const unitOptions = units.map(u => 
                `<option value="${u.id}" ${u.id == unitId ? 'selected' : ''}>${u.name}</option>`
            ).join('');

            const discountOptions = (val) => taxes.map(t => 
                `<option value="${t.name}" ${Number(t.name) === Number(val) ? 'selected':''}>${t.name}%</option>`
            ).join('');

            return `
            <tr>
                <td style="width:400px;">
                    <div class="input-group">
                        <input type="hidden" name="product_id[]" class="product_id" value="${productId}">
                        <input type="hidden" class="form-control code" value="${productCode}" readonly>
                        <input type="text" class="form-control productname" value="${productName}" readonly>
                        <button type="button" class="btn btn-outline-primary select-product-btn">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                    <div class="text-muted small selected-product-info mt-1">${productName}</div>
                </td>
                <td><select name="unit[]" class="form-control form-control-sm">${unitOptions}</select></td>
                <td>
                    <input type="number" name="qty[]" class="form-control qty" value="${qty}" ${productName ? '' : 'readonly'} data-original-stock="${stock}">
                    <small class="text-muted available-stock">Available: ${stock}</small>
                </td>
                <td>
                    <div class="row g-1">
                        <div class="col-12 mb-1">
                            <select name="discount_less_add[]" class="form-control form-control-sm dis">
                                <option value="less" ${disLessAdd=='less'?'selected':''}>Less (-)</option>
                                <option value="add" ${disLessAdd=='add'?'selected':''}>Add (+)</option>
                            </select>
                        </div>
                        <div class="col-12 mb-1">
                            <select name="dis1[]" class="form-control form-control-sm dis">
                                <option value="0">Discount 1 (%)</option>
                                ${discountOptions(dis1)}
                            </select>
                        </div>
                        <div class="col-12 mb-1">
                            <select name="dis2[]" class="form-control form-control-sm dis">
                                <option value="0">Discount 2 (%)</option>
                                ${discountOptions(dis2)}
                            </select>
                        </div>
                        <div class="col-12">
                            <select name="dis3[]" class="form-control form-control-sm dis">
                                <option value="0">Discount 3 (%)</option>
                                ${discountOptions(dis3)}
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="text" name="price[]" class="form-control price" value="${price}">
                </td>
                <td><input type="text" name="amount[]" class="form-control amount" value="${amount}" readonly></td>
                <td>
                    <button type="button" class="btn btn-danger remove">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        }

    });
</script>
@endpush


@endpush