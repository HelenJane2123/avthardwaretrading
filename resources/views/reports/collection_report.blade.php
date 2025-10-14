@extends('layouts.master')

@section('title', 'Collection Report | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-money-bill-wave"></i> Collection Report</h1>
            <p class="text-muted mb-0">
                View all collected payments from customers within a selected date range. 
                Filter by salesman, customer, or product to track collections, monitor performance, 
                and analyze payment trends.
            </p>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <h3 class="tile-title mb-3"><i class="fa fa-list"></i> Collection Transactions</h3>
                <div class="tile-body">
                    <div class="container">
                        {{-- Filters --}}
                        <form method="GET" action="{{ route('reports.collection_report') }}" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="salesman" class="form-label">Salesman</label>
                                <select name="salesman" id="salesman" class="form-control">
                                    <option value="">All</option>
                                    @foreach($salesmen as $salesman)
                                        <option value="{{ $salesman->salesman }}" {{ request('salesman') == $salesman->salesman ? 'selected' : '' }}>
                                            {{ $salesman->salesman }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="customer_id" class="form-label">Customer</label>
                                <select name="customer_id" id="customer_id" class="form-control">
                                    <option value="">All</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="product_id" class="form-label">Product</label>
                                <select name="product_id" id="product_id" class="form-control">
                                    <option value="">All</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 d-flex justify-content-end align-items-end mt-2">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('reports.collection_report_export', request()->all()) }}" class="btn btn-success">
                                    <i class="fa fa-file-excel-o"></i> Export
                                </a>
                            </div>
                        </form>

                        {{-- Report Table --}}
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-striped" id="collectionTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Collection #</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Invoice Number</th>
                                        <th>Salesman</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Amount Collected(₱)</th>
                                        <th>Payment Mode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData as $collection)
                                        <tr>
                                            <td>{{ $collection->collection_number }}</td>
                                            <td>{{ \Carbon\Carbon::parse($collection->collection_date)->format('M d, Y') }}</td>
                                            <td>{{ $collection->customer_name }}</td>
                                            <td>{{ $collection->invoice_number }}</td>
                                            <td>{{ $collection->salesman }}</td>
                                            <td>{{ $collection->product_name }}</td>
                                            <td>{{ $collection->qty }}</td>
                                            <td>{{ number_format($collection->amount_collected, 2) }}</td>
                                            <td>{{ $collection->payment_mode }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No collections found.</td>
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
<script src="{{ asset('/js/plugins/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('/js/plugins/dataTables.bootstrap.min.js') }}"></script>
<script>
    $('#collectionTable').DataTable({
        "order": [[1, 'desc']], // Sort by Date
        "paging": true,
        "searching": true,
        "info": true
    });
</script>
@endpush
