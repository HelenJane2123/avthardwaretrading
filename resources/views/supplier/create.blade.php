@extends('layouts.master')

@section('title', 'Supplier | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

    <main class="app-content">
        <!-- Page Title -->
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-industry"></i> Supplier</h1>
                <p class="text-muted mb-0">Add new supplier and manage their items</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Supplier</li>
                <li class="breadcrumb-item active">Add Supplier</li>
            </ul>
        </div>

        <!-- Flash Message -->
        @if(session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (session()->has('error'))
             <div class="alert alert-dange alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle"></i> {{ session()->get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Manage Supplier Button -->
        <div class="mb-3">
            <a class="btn btn-outline-primary btn-sm shadow-sm" href="{{ route('supplier.index') }}">
                <i class="fa fa-list"></i> Manage Supplier
            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                <!-- Supplier Form -->
                <div class="tile shadow-sm rounded">
                    @if(auth()->user()->user_role === 'super_admin')
                        <hr/>
                        <div class="col-md-12">
                            <div class="tile-body">
                                <h3 class="tile-title">Import Bulk Supplier</h3>
                                <small class="text-muted">Use this field only to import bulk supplier items and supplier details</small>
                                <form action="{{ route('import.supplier') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <label>Select Excel File:</label>
                                    <input type="file" name="file" class="form-control" required><br/>
                                    <button type="submit" class="btn btn-primary">Import</button>
                                </form>
                            </div>
                        </div>
                        <hr/>
                    @endif
                    <h3 class="tile-title">Supplier Details</h3>
                    <small class="text-muted">Fields marked with <span class="text-danger">*</span> are required</small>
                    <div class="tile-body">
                        <form method="POST" action="{{ route('supplier.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <!-- Supplier Code -->
                                <div class="form-group col-md-4">
                                    <label class="control-label">Supplier Code</label>
                                    <input name="supplier_code" id="supplier_code" 
                                           class="form-control form-control-sm" type="text" readonly>
                                </div>

                                <!-- Supplier Name -->
                                <div class="form-group col-md-6">
                                    <label class="control-label">Supplier Name</label>
                                    <input name="name" id="supplier_name" 
                                           class="form-control form-control-sm @error('name') is-invalid @enderror" 
                                           type="text" placeholder="Enter Supplier Name">
                                    @error('name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!-- Contact -->
                                <div class="form-group col-md-4">
                                    <label class="control-label">Contact</label>
                                    <input name="mobile" 
                                           class="form-control form-control-sm @error('mobile') is-invalid @enderror" 
                                           type="text" placeholder="Enter Contact Number">
                                    @error('mobile')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="form-group col-md-4">
                                    <label class="control-label">Email</label>
                                    <input name="email" 
                                           class="form-control form-control-sm @error('email') is-invalid @enderror" 
                                           type="email" placeholder="johndoe@gmail.com">
                                    @error('email')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!-- Tax -->
                                <div class="form-group col-md-4">
                                    <label class="control-label">Tax</label>
                                    <input name="tax" 
                                           class="form-control form-control-sm @error('tax') is-invalid @enderror" 
                                           type="text" placeholder="123-456-789-000">
                                    @error('tax')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="form-group col-md-4">
                                    <label class="control-label">Address</label>
                                    <textarea name="address" 
                                              class="form-control form-control-sm @error('address') is-invalid @enderror" 
                                              placeholder="Street, Barangay, City, Province"></textarea>
                                    @error('address')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!-- Details -->
                                <div class="form-group col-md-4">
                                    <label class="control-label">Details</label>
                                    <textarea name="details" 
                                              class="form-control form-control-sm @error('details') is-invalid @enderror" 
                                              placeholder="Additional Information"></textarea>
                                    @error('details')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control form-control-sm col-md-20">
                                        <option value="1">Active</option>
                                        <option value="2">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Item Details Table -->
                            <h5 class="mt-4">Item Details</h5>
                            <button
                                type="button"
                                class="btn btn-sm btn-primary"
                                id="addItemBtn">
                                <i class="fa fa-plus"></i> Add Item
                            </button>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="suppliercreateTable">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th>Item Code</th>
                                            <th>Category</th>
                                            <th>Description</th>
                                            <!-- <th>Qty</th> -->
                                            <th>Unit</th>
                                            <th>Unit Cost</th>
                                            <th>Net Cost</th>
                                            <th>Discounts</th>
                                            <th>Volume Less</th>
                                            <th>Regular Less</th>
                                            <th>Image</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        <!-- dynamically added rows -->
                                    </tbody>
                                    <!-- <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-right"><strong>Total Amount:</strong></td>
                                            <td colspan="3"><input type="text" id="total_amount" class="form-control" readonly></td>
                                        </tr>
                                    </tfoot> -->
                                </table>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group text-end">
                                <button class="btn btn-sm btn-success shadow-sm" type="submit">
                                    <i class="fa fa-check-circle"></i> Save Supplier
                                </button>
                                <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-secondary px-4">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="modal fade" id="itemModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Supplier Item</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Category</label>
                            <select id="modal_category" class="form-control form-control-sm">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Description</label>
                            <input type="text" id="modal_description" class="form-control form-control-sm">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Unit</label>
                            <select id="modal_unit" class="form-control form-control-sm">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Unit Cost</label>
                            <input type="number" id="modal_price" class="form-control form-control-sm" step="0.01">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Net Cost</label>
                            <input type="number" id="modal_net_cost" class="form-control form-control-sm" step="0.01">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Discount</label>
                            <div class="row g-1">
                                <!-- Discount Type -->
                                <div class="col-8">
                                    <select name="discount_less_add" class="form-control form-control-sm discount_type">
                                        <option value="less">Less (-)</option>
                                        <option value="add">Add (+)</option>
                                    </select>
                                </div>

                                <!-- Discount 1 -->
                                <div class="col-8">
                                    <select name="dis1" class="form-control form-control-sm dis1">
                                        <option value="0">Discount 1 (%)</option>
                                        @foreach($discounts as $tax)
                                            <option value="{{ $tax->name }}">{{ $tax->name }}%</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Discount 2 -->
                                <div class="col-8">
                                    <select name="dis2" class="form-control form-control-sm dis2">
                                        <option value="0">Discount 2 (%)</option>
                                        @foreach($discounts as $tax)
                                            <option value="{{ $tax->name }}">{{ $tax->name }}%</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Discount 3 -->
                                <div class="col-8">
                                    <select name="dis3" class="form-control form-control-sm dis3">
                                        <option value="0">Discount 3 (%)</option>
                                        @foreach($discounts as $tax)
                                            <option value="{{ $tax->name }}">{{ $tax->name  }}%</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Image</label>
                            <input type="file" id="modal_image" class="form-control form-control-sm">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Volume Less</label>
                            <input type="text" id="modal_volume_less" class="form-control form-control-sm">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Regular Less</label>
                            <input type="text" id="modal_regular_less" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="confirmAddItem">
                        Add Item
                    </button>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('/') }}js/plugins/jquery.dataTables.min.js"></script>
    <script src="{{ asset('/') }}js/plugins/dataTables.bootstrap.min.js"></script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            let table = $('#suppliercreateTable').DataTable({
                paging: true,
                searching: true,  
                ordering: true,
                language: {
                    "emptyTable": "No data available in table"
                }
            });

            $('#modal_category').select2({
                placeholder: "Select Category",
                allowClear: true,
                width: '250px'
            });

            $('#modal_unit').select2({
                placeholder: "Select Unit",
                allowClear: true,
                width: '150px'
            });

            let itemCount = 2;

            // Supplier Code Generator
            function getSupplierPrefix() {
                const name = $('#supplier_name').val().trim();
                return name ? name.split(' ')[0].toUpperCase().substring(0, 3) : 'SUP';
            }
            function generateSupplierCode() {
                const prefix = getSupplierPrefix();
                const randomNumber = Math.floor(Math.random() * 900 + 100);
                return `${prefix}-${randomNumber}`;
            }
            //disable adding item if supplier name is empty
            $('#addItemBtn').on('click', function (e) {
                if ($('#supplier_name').val().trim() === '') {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Supplier Required',
                        text: 'Please enter the supplier name before adding items.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    });
                    return false; 
                }
                resetModal();
                $('#itemModal').modal({
                    backdrop: 'static',
                    keyboard: false
                });
            });
            $('#supplier_name').on('input', function () {
                const supplierName = $(this).val().trim();

                if (supplierName === '') {
                    $('#addItemBtn').prop('disabled', true);
                    $('#supplierWarning').removeClass('d-none');
                    return;
                }

                // If supplier name exists
                const supplierCode = generateSupplierCode();

                $('#supplier_code').val(supplierCode);
                $('#addItemBtn').prop('disabled', false);
                $('#supplierWarning').addClass('d-none');

                // Auto-generate first item code
                $('#suppliercreateTable tbody tr:first input[name="item_code[]"]')
                    .val(`${supplierCode}-001`);
            });

            // Add Row
            let tableItems = $('#itemsTableBody tbody');
            function resetModal() {
                $('#modal_category').val('');
                $('#modal_description').val('');
                $('#modal_unit').val('');
                $('#modal_price').val('');
                $('#modal_net_cost').val('');
                $('.discount_type').val('less');
                $('.dis1').val('0');
                $('.dis2').val('0');
                $('.dis3').val('0');
                $('#modal_volume_less').val('');
                $('#modal_regular_less').val('');
                $('#modal_image').val('');
                editingRow = null;
            }

            $('#addItemBtn').click(function () {
                resetModal();
                $('#itemModal').modal('show');
                $('#itemModalTitle').text('Add Supplier Item');
            });

            let editingRow = null;
            $('#confirmAddItem').click(function () {
                $(this).blur();

                let discountText = `${$('.discount_type').val()} (${ $('.dis1').val() }%, ${ $('.dis2').val() }%, ${ $('.dis3').val()}%)`;
                let itemCode = generateItemCode();

                // Prepare row as array of column data
                let rowData = [
                    itemCode + `<input type="hidden" name="item_code[]" value="${itemCode}">`,
                    $('#modal_category option:selected').text() + `<input type="hidden" name="category_id[]" value="${$('#modal_category').val()}">`,
                    $('#modal_description').val() + `<input type="hidden" name="item_description[]" value="${$('#modal_description').val()}">`,
                    $('#modal_unit option:selected').text() + `<input type="hidden" name="unit_id[]" value="${$('#modal_unit').val()}">`,
                    $('#modal_price').val() + `<input type="hidden" name="unit_cost[]" value="${$('#modal_price').val()}">`,
                    $('#modal_net_cost').val() + `<input type="hidden" name="net_cost[]" value="${$('#modal_net_cost').val()}">`,
                    discountText + 
                    `<input type="hidden" name="discount_type[]" value="${$('.discount_type').val()}">` +
                    `<input type="hidden" name="discount1[]" value="${$('.dis1').val()}">` +
                    `<input type="hidden" name="discount2[]" value="${$('.dis2').val()}">` +
                    `<input type="hidden" name="discount3[]" value="${$('.dis3').val()}">`,
                    $('#modal_volume_less').val() + `<input type="hidden" name="volume_less[]" value="${$('#modal_volume_less').val()}">`,
                    $('#modal_regular_less').val() + `<input type="hidden" name="regular_less[]" value="${$('#modal_regular_less').val()}">`,
                    ($('#modal_image').val() || 'N/A') + `<input type="hidden" name="modal_image[]" value="${$('#modal_image').val() || 'N/A'}">`,
                    `<div class="text-center">
                        <button type="button" class="btn btn-sm btn-primary edit-item"><i class="fa fa-edit"></i></button>
                        <button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button>
                    </div>`
                ];

                // If editing, update row; else add new
                if (editingRow) {
                    table.row(editingRow).data(rowData).draw();
                    editingRow = null;
                } else {
                    table.row.add(rowData).draw();
                }

                $('#itemModal').modal('hide');
            });

            $('#itemModal').on('hidden.bs.modal', function () {
                // Remove leftover backdrop
                $('.modal-backdrop').remove();

                // Restore body scrolling & interaction
                $('body')
                    .removeClass('modal-open')
                    .css({
                        overflow: '',
                        paddingRight: ''
                    });

                // Restore focus safely
                $('[data-target="#itemModal"]').focus();
            });

            // Edit row
            $(document).on('click', '.edit-item', function () {
                editingRow = $(this).closest('tr');

                $('#modal_description').val(editingRow.find('input[name="item_description[]"]').val());
                $('#modal_unit').val(editingRow.find('input[name="unit_id[]"]').val());
                $('#modal_price').val(editingRow.find('input[name="unit_cost[]"]').val());
                $('.discount_type').val(editingRow.find('input[name="discount_type[]"]').val());
                $('.dis1').val(editingRow.find('input[name="discount1[]"]').val());
                $('.dis2').val(editingRow.find('input[name="discount2[]"]').val());
                $('.dis3').val(editingRow.find('input[name="discount3[]"]').val());
                $('#modal_net_cost').val(editingRow.find('input[name="net_cost[]"]').val());
                $('#modal_category').val(editingRow.find('input[name="category_id[]"]').val());
                $('#modal_volume_less').val(editingRow.find('input[name="volume_less[]"]').val());
                $('#modal_regular_less').val(editingRow.find('input[name="regular_less[]"]').val());

                $('#itemModal').modal('show');
            });

            // Remove row
            $(document).on('click', '.remove-row', function () {
                $(this).closest('tr').remove();
            });

            //check if selected item is duplicate
            $(document).on('change', 'input[name="item_description[]"]', function () {
                const currentValue = $(this).val().trim().toLowerCase();
                let isDuplicate = false;

                $('input[name="item_description[]"]').not(this).each(function () {
                    if ($(this).val().trim().toLowerCase() === currentValue && currentValue !== '') {
                        isDuplicate = true;
                        return false; // stop checking
                    }
                });

                if (isDuplicate) {
                    swal({
                        title: "Duplicate Item",
                        text: "This product already exists in the list.",
                        icon: "error",
                        button: "OK",
                    });
                    $(this).val(''); // clear the duplicate entry
                    $(this).focus();
                }
            });      

            function computeNetCost() {
                let price = parseFloat($('#modal_price').val()) || 0;
                let d1 = parseFloat($('.dis1').val()) || 0;
                let d2 = parseFloat($('.dis2').val()) || 0;
                let d3 = parseFloat($('.dis3').val()) || 0;
                let type = $('.discount_type').val();

                let net = price;

                if (type === 'less') {
                    if (d1 > 0) net -= net * (d1 / 100);
                    if (d2 > 0) net -= net * (d2 / 100);
                    if (d3 > 0) net -= net * (d3 / 100);
                } 
                else if (type === 'add') {
                    if (d1 > 0) net += net * (d1 / 100);
                    if (d2 > 0) net += net * (d2 / 100);
                    if (d3 > 0) net += net * (d3 / 100);
                }

                $('#modal_net_cost').val(net.toFixed(2));
            }

            function generateItemCode() {
                const supplierCode = $('#supplier_code').val();
                if (!supplierCode) return '';

                // Count existing rows in DataTable
                const itemCount = table.rows().count() + 1; // +1 for the new item

                const itemNumber = String(itemCount).padStart(3, '0');

                return `${supplierCode}-${itemNumber}`;
            }

            $('#modal_price, .dis1, .dis2, .dis3, .discount_type').on('change keyup', computeNetCost);
        });
    </script>
@endpush
