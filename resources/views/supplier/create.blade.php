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
                        <form method="POST" action="{{ route('supplier.store') }}">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label class="control-label">Supplier Code</label>
                                    <input name="supplier_code" id="supplier_code" class="form-control" type="text" readonly>
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="control-label">Supplier Name</label>
                                    <input name="supplier_name" id="supplier_name" class="form-control" type="text" placeholder="Enter Name of Supplier">
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="control-label">Contact</label>
                                    <input name="mobile" class="form-control @error('mobile') is-invalid @enderror" type="text" placeholder="Enter Contact Number of Supplier">
                                    @error('mobile')
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
                                <div class="form-group col-md-4">
                                    <label class="control-label">Tax</label>
                                    <input name="tax" class="form-control @error('tax') is-invalid @enderror" type="text" placeholder="123-456-789-000">
                                    @error('tax')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <h5 class="mt-4">Item Details</h5>
                            <table class="table table-bordered" id="suppliercreateTable">
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Item Description</th>
                                        <th>Item Price</th>
                                        <th>Item Amount</th>
                                        <th><button type="button" class="btn btn-sm btn-primary" id="add-row">Add New Item</button></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" name="item_code[]" class="form-control" readonly /></td>
                                        <td><input type="text" name="item_description[]" class="form-control" /></td>
                                        <td><input type="number" name="item_price[]" class="form-control item-price" step="0.01" /></td>
                                        <td><input type="number" name="item_amount[]" class="form-control item-amount" step="0.01" /></td>
                                        <td><button type="button" class="btn btn-sm btn-danger remove-row">Delete</button></td>
                                    </tr>
                                </tbody>
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
        let itemCount = 2; // because default row is already 1

        function getSupplierPrefix() {
            const name = $('#supplier_name').val().trim();
            if (!name) return 'SUP';
            return name.split(' ')[0].toUpperCase().substring(0, 3);
        }

        function generateSupplierCode() {
            const prefix = getSupplierPrefix();
            const randomNumber = Math.floor(Math.random() * 900 + 100); // 100-999
            return `${prefix}-${randomNumber}`;
        }

        // Auto-generate supplier code and update default item code
        $('#supplier_name').on('input', function () {
            const supplierCode = generateSupplierCode();
            $('#supplier_code').val(supplierCode);

            // Update first row item code
            $('#suppliercreateTable tbody tr:first input[name="item_code[]"]').val(`${supplierCode}-001`);
        });

        // Add new item row
        $('#add-row').click(function () {
            const supplierCode = $('#supplier_code').val() || 'SUP-000';
            const paddedCount = String(itemCount).padStart(3, '0');
            const itemCode = `${supplierCode}-${paddedCount}`;

            $('#suppliercreateTable tbody').append(`
                <tr>
                    <td><input type="text" name="item_code[]" class="form-control" value="${itemCode}" readonly /></td>
                    <td><input type="text" name="item_description[]" class="form-control" /></td>
                    <td><input type="number" name="item_price[]" class="form-control item-price" step="0.01" /></td>
                    <td><input type="number" name="item_amount[]" class="form-control item-amount" step="0.01" /></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row">Delete</button></td>
                </tr>
            `);
            itemCount++;
        });

        // Delete row
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
        });
    });
</script>
@endpush



