@extends('layouts.master')

@section('title', 'Sales Summary Report | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-th-list"></i> Sales Summary Report</h1>
                <p class="text-muted mb-0">
                    View a detailed summary of sales invoices including totals and statuses. Filtered by date range,
                    salesman, and customer. Export the report to Excel for further analysis.
                </p>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <h3 class="tile-title mb-3"><i class="fa fa-bar-chart"></i> Sales Summary Report</h3>
                    <div class="tile-body">
                        <div class="container">
                            {{-- Filters --}}
                            <form method="GET" action="{{ route('reports.sales_invoice_summary_report') }}" class="row g-4 mb-4">
                                <div class="col-md-2">
                                    <label class="form-label">End Date</label>    
                                    <!-- Start Date -->
                                    <input
                                        type="text"
                                        name="start_date"
                                        id="start_date"
                                        class="form-control form-control-sm"
                                        value="{{ now()->startOfYear()->format('F d, Y') }}"
                                        readonly
                                    >
                                </div>
                                <!-- End Date -->
                                <div class="col-md-2">
                                    <label class="form-label">End Date</label>
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
                                    <label class="form-label">Invoice Status</label>
                                    <select name="status" id="statusSelect" class="form-control">
                                        <option value="">-- Select Status --</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="printed" {{ request('status') == 'printed' ? 'selected' : '' }}>Printed</option>
                                        <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Salesman</label>
                                    <select name="salesman" id="salesmanSelect" class="form-control">
                                        <option value="">-- Select Salesman --</option>
                                        @foreach($salesmen as $salesman)
                                            <option value="{{ $salesman->salesman }}" {{ request('salesman') == $salesman->salesman ? 'selected' : '' }}>
                                                {{ $salesman->salesman_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Location -->
                                <div class="col-md-3">
                                    <label class="form-label">Location</label>
                                    <select name="location" id="locationSelect" class="form-control form-control-sm">
                                        <option value="">-- All Locations --</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->location }}"
                                                {{ request('location') == $loc->location ? 'selected' : '' }}>
                                                {{ $loc->location }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Customer -->
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
                                <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                                    <a href="{{ route('reports.sales_invoice_summary_report_export', request()->all()) }}" class="btn btn-success">
                                        <i class="fa fa-file-excel-o"></i> Export
                                    </a>
                                    <button type="button" id="clearFilters" class="btn btn-secondary">
                                        <i class="fa fa-eraser"></i> Clear Filters
                                    </button>
                                </div>
                            </form>

                            {{-- Report Table --}}
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-striped" id="summaryinvoiceReportTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Invoice Number</th>
                                            <th>Customer Name</th>
                                            <th>Invoice Date</th>
                                            <th>Invoice Due Date</th>
                                            <th>Invoice Status</th>
                                            <th>Location</th>
                                            <th>Salesman Name</th>
                                            <th>Payment Method</th>
                                            <th>Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sales as $invoice)
                                            <tr>
                                                <td>{{ $invoice->dr_no }}</td>
                                                <td>{{ $invoice->customer_name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</td>
                                                <td>
                                                    <span 
                                                        class="badge 
                                                        @if($invoice->invoice_status == 'pending') bg-warning 
                                                        @elseif($invoice->invoice_status == 'approved') bg-success 
                                                        @elseif($invoice->invoice_status == 'printed') bg-info 
                                                        @elseif($invoice->invoice_status == 'canceled') bg-danger 
                                                        @else bg-secondary 
                                                        @endif">
                                                        {{ $invoice->invoice_status }}
                                                    </span>
                                                </td>
                                                <td>{{ $invoice->location }}</td>
                                                <td>{{ $invoice->salesman_name }}</td>
                                                <td>
                                                    {{ $invoice->payment_method }}
                                                    @if(!in_array(strtolower($invoice->payment_method), ['Gcash', 'Cash']))
                                                        - {{ $invoice->payment_term }}
                                                    @endif
                                                </td>
                                                <td>{{ number_format($invoice->grand_total, 2) }}</td>
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
        // Initialize DataTable safely
        $('#summaryinvoiceReportTable').DataTable({
            pageLength: 25,
            order: [[1, 'asc']],
            responsive: true,
            rowGroup: {
                dataSrc: 1,
                endRender: function (rows, group) {
                    if (rows.count() === 0) {
                        return null;
                    }
                    let total = rows
                        .data()
                        .pluck(8)
                        .reduce(function (a, b) {
                            return parseFloat(a) + parseFloat(b.replace(/,/g, ''));
                        }, 0);

                   return $('<tr class="subtotal-row"/>')
                    .append('<td colspan="8" class="text-end fw-bold">Subtotal for ' + group + '</td>')
                    .append('<td class="fw-bold text-primary">' + 
                        total.toLocaleString(undefined, {minimumFractionDigits: 2}) + 
                    '</td>');
                }
            },
            drawCallback: function(settings) {
                var api = this.api();
                if (api.rows({ page: 'current' }).count() === 0) {
                    $('.subtotal-row').remove();
                }
            }
        });
        // Initialize Flatpickr
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

        // Initialize Select2
        $('#customerSelect, #locationSelect, #salesmanSelect,#statusSelect').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: 'resolve'
        });

        // Clear Filters Button
       $('#clearFilters').on('click', function(e) {
            e.preventDefault();
            window.location.href = "{{ route('reports.sales_invoice_summary_report') }}";
        });
    });
</script>
@endpush
