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
                                        <select name="filter_type" class="form-control">
                                            <option value="weekly" {{ request('filter_type')=='weekly'?'selected':'' }}>Weekly</option>
                                            <option value="monthly" {{ request('filter_type')=='monthly'?'selected':'' }}>Monthly</option>
                                            <option value="quarterly" {{ request('filter_type')=='quarterly'?'selected':'' }}>Quarterly</option>
                                            <option value="custom" {{ request('filter_type')=='custom'?'selected':'' }}>Custom</option>
                                        </select>
                                    </div>

                                    <!-- Start Date -->
                                    <div class="col-md-2">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="start_date" class="form-control"
                                            value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}">
                                    </div>

                                    <!-- End Date -->
                                    <div class="col-md-2">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="end_date" class="form-control"
                                            value="{{ request('end_date', now()->toDateString()) }}">
                                    </div>

                                    <!-- Customer -->
                                    <div class="col-md-3">
                                        <label class="form-label">Customer</label>
                                        <select name="customer_id" class="form-control">
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
                                        <select name="product_id" class="form-control">
                                            <option value="">-- All Products --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->product_name }}
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
                                            <th>Product</th>
                                            <th>Supplier</th> 
                                            <th>Qty Sold</th>
                                            <th>Qty Purchased</th>
                                            <th>Sales Price</th>
                                            <th>Purchase Price</th>
                                            <th>Total Sales</th>
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
                                                <td>{{ $row->product_name }}</td>
                                                <td>{{ $row->supplier_name ?? 'N/A' }}</td> 
                                                <td class="text-end">{{ number_format($row->quantity_sold, 0) }}</td>
                                                <td class="text-end">{{ number_format($row->quantity_purchased, 0) }}</td>
                                                <td class="text-end">{{ number_format($row->sales_price, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->purchase_price, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->total_sales, 2) }}</td>
                                                <td class="text-end fw-bold text-success">{{ number_format($row->estimated_income, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->profit_percentage ?? 0, 2) }}%</td> 
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="13" class="text-center">No records found for selected filters.</td>
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
<script>
    $('#EstimatedIncomeTable').DataTable({
        "pageLength": 25,
        "order": [[2, 'desc']],
        "responsive": true
    });
</script>
@endpush
