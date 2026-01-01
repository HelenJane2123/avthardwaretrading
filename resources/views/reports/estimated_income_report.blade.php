@extends('layouts.master')

@section('title', 'Estimated Income | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-line-chart"></i> Estimated Income Report</h1>
                <p class="text-muted mb-0">
                    View sales and purchase comparison by date range, customer, and product. 
                    Analyze estimated profit (Sales Price − Purchase Price) × Quantity Sold to monitor income trends weekly, monthly, or quarterly.
                </p>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <h3 class="tile-title mb-3"><i class="fa fa-bar-chart"></i> Estimated Income Report</h3>
                    <div class="tile-body">
                        <div class="container">
                            {{-- Filters --}}
                            <form method="GET" action="{{ route('reports.estimated_income_report') }}">
                                <div class="row align-items-end g-2">
                                    <!-- Filter Type -->
                                    <div class="col-md-2">
                                        <label class="form-label">Filter Type</label>
                                        <select name="filter_type" class="form-control form-control-sm">
                                            <option value="weekly" {{ request('filter_type')=='weekly'?'selected':'' }}>Weekly</option>
                                            <option value="monthly" {{ request('filter_type')=='monthly'?'selected':'' }}>Monthly</option>
                                            <option value="quarterly" {{ request('filter_type')=='quarterly'?'selected':'' }}>Quarterly</option>
                                            <option value="custom" {{ request('filter_type')=='custom'?'selected':'' }}>Custom</option>
                                        </select>
                                    </div>

                                    <!-- Start Date -->
                                    <div class="col-md-2">
                                        <label class="form-label">Start Date</label>
                                        <input
                                            type="text"
                                            name="start_date"
                                            id="start_date"
                                            class="form-control form-control-sm"
                                            value="{{ request('start_date')
                                                ? \Carbon\Carbon::parse(request('start_date'))->format('F d, Y')
                                                : now()->startOfMonth()->format('F d, Y') }}"
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

                                    <!-- Product -->
                                    <div class="col-md-3">
                                        <label class="form-label">Product</label>
                                        <select name="product_id" id="productSelect" class="form-control form-control-sm">
                                            <option value="">-- All Products --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->product_name }}
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

                                    <!-- Buttons -->
                                    <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                                        <a href="{{ route('reports.estimated_income_export', request()->all()) }}" class="btn btn-success">
                                            <i class="fa fa-file-excel-o"></i> Export
                                        </a>
                                        <button type="button" id="clearFilters" class="btn btn-secondary">
                                            <i class="fa fa-eraser"></i> Clear Filters
                                        </button>
                                    </div>
                                </div>
                            </form>

                            {{-- Report Table --}}
                            <div class="table-responsive mt-4">
                                <table class="table table-striped table-hover table-bordered" id="EstimatedIncomeTable">
                                    <thead class="table-dark text-center align-middle">
                                       <tr>
                                            <th>Invoice #</th>
                                            <th>Purchase #</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Location</th>
                                            <th>Product</th>
                                            <th>Supplier</th> 
                                            <th>Qty Sold</th>
                                            <!-- <th>Qty Purchased</th> -->
                                            <th>Sale Price</th>
                                            <th>Sales Net Price</th>
                                            <th>Purchase Price</th>
                                            <th>Purchase Net Price</th>
                                            <th>Estimated Income</th>
                                            <th>Profit %</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reportData as $row)
                                            <tr>
                                                <td>{{ $row->invoice_number }}</td>
                                                <td>{{ $row->purchase_number ?? 'N/A' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($row->invoice_date)->format('Y-m-d') }}</td>
                                                <td>{{ $row->customer_name }}</td>
                                                <td>{{ $row->customer_location }}</td>
                                                <td>{{ $row->product_name }}</td>
                                                <td>{{ $row->supplier_name ?? 'N/A' }}</td> 
                                                <td class="text-end">{{ number_format($row->quantity_sold, 0) }}</td>
                                                <!-- <td class="text-end">{{ number_format($row->quantity_purchased ?? 0, 0) }}</td> -->
                                                <td class="text-end">{{ number_format($row->sales_net_price ?? 0, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sales_net_of_net ?? 0, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->net_price ?? 0, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->purchase_net_of_net ?? 0, 2) }}</td>
                                                <td class="text-end fw-bold text-success">{{ number_format($row->estimated_income ?? 0, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->profit_percentage ?? 0, 2) }}%</td> 
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="14" class="text-center">No records found for selected filters.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                   @if(isset($summary))
                                    <tfoot class="table-secondary fw-bold">
                                        <tr>
                                            <td colspan="10" class="text-end">Grand Total:</td>
                                            <td class="text-end">{{ number_format($summary->total_sales ?? 0, 2) }}</td>
                                            <td class="text-end text-success">{{ number_format($summary->total_income ?? 0, 2) }}</td>
                                            <td class="text-end">{{ number_format($summary->average_profit ?? 0, 2) }}%</td> 
                                        </tr>
                                    </tfoot>
                                    @endif
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable safely
        let estimatedIncomeTable;
        if (!$.fn.DataTable.isDataTable('#EstimatedIncomeTable')) {
            estimatedIncomeTable = $('#EstimatedIncomeTable').DataTable({
                pageLength: 25,
                order: [[2, 'desc']],
                responsive: true
            });
        }

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
        $('#customerSelect, #productSelect, #locationSelect').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: 'resolve'
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
            if (estimatedIncomeTable) {
                estimatedIncomeTable.search('').columns().search('').draw();
            }

            // Reload the page without query parameters
            window.location.href = "{{ route('reports.estimated_income_report') }}";
        });
    });
</script>
@endpush
