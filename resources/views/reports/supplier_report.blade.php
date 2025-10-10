@extends('layouts.master')

@section('title', 'Supplier Report | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-th-list"></i> Supplier Report</h1>
                <p class="text-muted mb-0">
                    View a detailed list of all Suppliers with filters by status (active or inactive) 
                    and registration date range. Use this report to monitor suppliers records, 
                    track account activity, and export results to Excel for analysis.
                </p>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <h3 class="tile-title mb-3"><i class="fa fa-bar-chart"></i> Supplier Report</h3>
                    <div class="tile-body">
                        <div class="container">
                            {{-- Filters --}}
                            <form method="GET" action="{{ route('reports.supplier_report') }}" class="row g-4 mb-4">
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option value="">-- Select Status --</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                                    <a href="{{ route('reports.supplier_report_export', request()->all()) }}" 
                                        class="btn btn-success">
                                        <i class="fa fa-file-excel-o"></i> Export
                                    </a>
                                </div>
                            </form>

                            {{-- Report Table --}}
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-striped" id="supplierReportTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Supplier ID</th>
                                            <th>Supplier Code</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Date Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($suppliers as $supplier)
                                            <tr>
                                                <td>{{ $supplier->id }}</td>
                                                <td>{{ $supplier->supplier_code }}</td>
                                                <td>{{ $supplier->name }}</td>
                                                <td>{{ $supplier->email }}</td>
                                                <td>{{ $supplier->mobile }}</td>
                                                <td>
                                                    <span class="badge {{ $supplier->status ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $supplier->status ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($supplier->created_at)->format('M d, Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No suppliers found.</td>
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
<script>
    $('#supplierReportTable').DataTable({
        "order": [[0, 'asc']],
        "paging": true,
        "searching": true,
        "info": true
    });
</script>
@endpush
