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
                                <select id="customerSelect" name="customer_id" class="form-control form-control-sm">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                    <!-- Displayed to user -->
                                    <input type="text"
                                        id="invoice_date_display"
                                        class="form-control form-control-sm"
                                        value="{{ date('F d, Y') }}"
                                        required>

                                    <!-- Actual value submitted -->
                                    <input type="hidden"
                                        name="invoice_date"
                                        id="invoice_date"
                                        value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Invoice Due Date <span class="text-danger">*</span></label>
                                <input type="text"
                                    id="due_date_display"
                                    class="form-control form-control-sm"
                                    value="{{ date('F d, Y') }}"
                                    required>

                                <input type="hidden"
                                    name="due_date"
                                    id="due_date"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Invoice Number</label>
                                <input type="text"
                                    id="invoice_number"
                                    class="form-control form-control-sm"
                                    value="{{ $invoiceNumber ?? 'Auto-generated' }}"
                                    readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mode of Payment <span class="text-danger">*</span></label>
                                <select name="payment_mode_id" id="payment_id" class="form-control form-control-sm">
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
                                <label class="form-label">Salesman</label>
                               <select name="salesman" id="salesman_id" class="form-control form-control-sm">
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
                                        <th></th>
                                        <th style="width: 45%">Product</th>
                                        <th style="width: 8%">Unit</th>
                                        <th style="width: 8%">Qty</th>
                                        <th style="width: 20%">Discount (%)</th>
                                        <th style="width: 10%">Unit Price</th>
                                        <th style="width: 12%">Total Price</th>
                                        <!-- <th style="width: 45%">Is free?</th> -->
                                        <th class="text-center">
                                            <button type="button" class="btn btn-success btn-sm addRow">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="po-body">
                                    <tr>
                                        <td>1</td>
                                        <td>
                                            <input type="hidden" name="product_code[]" class="form-control code" readonly>
                                            <div class="input-group">
                                                <input type="text" name="product_name[]" class="form-control form-control-sm product-input productname" placeholder="Search Product" readonly>
                                                <input type="hidden" name="product_id[]" class="product_id">
                                                <button type="button" class="btn btn-outline-primary select-product-btn">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </div>
                                            <div class="text-muted small selected-product-info mt-1"></div>
                                        </td>
                                        <td>
                                            <select name="unit[]" class="form-control form-control-sm unit">
                                                <option value="">Select Unit</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="qty[]" class="form-control form-control-sm qty">
                                            <small class="text-muted available-stock"></small>
                                        </td>
                                        <td>
                                            <div class="row g-1">
                                                <!-- Discount Type -->
                                                <div class="col-8">
                                                    <select name="discount_less_add[]" class="form-control form-control-sm discount_type">
                                                        <option value="less">Less (-)</option>
                                                        <option value="add">Add (+)</option>
                                                    </select>
                                                </div>

                                                <!-- Discount 1 -->
                                                <div class="col-8">
                                                    <select name="dis1[]" class="form-control form-control-sm dis1">
                                                        <option value="0">Discount 1 (%)</option>
                                                        @foreach($taxes as $tax)
                                                            <option value="{{ $tax->name }}">{{ $tax->name }}%</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Discount 2 -->
                                                <div class="col-8">
                                                    <select name="dis2[]" class="form-control form-control-sm dis2">
                                                        <option value="0">Discount 2 (%)</option>
                                                        @foreach($taxes as $tax)
                                                            <option value="{{ $tax->name }}">{{ $tax->name }}%</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Discount 3 -->
                                                <div class="col-8">
                                                    <select name="dis3[]" class="form-control form-control-sm dis3">
                                                        <option value="0">Discount 3 (%)</option>
                                                        @foreach($taxes as $tax)
                                                            <option value="{{ $tax->name }}">{{ $tax->name }}%</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="price[]" class="form-control form-control-sm price">
                                            <!-- <div class="text-muted small show-base-price mt-1"></div> -->
                                        </td>
                                        <td><input type="number" name="amount[]" class="form-control form-control-sm amount" readonly></td>
                                        <!-- <td>
                                            <input type="checkbox" name="is_free[]" class="is-free" value="1">
                                        </td> -->
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
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Select Product</h5>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label for="filterSupplier" class="form-label">Filter by Supplier</label>
                        <select id="filterSupplier" class="form-control form-control-sm">
                            <option value="">-- All Suppliers --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- <input type="text" id="productSearch" class="form-control mb-3" placeholder="Search product..."> -->
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
                                <th>Status</th>
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
                                    data-baseprice="{{ optional($product->supplierItems->first())->item_price }}"
                                    data-productstatus="{{ $product->status }}">
                                    <td>{{ $product->product_code }}</td>
                                    <td>{{ $product->supplier_product_code }}</td>
                                    <td>{{ optional(optional($product->supplierItems->first())->supplier)->name ?? '-' }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ optional($product->supplierItems->first())->item_price }}</td>
                                    <td>{{ $product->sales_price }}</td>
                                    <td>{{ $product->remaining_stock }}</td>
                                    <td>{{ $product->unit->name }}</td>
                                    <td>
                                        @if($product->remaining_stock <= 0)
                                            <span class="text-danger">Out of Stock</span>
                                        @elseif($product->remaining_stock <= 5)
                                            <span class="text-warning">Low Stock</span>
                                        @else
                                            <span class="text-success">In Stock</span>
                                        @endif
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
    </div>
</div>
@endsection

@push('js')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function(){
        function formatToYMD(date) {
            const d = new Date(date);
            const month = ('0' + (d.getMonth() + 1)).slice(-2);
            const day   = ('0' + d.getDate()).slice(-2);
            const year  = d.getFullYear();
            return `${year}-${month}-${day}`;
        }

        $('#invoice_date_display').datepicker({
            dateFormat: 'MM dd, yy',
            onSelect: function(dateText) {
                $('#invoice_date').val(formatToYMD(dateText));
            }
        });

        $('#due_date_display').datepicker({
            dateFormat: 'MM dd, yy',
            onSelect: function(dateText) {
                $('#due_date').val(formatToYMD(dateText));
            }
        });
        let currentRow = null;
        let productTable = null;

            $('#productModal').on('shown.bs.modal', function () {
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
        });

        $('#filterSupplier').on('change', function () {
            let supplierId = $(this).val();

            $.fn.dataTable.ext.search = []; // clear old filters first

            if (!supplierId) {
                productTable.draw();
                return;
            }

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {

                // Correct row reference
                let rowNode = productTable.row(dataIndex).node();

                let rowSupplier = $(rowNode).data('supplier');

                return rowSupplier == supplierId;
            });

            productTable.draw();
        });

        // Open modal when search button clicked
        $(document).on('click', '.select-product-btn', function() {
            currentRow = $(this).closest('tr'); // remember which row opened the modal
            $('#productModal').modal('show');
            $('#productSearch').val('').trigger('input'); // reset search
        });
        // Filter products as you type
        $('#productSearch').on('input', function() {
            productTable.search($(this).val()).draw();
        });

        $('#productModal .btn-close').on('click', function() {
            $('#productModal').modal('hide'); // jQuery fallback
        });
        // When selecting a product
        $(document).on('click', '.select-this', function() {
            let tr = $(this).closest('tr');
            let id = tr.data('id');
            let code = tr.data('code');
            let name = tr.data('name');
            let price = tr.data('price');
            let stock = tr.data('stock');
            let unitId = tr.data('unit');
            let basePrice = tr.data('baseprice');
            let discount_type = tr.data('discounttype');
            let discount_1 = tr.data('discount1');
            let discount_2 = tr.data('discount2');
            let discount_3 = tr.data('discount3');
            let prodStatus = tr.data('productstatus');


            let duplicate = false;
            $('input.product_id').each(function() {
                if ($(this).val() == id && this !== currentRow.find('.product_id')[0]) {
                    duplicate = true;
                }
            });
            
            if (prodStatus === 'Out of Stock') {
                Swal.fire({
                    icon: 'error',
                    title: 'Inactive Product',
                    text: 'The selected product is currently out of stock and cannot be added.',
                    confirmButtonColor: '#ff9f43',
                });
                return;
            }

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
            currentRow.find('.product-input').val(name);
            currentRow.find('.product_id').val(id);
            currentRow.find('.code').val(code);
            currentRow.find('.price').val(price);
            currentRow.find('.qty').val('').prop('readonly', false).data('stock', stock);
            currentRow.find('.unit').val(unitId).trigger('change');
            currentRow.find('.discount_type').val(discount_type).trigger('change');
            currentRow.find('.dis1').val(discount_1).trigger('change');
            currentRow.find('.dis2').val(discount_2).trigger('change');
            currentRow.find('.dis3').val(discount_3).trigger('change');

            currentRow.find('.available-stock').text("Available: " + stock);

            currentRow.find('.selected-product-info').html(name);
            currentRow.find('.show-base-price').html("Unit Cost: " +basePrice);

            $('#productModal').modal('hide');
        });
        
        $('#customerSelect').select2({
            placeholder: "Select Customer",
            allowClear: true,
            width: 'resolve'
        });

        $('.unit').select2({
            placeholder: "Select Unit",
            allowClear: true,
            width: 'resolve'
        });

        function renumberRows() {
            $('#po-body tr').each(function (index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Disable all Add Discount buttons initially
        $('.add-discount').prop('disabled', true);
        $('.addRow').on('click', function () {
            let rowCount = $('#po-body tr').length;
            if (rowCount >= MAX_ROWS) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Row Limit Reached',
                    text: 'You can only add up to 15 products per invoice.',
                    confirmButtonColor: '#ff9f43',
                });
                return;
            }

            addRow();
            renumberRows();
            calculateTotals();
        });

        const productOptions = `{!! 
            $products->map(function($product){
                return '<option value="'.$product->id.'">'.$product->name.'</option>';
            })->implode('') 
        !!}`;

        let rowIndex = $('#po-body tr').length;
        let rowColumnAdd = rowIndex++ + 1;
        const MAX_ROWS = 15;
        
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
                                <td></td>
                                <td>
                                    <input type="hidden" name="product_id[]" class="product_id">
                                    <input type="hidden" name="product_code[]" class="form-control code" readonly>
                                    <div class="input-group">
                                        <input type="text" name="product_name[]" class="form-control form-control-sm product-input productname" placeholder="Search Product" readonly>
                                        <button type="button" class="btn btn-outline-primary select-product-btn">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                    <div class="text-muted small selected-product-info mt-1"></div>
                                </td>
                                <td>
                                    <select name="unit[]" class="form-control form-control-sm unit">
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="qty[]" class="form-control form-control-sm qty">
                                    <small class="text-muted available-stock"></small>
                                </td>
                                <td>
                                    <div class="row g-1">
                                        <!-- Discount Type -->
                                        <div class="col-8">
                                            <select name="discount_less_add[]" class="form-control form-control-sm discount_type">
                                                <option value="less">Less (-)</option>
                                                <option value="add">Add (+)</option>
                                            </select>
                                        </div>

                                        <!-- Discount 1 -->
                                        <div class="col-8">
                                            <select name="dis1[]" class="form-control form-control-sm dis1">
                                                <option value="0">Discount 1 (%)</option>
                                                @foreach($taxes as $tax)
                                                    <option value="{{ $tax->name }}">{{ $tax->name }}%</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Discount 2 -->
                                        <div class="col-8">
                                            <select name="dis2[]" class="form-control form-control-sm dis2">
                                                <option value="0">Discount 2 (%)</option>
                                                @foreach($taxes as $tax)
                                                    <option value="{{ $tax->name }}">{{ $tax->name }}%</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Discount 3 -->
                                        <div class="col-8">
                                            <select name="dis3[]" class="form-control form-control-sm dis3">
                                                <option value="0">Discount 3 (%)</option>
                                                @foreach($taxes as $tax)
                                                    <option value="{{ $tax->name }}">{{ $tax->name }}%</option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="price[]" class="form-control form-control-sm price">
                                </td>
                                <td><input type="number" name="amount[]" class="form-control form-control-sm amount" readonly></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>`;
            $('#po-body').append(newRow);

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
            let rows = $('#po-body tr').length;

            if (rows === 1) {
                alert("You can't delete the last row");
                return;
            }

            $(this).closest('tr').remove();
            renumberRows();
            $('.addRow').prop('disabled', false);
            calculateTotals();
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
            let term = parseInt(selected.data('term')) || 0;

            let invoiceDate = $('#invoice_date').val(); // yyyy-mm-dd

            if (!invoiceDate) return;

            let d = new Date(invoiceDate);
            d.setDate(d.getDate() + term);

            // Format values
            let yyyy = d.getFullYear();
            let mm = String(d.getMonth() + 1).padStart(2, '0');
            let dd = String(d.getDate()).padStart(2, '0');

            let dueDateYMD = `${yyyy}-${mm}-${dd}`;
            let dueDateDisplay = d.toLocaleDateString('en-US', {
                month: 'long',
                day: '2-digit',
                year: 'numeric'
            });

            // Update both fields
            $('#due_date').val(dueDateYMD);
            $('#due_date_display').val(dueDateDisplay);
        });

        $('#invoice_date_display').on('change', function () {
            $('#payment_id').trigger('change');
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
                Swal.fire({
                    icon: "warning",
                    title: "Insufficient Stock",
                    text: "Quantity exceeds the available stock!",
                    confirmButtonColor: "#ff9f43",
                });

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
    });
    $(document).on('change', '.is-free', function () {
        const $row = $(this).closest('tr');
        const is_free = $(this).is(':checked');

        if (is_free) {
            // Set values to 0
            $row.find('.qty, .amount').val(0).prop('readonly', true);
            $row.find('.dis1, .dis2, .dis3').val('');
            $row.find('.discount_type').val('');

            // Disable discounts
            $row.find('.dis1, .dis2, .dis3, .discount_type').prop('disabled', true);
        } else {
            // Re-enable fields
            $row.find('.qty, .price').prop('readonly', false);

            $row.find('.dis1, .dis2, .dis3, .discount_type').prop('disabled', false);
        }
    });
    let formPendingSubmit = null;
    $('form').on('submit', function(e) {
        e.preventDefault();

        let hasError = false;
        let errorMessages = [];
        let validItemCount = 0;

        const customerId   = $('#customerSelect').val();
        const invoiceDate  = $('#invoice_date').val();
        const paymentMode  = $('#payment_id').val();
        const salesman     = $('#salesman_id').val();

        if (!customerId) {
            hasError = true;
            errorMessages.push('Customer is required.');
        }

        if (!invoiceDate) {
            hasError = true;
            errorMessages.push('Invoice date is required.');
        }

        if (!paymentMode) {
            hasError = true;
            errorMessages.push('Mode of payment is required.');
        }

        if (!salesman) {
            hasError = true;
            errorMessages.push('Salesman is required.');
        }

        $('#po-body tr').each(function(index) {
            const $row = $(this);
            const rowNumber = index + 1;

            const productName = $row.find('.selected-product-info').text();
            const productId = $row.find('.product_id').val();
            const stock = parseInt($row.find('.qty').data('stock')) || 0;
            const qty = parseInt($row.find('.qty').val()) || 0;
            const price = parseFloat($row.find('.price').val()) || 0;
            const is_free = $row.find('.is-free').is(':checked');
            const d1 = parseFloat($row.find('.dis1').val()) || 0;
            const d2 = parseFloat($row.find('.dis2').val()) || 0;
            const d3 = parseFloat($row.find('.dis3').val()) || 0;
            const discountType = $row.find('.discount_type').val();

            let netDiscount = 0;
            if (discountType === 'less') {
                netDiscount = d1 + d2 + d3;
            } else if (discountType === 'add') {
                netDiscount = -(d1 + d2 + d3);
            }

            // clamp discount
            netDiscount = Math.max(0, Math.min(netDiscount, 100));

            const isFullyDiscounted = netDiscount >= 100;

            if (isRowEmpty($row)) {
                hasError = true;
                errorMessages.push(`Row ${rowNumber}: Empty row is not allowed.`);
                return;
            }

            // Product required
            if (!productId) {
                hasError = true;
                errorMessages.push(`Row ${index + 1}: Please select a product.`);
                return;
            }

            // if (is_free) {
            //     $row.find('.dis1, .dis2, .dis3, .discount_type').prop('disabled', true);
            //     $row.find('.qty, .amount').val(0).prop('readonly', true);
            //     // Free item but qty or price has value
            //     if (qty > 0 || price > 0) {
            //         hasError = true;
            //         errorMessages.push(
            //             `${productName} is marked as FREE. Quantity and price must be 0.`
            //         );
            //     }
            //     return;
            // }

            if (is_free || isFullyDiscounted) {
                $row.find('.dis1, .dis2, .dis3, .discount_type')
                    .prop('disabled', true);

                $row.find('.qty, .price, .amount')
                    .val(0)
                    .prop('readonly', true);

                validItemCount++;
                return; // skip qty/price validation
            }

            if (qty <= 0) {
                hasError = true;
                errorMessages.push(`${productName} must have a quantity greater than 0.`);
            }

            if (price <= 0) {
                hasError = true;
                errorMessages.push(`${productName} must have a price greater than 0.`);
            }

            if (stock <= 0) {
                hasError = true;
                errorMessages.push(`${productName} is out of stock.`);
            }

            if (qty > stock) {
                hasError = true;
                errorMessages.push(
                    `${productName} quantity (${qty}) exceeds available stock (${stock}).`
                );
            }
        });

        if (hasError) {
            // SweetAlert2 error
            swal({
                title: 'Cannot submit invoice order',
                html: errorMessages.join('<br>'),
                type: 'error',
                confirmButtonText: 'OK'
            });
            return false; // stop submit
        }

        // save reference to the form
        const form = this; 
        swal({
            title: 'Are you sure?',
            text: "Do you want to submit this invoice?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            form.submit(); 
        });
    });

    function isRowEmpty($row) {
        const productId = $row.find('.product_id').val();
        // const qty = parseFloat($row.find('.qty').val()) || 0;
        // const price = parseFloat($row.find('.price').val()) || 0;

        return !productId;
    }

    // Disable all discount fields by default
    $('.dis, #discount').prop('disabled', true);

    $('#discount_type').trigger('change');
        let discountApprovalCount = 0;
        let pendingDiscountInput = null;

        var discountModal = new bootstrap.Modal(document.getElementById('discountApprovalModal'), {
            backdrop: 'static',
            keyboard: false
        });

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

        $('#po-body tr').each(function () {
            const $row = $(this);
            const qty = parseFloat($row.find('.qty').val()) || 0;
            const price = parseFloat($row.find('.price').val()) || 0;

            let lineTotal = qty * price;

            const discountType = $row.find('select[name="discount_less_add[]"]').val() || 'less';

            const discounts = [
                parseFloat($row.find('select[name="dis1[]"]').val()) || 0,
                parseFloat($row.find('select[name="dis2[]"]').val()) || 0,
                parseFloat($row.find('select[name="dis3[]"]').val()) || 0
            ];

            if (discountType === 'less') {
                // LESS = subtract %
                discounts.forEach(d => {
                    if (d > 0) lineTotal *= (1 - d / 100);
                });

                $row.find('.discount-row .dis').each(function () {
                    const d = parseFloat($(this).val()) || 0;
                    if (d > 0) lineTotal *= (1 - d / 100);
                });

            } else if (discountType === 'add') {
                // ADD = convert % → multiplier
                discounts.forEach(d => {
                    if (d > 0) lineTotal *= (1 + d / 100);
                });

                $row.find('.discount-row .dis').each(function () {
                    const d = parseFloat($(this).val()) || 0;
                    if (d > 0) lineTotal *= (1 + d / 100);
                });
            }

            $row.find('.amount').val(lineTotal.toFixed(2));
            subtotal += lineTotal;
        });

        // OVERALL DISCOUNT
        const overallType = $('#discount_type').val();
        let overallDis = parseFloat($('#discount').val()) || 0;

        if (overallType === 'overall' && overallDis > 0) {
            subtotal *= (1 - overallDis / 100);
        }

        const shipping = parseFloat($('#shipping').val()) || 0;
        const other = parseFloat($('#other').val()) || 0;

        const grandTotal = subtotal + shipping + other;

        $('#subtotal').val(subtotal.toFixed(2));
        $('#grand_total').val(grandTotal.toFixed(2));
    }


    // Trigger recalculation whenever relevant inputs change
    $(document).on('change input', '.qty, .price, select[name="dis1[]"], select[name="dis2[]"], select[name="dis3[]"], .dis, select[name="discount_less_add[]"], #discount, #shipping, #other', function() {
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