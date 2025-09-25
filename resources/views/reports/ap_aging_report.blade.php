@extends('layouts.master')

@section('title', 'Purchase | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-th-list"></i> AP Aging Report</h1>
                <p class="text-muted mb-0">
                    View outstanding purchase invoices by supplier and payment method. 
                    Track balances across aging buckets (Current, 1–30, 31–60, 61–90, 90+ days) 
                    to monitor unpaid amounts and manage supplier payments efficiently. 
                    Export the results to Excel for reporting and reconciliation.
                </p>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <h3 class="tile-title mb-3"><i class="fa fa-bar-chart"></i> Accounts Payable Aging Report</h3>
                    <div class="tile-body">
                        <div class="container">
                            {{-- Filters --}}
                            <form method="GET" action="{{ route('reports.ap_aging_report') }}">
                                <div class="row align-items-end g-2">
                                    <!-- Customer Filter -->
                                    <div class="col-md-3">
                                        <label for="supplier_id" class="form-label">Customer</label>
                                        <select name="supplier_id" class="form-control">
                                            <option value="">-- All Suppliers --</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" 
                                                    {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Payment Method Filter -->
                                    <div class="col-md-3">
                                        <label for="payment_id" class="form-label">Payment Method</label>
                                        <select name="payment_id" class="form-control">
                                            <option value="">-- All Payment Methods --</option>
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method->id }}"
                                                    {{ request('payment_id') == $method->id ? 'selected' : '' }}>
                                                    {{ $method->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- As of Date Filter -->
                                    <div class="col-md-3">
                                        <label for="as_of_date" class="form-label">As of Date</label>
                                        <input type="date" name="as_of_date" class="form-control"
                                            value="{{ request('as_of_date', now()->toDateString()) }}">
                                    </div>

                                    <!-- Buttons -->
                                    <div class="col-md-3 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="{{ route('reports.ap_aging_export', request()->all()) }}" 
                                            class="btn btn-success">
                                            <i class="fa fa-file-excel-o"></i> Export
                                        </a>
                                    </div>
                                </div>
                            </form>
                            {{-- Report Table --}}
                            <div class="table-responsive mt-3">
                                <table class="table table-striped table-hover table-bordered" id="ApAgingTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Supplier Code</th>
                                            <th>Supplier</th>
                                            <th>Purchase #</th>
                                            <th>Purchase Date</th>
                                            <th>Purchase Amount</th>
                                            <th>Outstanding Balance</th>
                                            <th>Amount Paid</th>
                                            <th>Payment Date</th>
                                            <th>Payment Method</th>
                                            <th>Payment Status</th>
                                            <th>Payment Term</th>
                                            <th>Aging Bucket</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $lastSupplier = ''; @endphp
                                        @forelse($agingData as $row)
                                            <tr>
                                                <td>{{ $row->supplier_code }}</td>
                                                <td>{{ $row->supplier_name }}</td>
                                                <td>{{ $row->purchase_number }}</td>
                                                <td>{{ \Carbon\Carbon::parse($row->purchase_date)->format('Y-m-d') }}</td>
                                                <td>{{ number_format($row->purchase_amount, 2) }}</td>
                                                <td>{{ number_format($row->outstanding_balance, 2) }}</td>
                                                <td>{{ number_format($row->amount_paid ?? 0, 2) }}</td>
                                                <td>{{ $row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $row->payment_method ?? '-' }}</td>
                                                <td>{{ $row->payment_status ?? '-' }}</td>
                                                <td>{{ $row->payment_term ?? '-' }}</td>
                                                <td><span class="badge bg-info">{{ $row->aging_bucket }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center">No records found.</td>
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
<script src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
<script src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/rowgroup/1.3.1/css/rowGroup.dataTables.min.css">
<script src="https://cdn.datatables.net/rowgroup/1.3.1/js/dataTables.rowGroup.min.js"></script>
<script>
    $('#ApAgingTable').DataTable({
        "order": [[0, 'asc']], // Sort by Supplier Code
        "rowGroup": {
            dataSrc: 0, // Group by first column (Supplier Code)
            startRender: function (rows, group) {
                // Optionally display Supplier Name in the group header
                var supplierName = rows.data()[0][1]; // Second column = Supplier Name
                return group + ' - ' + supplierName + ' (' + rows.count() + ' purchases)';
            }
        },
        "columnDefs": [
            { "visible": false, "targets": 0 } // Hide Supplier Code if desired
        ],
        "paging": true,
        "searching": true,
        "info": true
    });
</script>
@endpush

