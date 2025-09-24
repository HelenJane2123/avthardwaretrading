@extends('layouts.master')

@section('title', 'Purchase | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                 <h1><i class="fa fa-th-list"></i> AR Aging Report</h1>
                <p class="text-muted mb-0">
                    View outstanding invoices by customer, payment method, and due date. 
                    Track balances across aging buckets (Current, 1–30, 31–60, 61–90, 90+ days) 
                    and export the results to Excel for reporting.
                </p>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <h3 class="tile-title mb-3"><i class="fa fa-bar-chart"></i> Accounts Receivable Aging Report</h3>
                    <div class="tile-body">
                        <div class="container">
                            {{-- Filters --}}
                            <form method="GET" action="{{ route('reports.ar_aging_report') }}">
                                <div class="row align-items-end g-2">
                                    <!-- Customer -->
                                    <div class="col-md-3">
                                        <label for="customer_id" class="form-label">Customer</label>
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

                                    <!-- Payment Method -->
                                    <div class="col-md-3">
                                        <label for="payment_mode_id" class="form-label">Payment Method</label>
                                        <select name="payment_mode_id" class="form-control">
                                            <option value="">-- All Payment Methods --</option>
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method->id }}"
                                                    {{ request('payment_mode_id') == $method->id ? 'selected' : '' }}>
                                                    {{ $method->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- As of Date -->
                                    <div class="col-md-3">
                                        <label for="as_of_date" class="form-label">As of Date</label>
                                        <input type="date" name="as_of_date" class="form-control"
                                            value="{{ request('as_of_date', now()->toDateString()) }}">
                                    </div>

                                    <!-- Buttons -->
                                    <div class="col-md-3 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="{{ route('reports.ar_aging_export', request()->all()) }}" 
                                            class="btn btn-success">
                                            <i class="fa fa-file-excel-o"></i> Export
                                        </a>
                                    </div>
                                </div>
                            </form>

                            {{-- Report Table --}}
                            <div class="table-responsive mt-3">
                                <table class="table table-striped table-hover table-bordered" id="ArAgingTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Customer Code</th>
                                            <th>Customer</th>
                                            <th>Invoice #</th>
                                            <th>Invoice Date</th>
                                            <th>Due Date</th>
                                            <th>Invoice Amount</th>
                                            <th>Outstanding</th>
                                            <th>Amount Paid</th>
                                            <th>Collection Date</th>
                                            <th>Remarks</th>
                                            <th>Payment Method</th>
                                            <th>Payment Term</th>
                                            <th>Aging Bucket</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($agingData as $row)
                                            <tr>
                                                <td>{{ $row->customer_code }}</td>
                                                <td>{{ $row->customer_name }}</td>
                                                <td>{{ $row->invoice_number }}</td>
                                                <td>{{ \Carbon\Carbon::parse($row->invoice_date)->format('Y-m-d') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($row->due_date)->format('Y-m-d') }}</td>
                                                <td class="text-end">{{ number_format($row->invoice_amount, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->outstanding_balance, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->amount_paid ?? 0, 2) }}</td>
                                                <td>{{ $row->collection_date ? \Carbon\Carbon::parse($row->collection_date)->format('Y-m-d') : '' }}</td>
                                                <td>{{ $row->collection_remarks ?? '-' }}</td>
                                                <td>{{ $row->payment_method ?? '-' }}</td>
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
<script>
    $('#ArAgingTable').DataTable();
</script>
@endpush

