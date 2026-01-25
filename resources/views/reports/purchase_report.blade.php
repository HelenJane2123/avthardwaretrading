@extends('layouts.master')

@section('title', 'Purchase Report | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-shopping-cart"></i> Purchase Report</h1>
            <p class="text-muted mb-0">
                View all product purchases within a date range. 
                Filter by product and purchase date to track supplier orders and spending.
            </p>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <h3 class="tile-title mb-3"><i class="fa fa-list"></i> Purchase Transactions</h3>
                <div class="tile-body">
                    <div class="container">
                        {{-- Filters --}}
                        <form method="GET" action="{{ route('reports.purchase_report') }}" class="row g-3 mb-4">
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input
                                    type="text"
                                    name="start_date"
                                    id="start_date"
                                    class="form-control form-control-sm"
                                    value="{{ request('start_date')
                                        ? \Carbon\Carbon::parse(request('start_date'))->format('F d, Y')
                                        : now()->format('F d, Y') }}"
                                >
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">End Date</label>
                                <input
                                    type="text"
                                    name="end_date"
                                    id="end_date"
                                    class="form-control form-control-sm"
                                    value="{{ request('end_date')
                                        ? \Carbon\Carbon::parse(request('end_date'))->format('F d, Y')
                                        : now()->format('F d, Y') }}"
                                >
                            </div>
                             <div class="col-md-4">
                                <label for="supplier" class="form-label">Supplier</label>
                                <select name="supplier_id" id="supplier_id" class="form-control">
                                    <option value="">All Suppliers</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="product_id" class="form-label">Product</label>
                                <select name="product_id" id="product_id" class="form-control">
                                    <option value="">All Products</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('reports.purchase_report_export', request()->all()) }}" class="btn btn-success">
                                    <i class="fa fa-file-excel-o"></i> Export
                                </a>
                            </div>
                        </form>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-striped" id="purchaseTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Purchase #</th>
                                        <th>Date</th>
                                        <th>Supplier</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Payment Term</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchases as $purchase)
                                        <tr>
                                            <td>{{ $purchase->po_number }}</td>
                                            <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }}</td>
                                            <td>{{ $purchase->supplier_name }}</td>
                                            <td>{{ $purchase->product_name }}</td>
                                            <td>{{ $purchase->qty }}</td>
                                            <td>{{ number_format($purchase->unit_price, 2) }}</td>
                                            <td>{{ number_format($purchase->total_amount, 2) }}</td>
                                            <td>{{ $purchase->payment_method }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No purchases found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
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
<link rel="stylesheet" href="https://cdn.datatables.net/rowgroup/1.3.1/css/rowGroup.dataTables.min.css">
<script src="https://cdn.datatables.net/rowgroup/1.3.1/js/dataTables.rowGroup.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        let purchaseTable;
        if (!$.fn.DataTable.isDataTable('#purchaseTable')) {
            purchaseTable = $('#purchaseTable').DataTable({
                pageLength: 25,
                order: [[2, 'desc']],
                responsive: true
            });
        }
        flatpickr("#start_date", {
            dateFormat: "F d, Y",
            altInput: true,
            altFormat: "F d, Y",
            allowInput: true
        });
        flatpickr("#end_date", {
            dateFormat: "F d, Y",
            altInput: true,
            altFormat: "F d, Y",
            allowInput: true
        });
        $('#product_id').select2({
            placeholder: "Select Product",
            allowClear: true,
            width: '100%'
        });
        $('#supplier_id').select2({
            placeholder: "Select Supplier",
            allowClear: true,
            width: '100%'
        });
        // Clear Filters Button
        $('#clearFilters').on('click', function(e) {
            e.preventDefault(); // prevent form submission

            const form = $(this).closest('form')[0];

            // Reset standard inputs
            form.reset();

            // Reset Select2 dropdowns
            $(form).find('select').val(null).trigger('change');

            // Optional: reset DataTable to first page
            if (purchaseTable) {
                purchaseTable.search('').columns().search('').draw();
            }

            // Reload the page without query parameters
            window.location.href = "{{ route('reports.purchase_report') }}";
        });
    });
</script>
@endpush