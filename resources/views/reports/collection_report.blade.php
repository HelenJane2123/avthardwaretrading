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
                            <div class="col-md-3">
                                <label class="form-label">Customer</label>
                                <select name="customer_id" id="customerSelect" class="form-control form-control-sm">
                                    <option value="">-- All Customers --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
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
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-striped" id="collectionTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Collection #</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Invoice Number</th>
                                        <th>Salesman</th>
                                        <th>Amount Collected(₱)</th>
                                        <th>Adjustment</th>
                                        <th>Adjustment Name</th>
                                        <th>Adjustment Date</th>
                                        <th>Adjustment Amount</th>
                                        <th>Adjustment Remarks</th>
                                        <th>Payment Mode</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportData as $collection)
                                        <tr>
                                            <td>{{ $collection->collection_number ?? '-' }}</td>
                                            <td>{{ $collection->collection_date ? \Carbon\Carbon::parse($collection->collection_date)->format('M d, Y') : '-' }}</td>
                                            <td>{{ $collection->customer_name ?? '-' }}</td>
                                            <td>{{ $collection->invoice_number ?? '-' }}</td>
                                            <td>{{ $collection->salesman ?? '-' }}</td>
                                            <td>{{ $collection->amount_collected ? number_format($collection->amount_collected, 2) : '-' }}</td>
                                            <td>{{ $collection->adjustment_type ?? '-' }}</td>
                                            <td>{{ $collection->adjustment_name ?? '-' }}</td>
                                            <td>{{ $collection->adjustment_date ? \Carbon\Carbon::parse($collection->adjustment_date)->format('M d, Y') : '-' }}</td>
                                            <td>{{ $collection->adjustment_amount ? number_format($collection->adjustment_amount, 2) : '-' }}</td>
                                            <td>{{ $collection->adjustment_remarks ?? '-' }}</td>
                                            <td>{{ $collection->payment_mode ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="14" class="text-center">No collections found.</td>
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
    let collectionTable;
    if (!$.fn.DataTable.isDataTable('#collectionTable')) {
        collectionTable = $('#collectionTable').DataTable({
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

    $('#customerSelect').select2({
        placeholder: "Select an option",
        allowClear: true,
        width: '100%'
    });
});
</script>
@endpush
