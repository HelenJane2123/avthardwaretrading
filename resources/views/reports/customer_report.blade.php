@extends('layouts.master')

@section('title', 'Customer Report | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-th-list"></i> Customer Report</h1>
                <p class="text-muted mb-0">
                    View a detailed list of all customers with filters by status (active or inactive) 
                    and registration date range. Use this report to monitor customer records, 
                    track account activity, and export results to Excel for analysis.
                </p>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile shadow-sm">
                    <h3 class="tile-title mb-3"><i class="fa fa-bar-chart"></i> Customer Report</h3>
                    <div class="tile-body">
                        <div class="container">
                            {{-- Filters --}}
                            <form method="GET" action="{{ route('reports.customer_report') }}" class="row g-4 mb-4">
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
                                    <a href="{{ route('reports.customer_report_export', request()->all()) }}" 
                                        class="btn btn-success">
                                        <i class="fa fa-file-excel-o"></i> Export
                                    </a>
                                </div>
                            </form>

                            {{-- Report Table --}}
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered table-striped" id="customerReportTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Customer ID</th>
                                            <th>Customer Code</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Date Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customers as $customer)
                                            <tr>
                                                <td>{{ $customer->id }}</td>
                                                <td>{{ $customer->customer_code }}</td>
                                                <td>{{ $customer->name }}</td>
                                                <td>{{ $customer->email }}</td>
                                                <td>{{ $customer->mobile }}</td>
                                                <td>
                                                    <span class="badge {{ $customer->status ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $customer->status ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($customer->created_at)->format('M d, Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No customers found.</td>
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
    $('#customerReportTable').DataTable({
        "order": [[0, 'asc']],
        "paging": true,
        "searching": true,
        "info": true
    });
</script>
@endpush
