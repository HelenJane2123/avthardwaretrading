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
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
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

                        {{-- Report Table --}}
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
                                            <td>
                                                @if(in_array(strtolower($purchase->name), ['cash', 'gcash']))
                                                    {{ $purchase->name }}
                                                @else
                                                    {{ $purchase->name }} - {{ $purchase->term }} Days
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No purchases found.</td>
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
    $('#purchaseTable').DataTable({
        "order": [[1, 'desc']], // Sort by Date
        "paging": true,
        "searching": true,
        "info": true
    });
</script>
@endpush