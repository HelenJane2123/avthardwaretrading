@extends('layouts.master')

@section('title', 'Supplier | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-edit"></i> Edit Supplier</h1>
                <p class="text-muted mb-0">Update supplier details and information</p>
            </div>
            <ul class="app-breadcrumb breadcrumb">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Supplier</li>
                <li class="breadcrumb-item"><a href="#">Edit Supplier</a></li>
            </ul>
        </div>

        @if(session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title">Supplier Information</h3>
                    <small class="text-muted">Fields marked with <span class="text-danger">*</span> are required</small>
                    <div class="tile-body">
                        <form method="POST" action="{{ route('supplier.update', $supplier->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>Supplier Code</label>
                                    <input id="supplier_code" class="form-control" type="text" name="supplier_code"
                                           value="{{ $supplier->supplier_code }}" readonly>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Supplier Name</label>
                                    <input id="supplier_name" class="form-control" type="text" name="name"
                                           value="{{ $supplier->name }}">
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Contact</label>
                                    <input class="form-control" type="text" name="mobile" value="{{ $supplier->mobile }}">
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Email</label>
                                    <input class="form-control" type="email" name="email" value="{{ $supplier->email }}">
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Tax ID</label>
                                    <input class="form-control" type="text" name="tax" value="{{ $supplier->tax }}">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Address</label>
                                    <textarea class="form-control" name="address">{{ $supplier->address }}</textarea>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Details</label>
                                    <textarea class="form-control" name="details">{{ $supplier->details }}</textarea>
                                </div>
                            </div>

                            <h5 class="mt-4">Item Details</h5>
                            <table class="table table-bordered" id="supplierEditTable">
                                <thead>
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
                                            Add Item
                                        </button>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($supplier->items as $key => $item)
                                    <tr>
                                        <td>
                                            <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                            <input type="text" name="item_code[]" class="form-control"
                                                   value="{{ $item->item_code }}" readonly>
                                        </td>

                                        <td>
                                            <select name="category_id[]" class="form-control">
                                                <option value="">Select</option>
                                                @foreach ($categories as $cat)
                                                    <option value="{{ $cat->id }}"
                                                            {{ $cat->id == $item->category_id ? 'selected' : '' }}>
                                                        {{ $cat->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input type="text" name="item_description[]" class="form-control"
                                                   value="{{ $item->item_description }}">
                                        </td>

                                        <td>
                                            <input type="number" name="item_qty[]" class="form-control qty"
                                                   value="{{ $item->item_qty }}">
                                        </td>

                                        <td>
                                            <select name="unit_id[]" class="form-control">
                                                <option value="">Select</option>
                                                @foreach ($units as $unit)
                                                    <option value="{{ $unit->id }}"
                                                            {{ $unit->id == $item->unit_id ? 'selected' : '' }}>
                                                        {{ $unit->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input type="number" name="item_price[]" class="form-control price"
                                                   step="0.01" value="{{ $item->item_price }}">
                                        </td>

                                        <td>
                                            <input type="number" name="item_amount[]" class="form-control amount"
                                                   step="0.01" value="{{ $item->item_amount }}" readonly>
                                        </td>

                                        <td>
                                            @if($item->item_image)
                                                <img src="{{ asset('storage/' . $item->item_image) }}" width="60" class="mb-1">
                                            @endif
                                            <input type="file" name="item_image[]" class="form-control" accept="image/*">
                                        </td>

                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-row">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end fw-bold">Total Amount:</td>
                                    <td>
                                        <input type="text" class="form-control" id="totalAmount" readonly>
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                </tfoot>
                            </table>

                            <div class="form-group text-end">
                                <button class="btn btn-success" type="submit">
                                    <i class="fa fa-fw fa-lg fa-check-circle"></i> Update Supplier
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
        let table = $('#supplierEditTable').DataTable({
            paging: false,
            searching: true,  // ✅ search enabled
            ordering: false   // (optional: disable sorting if not needed for inputs)
        });
        let itemCount = {{ isset($supplier->items) ? count($supplier->items) + 1 : 1 }};
        const categories = @json($categories);
        const units = @json($units);

        // Generate Supplier Code
        function generateSupplierCode(name) {
            if (!name) return '';
            return name.toUpperCase().replace(/\s+/g, '-').substring(0, 5) + '-' +
                String(Math.floor(Math.random() * 900 + 100));
        }

        function updateSupplierCode() {
            const name = $('#supplier_name').val();
            if (name && !$('#supplier_code').val()) {
                $('#supplier_code').val(generateSupplierCode(name));
            }
        }

        $('#supplier_name').on('input', updateSupplierCode);

        // Compute Amount for row
        function computeAmount(row) {
            let qty = parseFloat($(row).find('.qty').val()) || 0;
            let price = parseFloat($(row).find('.price').val()) || 0;
            let amount = (qty * price).toFixed(2);
            $(row).find('.amount').val(amount);
        }

        // Update Total
        function updateTotalAmount() {
            let total = 0;
            $('.amount').each(function () {
                total += parseFloat($(this).val()) || 0;
            });
            $('#totalAmount').val(total.toFixed(2));
        }

        // Add Row
        $('#add-row').click(function () {
            let supplierCode = $('#supplier_code').val() || 'SUP';
            let itemCode = supplierCode + '-' + String(itemCount).padStart(3, '0');

            let categoryOptions = categories.map(cat =>
                `<option value="${cat.id}">${cat.name}</option>`).join('');

            let unitOptions = units.map(unit =>
                `<option value="${unit.id}">${unit.name}</option>`).join('');

            let row = `
                <tr>
                    <td>
                        <input type="hidden" name="item_ids[]" value="">
                        <input type="text" name="item_code[]" class="form-control" value="${itemCode}" readonly>
                    </td>
                    <td>
                        <select name="category_id[]" class="form-control">
                            <option value="">Select</option>
                            ${categoryOptions}
                        </select>
                    </td>
                    <td><input type="text" name="item_description[]" class="form-control"></td>
                    <td><input type="number" name="item_qty[]" class="form-control qty" min="0"></td>
                    <td>
                        <select name="unit_id[]" class="form-control">
                            <option value="">Select</option>
                            ${unitOptions}
                        </select>
                    </td>
                    <td><input type="number" name="item_price[]" class="form-control price" step="0.01" min="0"></td>
                    <td><input type="number" name="item_amount[]" class="form-control amount" step="0.01" readonly></td>
                    <td><input type="file" name="item_image[]" class="form-control" accept="image/*"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row">Delete</button></td>
                </tr>
            `;

            $('#supplierEditTable tbody').append(row);
            itemCount++;
        });

        // Remove Row
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            updateTotalAmount();
        });

        // Update on Qty/Price change
        $(document).on('input', '.qty, .price', function () {
            let row = $(this).closest('tr');
            computeAmount(row);
            updateTotalAmount();
        });

        // On Page Load
        $(document).ready(function () {
            $('#supplierEditTable tbody tr').each(function () {
                computeAmount(this);
            });
            updateTotalAmount();
        });
    </script>
@endpush
