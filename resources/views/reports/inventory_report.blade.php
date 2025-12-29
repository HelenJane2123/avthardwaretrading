@extends('layouts.master')

@section('title', 'Purchase | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-th-list"></i> Inventory Report</h1>
                <p class="text-muted mb-0">
                    View current product inventory with filters by status, category, and supplier. 
                    Track stock levels to identify items that are in stock, low stock, or out of stock. 
                    Use this report to monitor product availability, support restocking decisions, 
                    and export results to Excel for analysis and record-keeping.
                </p>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <h3 class="tile-title mb-3"><i class="fa fa-bar-chart"></i> Inventory Report</h3>
                    <div class="tile-body">
                        <div class="container">
                            {{-- Filters --}}
                            <form method="GET" action="{{ route('reports.inventory_report') }}" class="row g-3 mb-4">
                                {{-- Status Filter --}}
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="All" {{ request('status') == 'All' ? 'selected' : '' }}>All</option>
                                        <option value="In Stock" {{ request('status') == 'In Stock' ? 'selected' : '' }}>In Stock</option>
                                        <option value="Out of Stock" {{ request('status') == 'Out of Stock' ? 'selected' : '' }}>Out of Stock</option>
                                        <option value="Low Stock" {{ request('status') == 'Low Stock' ? 'selected' : '' }}>Low Stock</option>
                                    </select>
                                </div>

                                {{-- Category Filter --}}
                                <div class="col-md-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select name="category_id" id="category_id" class="form-control">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Supplier Filter --}}
                                <div class="col-md-3">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select name="supplier_id" id="supplier_id" class="form-control">
                                        <option value="">All Suppliers</option>
                                        @foreach($suppliers as $sup)
                                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                                                {{ $sup->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                                    <a href="{{ route('reports.inventory_report_export', request()->all()) }}" 
                                        class="btn btn-success">
                                        <i class="fa fa-file-excel-o"></i> Export
                                    </a>
                                </div>
                            </form>
                            {{-- Report Table --}}
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-striped" id="inventoryProductTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Product Code</th>
                                            <th>Product Name</th>
                                            <th>Category</th>
                                            <th>Unit</th>
                                            <th>Supplier</th>
                                            <th>Sales Price</th>
                                            <th>Quantity</th>
                                            <th>Remaining Stock</th>
                                            <th>Threshold</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($inventory as $item)
                                            <tr>
                                                <td>{{ $item->product_code }} - {{ $item->product_name }}</td>
                                                <td>{{ $item->product_code }} - {{ $item->product_name }}</td>
                                                <td>{{ $item->category_name }}</td>
                                                <td>{{ $item->unit_name }}</td>
                                                <td>{{ $item->supplier_name }}</td>
                                                <td>{{ number_format($item->sales_price, 2) }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ $item->remaining_stock }}</td>
                                                <td>{{ $item->threshold }}</td>
                                                <td>
                                                    @if($item->product_status == 'Out of Stock')
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    @elseif($item->product_status == 'Low Stock')
                                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                                    @else
                                                        <span class="badge bg-success">In Stock</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No data found.</td>
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
    $('#inventoryProductTable').DataTable({
        "order": [[0, 'asc']], // Sort by Supplier Code
        "rowGroup": {
            dataSrc: 4, // Group by first column (Supplier Code)
            startRender: function (rows, group) {
                var productCat = rows.data()[0][1];
                return group;
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

