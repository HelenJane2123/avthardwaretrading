@extends('layouts.master')

@section('title', 'Supplier | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i>Supplier</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Supplier</li>
                <li class="breadcrumb-item"><a href="#">Add Supplier</a></li>
            </ul>
        </div>

        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="">
            <a class="btn btn-primary" href="{{route('supplier.index')}}"><i class="fa fa-edit"></i> Manage Supplier</a>
        </div>
        <div class="row mt-2">

            <div class="clearix"></div>
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Supplier</h3>
                    <div class="tile-body">
                        <form method="POST" action="{{ route('supplier.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="control-label">Supplier Code</label>
                                    <input name="supplier_code" id="supplier_code" class="form-control" type="text" readonly>
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="control-label">Supplier Name</label>
                                    <input name="name" id="supplier_name" class="form-control @error('name') is-invalid @enderror" type="text" placeholder="Enter Name of Supplier">
                                     @error('name')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="control-label">Contact</label>
                                    <input name="mobile" class="form-control @error('mobile') is-invalid @enderror" type="text" placeholder="Enter Contact Number of Supplier">
                                    @error('mobile')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                 <div class="form-group col-md-4">
                                    <label class="control-label">Email</label>
                                    <input name="email" class="form-control @error('email') is-invalid @enderror" type="text" placeholder="johndoe@gmail.com">
                                    @error('tax')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="control-label">Tax</label>
                                    <input name="tax" class="form-control @error('tax') is-invalid @enderror" type="text" placeholder="123-456-789-000">
                                    @error('tax')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="control-label">Address</label>
                                    <textarea name="address" class="form-control @error('address') is-invalid @enderror"></textarea>
                                    @error('address')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="control-label">Details</label>
                                    <textarea name="details" class="form-control @error('details') is-invalid @enderror"></textarea>
                                    @error('details')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            
                            </div>
                            <h5 class="mt-4">Item Details</h5>
                            <table class="table table-bordered" id="suppliercreateTable">
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Item Category</th>
                                        <th>Item Description</th>
                                        <th>Item Qty</th>
                                        <th>Item Unit</th>
                                        <th>Item Price</th>
                                        <th>Item Amount</th>
                                        <th>Item Image</th>
                                        <th><button type="button" class="btn btn-sm btn-primary" id="add-row">Add New Item</button></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" name="item_code[]" class="form-control" readonly /></td>
                                        <td>
                                            <select name="item_category[]" class="form-control">
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="item_description[]" class="form-control" /></td>
                                        <td><input type="number" name="item_qty[]" class="form-control item-qty" /></td>
                                        <td>
                                            <select name="unit_id[]" class="form-control">
                                                <option value="">Select Unit</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" name="item_price[]" class="form-control item-price" step="0.01" /></td>
                                        <td><input type="number" name="item_amount[]" class="form-control item-amount" step="0.01" /></td>
                                        <td><input type="file" name="item_image[]" class="form-control" accept="image/*" /></td>
                                        <td><button type="button" class="btn btn-sm btn-danger remove-row">Delete</button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Total Amount:</strong></td>
                                        <td colspan="3"><input type="text" id="total_amount" class="form-control" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="form-group col-md-12 text-end">
                                <button class="btn btn-success" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i> Add Supplier Details
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
@push('js')
<script>
    $(document).ready(function () {
        let itemCount = 2;

        function getSupplierPrefix() {
            const name = $('#supplier_name').val().trim();
            return name ? name.split(' ')[0].toUpperCase().substring(0, 3) : 'SUP';
        }

        function generateSupplierCode() {
            const prefix = getSupplierPrefix();
            const randomNumber = Math.floor(Math.random() * 900 + 100); // 100–999
            return `${prefix}-${randomNumber}`;
        }

        $('#supplier_name').on('input', function () {
            const supplierCode = generateSupplierCode();
            $('#supplier_code').val(supplierCode);
            $('#suppliercreateTable tbody tr:first input[name="item_code[]"]').val(`${supplierCode}-001`);
        });

        const categories = @json($categories);
        const units = @json($units);
        $('#add-row').click(function () {
            const supplierCode = $('#supplier_code').val() || 'SUP-000';
            const paddedCount = String(itemCount).padStart(3, '0');
            const itemCode = `${supplierCode}-${paddedCount}`;

            const categoryOptions = categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
            const unitOptions = units.map(unit => `<option value="${unit.id}">${unit.name}</option>`).join('');

            $('#suppliercreateTable tbody').append(`
                <tr>
                    <td><input type="text" name="item_code[]" class="form-control" value="${itemCode}" readonly /></td>
                    <td>
                        <select name="item_category[]" class="form-control">
                            <option value="">Select Category</option>
                            ${categoryOptions}
                        </select>
                    </td>
                    <td><input type="text" name="item_description[]" class="form-control" /></td>
                    <td><input type="number" name="item_qty[]" class="form-control item-qty"/></td>
                    <td>
                        <select name="unit_id[]" class="form-control">
                            <option value="">Select Unit</option>
                            ${unitOptions}
                        </select>
                    </td>
                    <td><input type="number" name="item_price[]" class="form-control item-price" step="0.01" /></td>
                    <td><input type="number" name="item_amount[]" class="form-control item-amount" step="0.01" /></td>
                    <td><input type="file" name="item_image[]" class="form-control" accept="image/*" /></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row">Delete</button></td>
                </tr>
            `);

            itemCount++;
        });

        // Compute item amount = qty * price
        function calculateAmount(row) {
            const qty = parseFloat(row.find('.item-qty').val()) || 0;
            const price = parseFloat(row.find('.item-price').val()) || 0;
            const amount = qty * price;
            row.find('.item-amount').val(amount.toFixed(2));
        }

        // Compute total amount
        function calculateTotalAmount() {
            let total = 0;
            $('.item-amount').each(function () {
                total += parseFloat($(this).val()) || 0;
            });
            $('#total_amount').val(total.toFixed(2));
        }

        // Auto compute on input change
        $(document).on('input', '.item-qty, .item-price', function () {
            const row = $(this).closest('tr');
            calculateAmount(row);
            calculateTotalAmount();
        });

        // Delete row
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            calculateTotalAmount();
        });

    });
</script>
@endpush



