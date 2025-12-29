@extends('layouts.master')

@section('title', 'Sales Report | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-th-list"></i> Sales Report</h1>
            <p class="text-muted mb-0">
                View sales transactions with filters by date, product, customer, and payment method. 
                Track sales performance, identify top-selling products, and monitor revenue trends. 
                Use this report to analyze sales data, support business decisions, 
                and export results to Excel for analysis and record-keeping.
            </p>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <h3 class="tile-title mb-3"><i class="fa fa-bar-chart"></i> Sales Report</h3>
                <div class="tile-body">
                    <div class="container">

                        {{-- Filters --}}
                        <form method="GET" action="{{ route('reports.sales_report') }}" class="row g-4 mb-4">
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input
                                    type="text"
                                    name="start_date"
                                    id="start_date"
                                    class="form-control"
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
                                    class="form-control"
                                    value="{{ request('end_date')
                                        ? \Carbon\Carbon::parse(request('end_date'))->format('F d, Y')
                                        : now()->format('F d, Y') }}"
                                >
                            </div>
                            <div class="col-md-2">
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
                            <div class="col-md-2">
                                <label for="customer_id" class="form-label">Customer</label>
                                <select name="customer_id" id="customer_id" class="form-control">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="salesman_name" class="form-label">Salesman</label>
                                <select name="salesman_name" id="salesman_name" class="form-control">
                                    <option value="">All Salesmen</option>
                                    @foreach($salesmen as $salesman)
                                        <option value="{{ $salesman->salesman }}" 
                                            {{ request('salesman_name') == $salesman->salesman ? 'selected' : '' }}>
                                            {{ $salesman->salesman }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Location</label>
                                <select name="location" class="form-control">
                                    <option value="">-- All Locations --</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->location }}"
                                            {{ request('location') == $loc->location ? 'selected' : '' }}>
                                            {{ $loc->location }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('reports.sales_report_export', request()->all()) }}" class="btn btn-success">
                                    <i class="fa fa-file-excel-o"></i> Export
                                </a>
                            </div>
                        </form>

                        {{-- Report Table --}}
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-striped" id="salesProductTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Location</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Discounts</th>
                                        <th>Total</th>
                                        <th>Payment Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sales as $sale)
                                        <tr>
                                            <td>{{ $sale->invoice_number }}</td>
                                            <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</td>
                                            <td>{{ $sale->customer_name }}</td>
                                            <td>{{ $sale->location }}</td>
                                            <td>{{ $sale->product_name }}</td>
                                            <td>{{ $sale->quantity }}</td>
                                            <td>{{ number_format($sale->price, 2) }}</td>
                                            <td>
                                                {{ $sale->discount_display }}
                                            </td>
                                            <td>{{ number_format($sale->total_amount, 2) }}</td>
                                            <td>{{ $sale->payment_method }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No sales found.</td>
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
<script>
    $('#salesProductTable').DataTable({
        "order": [[0, 'asc']],
        "paging": true,
        "searching": true,
        "info": true
    });
</script>
@endpush
