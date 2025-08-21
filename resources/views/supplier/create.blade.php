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

        <!-- Manage Supplier Button -->
        <div class="mb-3">
            <a class="btn btn-outline-primary shadow-sm" href="{{ route('supplier.index') }}">
                <i class="fa fa-list"></i> Manage Supplier
            </a>
        </div>

        <div class="row">
            <div class="col-md-12">
                <!-- Supplier Form -->
                <div class="tile shadow-sm rounded">
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
                                           class="form-control" type="text" readonly>
                                </div>

                                <!-- Supplier Name -->
                                <div class="form-group col-md-6">
                                    <label class="control-label">Supplier Name</label>
                                    <input name="name" id="supplier_name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           type="text" placeholder="Enter Supplier Name">
                                    @error('name')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!-- Contact -->
                                <div class="form-group col-md-4">
                                    <label class="control-label">Contact</label>
                                    <input name="mobile" 
                                           class="form-control @error('mobile') is-invalid @enderror" 
                                           type="text" placeholder="Enter Contact Number">
                                    @error('mobile')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="form-group col-md-4">
                                    <label class="control-label">Email</label>
                                    <input name="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           type="email" placeholder="johndoe@gmail.com">
                                    @error('email')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!-- Tax -->
                                <div class="form-group col-md-4">
                                    <label class="control-label">Tax</label>
                                    <input name="tax" 
                                           class="form-control @error('tax') is-invalid @enderror" 
                                           type="text" placeholder="123-456-789-000">
                                    @error('tax')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="form-group col-md-6">
                                    <label class="control-label">Address</label>
                                    <textarea name="address" 
                                              class="form-control @error('address') is-invalid @enderror" 
                                              placeholder="Enter Supplier Address"></textarea>
                                    @error('address')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <!-- Details -->
                                <div class="form-group col-md-6">
                                    <label class="control-label">Details</label>
                                    <textarea name="details" 
                                              class="form-control @error('details') is-invalid @enderror" 
                                              placeholder="Additional Information"></textarea>
                                    @error('details')
                                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Item Details Table -->
                            <h5 class="mt-4">Item Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="suppliercreateTable">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th>Item Code</th>
                                            <th>Category</th>
                                            <th>Description</th>
                                            <th>Qty</th>
                                            <th>Unit</th>
                                            <th>Price</th>
                                            <th>Amount</th>
                                            <th>Image</th>
                                            <th>
                                                <button type="button" class="btn btn-sm btn-primary" id="add-row">
                                                    <i class="fa fa-plus"></i> Add Item
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="item_code[]" class="form-control" readonly></td>
                                            <td>
                                                <select name="item_category[]" class="form-control">
                                                    <option value="">Select Category</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" name="item_description[]" class="form-control"></td>
                                            <td><input type="number" name="item_qty[]" class="form-control item-qty"></td>
                                            <td>
                                                <select name="unit_id[]" class="form-control">
                                                    <option value="">Select Unit</option>
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" name="item_price[]" class="form-control item-price" step="0.01"></td>
                                            <td><input type="number" name="item_amount[]" class="form-control item-amount" step="0.01" readonly></td>
                                            <td><input type="file" name="item_image[]" class="form-control" accept="image/*"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-row">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-right"><strong>Total Amount:</strong></td>
                                            <td colspan="3"><input type="text" id="total_amount" class="form-control" readonly></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group text-end">
                                <button class="btn btn-success shadow-sm" type="submit">
                                    <i class="fa fa-check-circle"></i> Save Supplier
                                </button>
                                <a href="{{ route('supplier.index') }}" class="btn btn-secondary px-4">
                                    <i class="fa fa-times"></i> Cancel
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
    <script src="{{ asset('/') }}js/plugins/jquery.dataTables.min.js"></script>
    <script src="{{ asset('/') }}js/plugins/dataTables.bootstrap.min.js"></script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>

    <script>
        $(document).ready(function () {
            let table = $('#suppliercreateTable').DataTable({
                paging: false,
                searching: true,  // ✅ search enabled
                ordering: false   // (optional: disable sorting if not needed for inputs)
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
            $('#supplier_name').on('input', function () {
                const supplierCode = generateSupplierCode();
                $('#supplier_code').val(supplierCode);
                $('#suppliercreateTable tbody tr:first input[name="item_code[]"]').val(`${supplierCode}-001`);
            });

            // Add Row
            const categories = @json($categories);
            const units = @json($units);
            $('#add-row').click(function () {
                const supplierCode = $('#supplier_code').val() || 'SUP-000';
                const paddedCount = String(itemCount).padStart(3, '0');
                const itemCode = `${supplierCode}-${paddedCount}`;

                const categoryOptions = categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
                const unitOptions = units.map(unit => `<option value="${unit.id}">${unit.name}</option>`).join('');

                table.row.add([
                    `<input type="text" name="item_code[]" class="form-control" value="${itemCode}" readonly>`,
                    `<select name="item_category[]" class="form-control"><option value="">Select Category</option>${categoryOptions}</select>`,
                    `<input type="text" name="item_description[]" class="form-control">`,
                    `<input type="number" name="item_qty[]" class="form-control item-qty">`,
                    `<select name="unit_id[]" class="form-control"><option value="">Select Unit</option>${unitOptions}</select>`,
                    `<input type="number" name="item_price[]" class="form-control item-price" step="0.01">`,
                    `<input type="number" name="item_amount[]" class="form-control item-amount" step="0.01" readonly>`,
                    `<input type="file" name="item_image[]" class="form-control" accept="image/*">`,
                    `<button type="button" class="btn btn-sm btn-danger remove-row"><i class="fa fa-trash"></i></button>`
                ]).draw(false); // ✅ keeps search working

                itemCount++;
            });
            // Calculate Row & Total
            function calculateAmount(row) {
                const qty = parseFloat(row.find('.item-qty').val()) || 0;
                const price = parseFloat(row.find('.item-price').val()) || 0;
                const amount = qty * price;
                row.find('.item-amount').val(amount.toFixed(2));
            }
            function calculateTotalAmount() {
                let total = 0;
                $('.item-amount').each(function () {
                    total += parseFloat($(this).val()) || 0;
                });
                $('#total_amount').val(total.toFixed(2));
            }
            $(document).on('input', '.item-qty, .item-price', function () {
                const row = $(this).closest('tr');
                calculateAmount(row);
                calculateTotalAmount();
            });

            // Remove Row
            $(document).on('click', '.remove-row', function () {
                $(this).closest('tr').remove();
                calculateTotalAmount();
            });
        });
    </script>
@endpush
