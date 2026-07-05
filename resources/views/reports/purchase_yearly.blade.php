@extends('layouts.master')

@section('title', 'Yearly Purchase Report')

@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title">
        <div>
            <h1>Yearly Purchase Report</h1>
            <p class="text-muted mb-0">
                This report provides a comprehensive overview of purchases made throughout the year, allowing you to analyze trends and make informed decisions. 
                You can filter the data by year, month, quarter, and supplier to get a more detailed view of your purchasing activities.
            </p>
        </div>
    </div>
    <div class="tile">
        <form method="GET" action="{{ route('reports.purchase_yearly') }}" class="row mb-3">
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-control form-control-sm">
                    @for($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ request('year', date('Y')) == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Month</label>
                <select name="month" id="month" class="form-control form-control-sm">
                    <option value="">All Months</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Quarter</label>
                <select name="quarter" id="quarter" class="form-control form-control-sm">
                    <option value="">All Quarters</option>
                    <option value="1" {{ request('quarter') == 1 ? 'selected' : '' }}>Q1 (Jan - Mar)</option>
                    <option value="2" {{ request('quarter') == 2 ? 'selected' : '' }}>Q2 (Apr - Jun)</option>
                    <option value="3" {{ request('quarter') == 3 ? 'selected' : '' }}>Q3 (Jul - Sep)</option>
                    <option value="4" {{ request('quarter') == 4 ? 'selected' : '' }}>Q4 (Oct - Dec)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select name="supplier" id="supplierSelect" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 mt-3">
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('reports.purchase_yearly') }}"
                    class="btn btn-secondary">Reset</a>
                <a href="{{ route('reports.purchase_yearly_export', request()->all()) }}"
                class="btn btn-success">
                    Export Excel
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="purchaseYearlyTable">
                <thead class="table-dark">
                    <tr>
                        <th>Supplier</th>
                        <th>Jan</th>
                        <th>Feb</th>
                        <th>Mar</th>
                        <th>Apr</th>
                        <th>May</th>
                        <th>Jun</th>
                        <th>Jul</th>
                        <th>Aug</th>
                        <th>Sep</th>
                        <th>Oct</th>
                        <th>Nov</th>
                        <th>Dec</th>
                        <th>Total Qty</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $r)
                        <tr>
                            <td>{{ $r->supplier_name }}</td>
                            <td>{{ number_format($r->january) }}</td>
                            <td>{{ number_format($r->february) }}</td>
                            <td>{{ number_format($r->march) }}</td>
                            <td>{{ number_format($r->april) }}</td>
                            <td>{{ number_format($r->may) }}</td>
                            <td>{{ number_format($r->june) }}</td>
                            <td>{{ number_format($r->july) }}</td>
                            <td>{{ number_format($r->august) }}</td>
                            <td>{{ number_format($r->september) }}</td>
                            <td>{{ number_format($r->october) }}</td>
                            <td>{{ number_format($r->november) }}</td>
                            <td>{{ number_format($r->december) }}</td>
                            <td><b>{{ number_format($r->total_quantity) }}</b></td>
                            <td><b>{{ number_format($r->total_amount, 2) }}</b></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center">No data found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
        $('#purchaseYearlyTable').DataTable({
            pageLength: 25,
            order: [[1, 'asc']],
            responsive: true,
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
        $('#supplierSelect').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: 'resolve'
        });

        // Clear Filters Button
       $('#clearFilters').on('click', function(e) {
            e.preventDefault();
            window.location.href = "{{ route('reports.purchase_yearly') }}";
        });
    });
</script>
@endpush