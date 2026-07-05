@extends('layouts.master')

@section('title', 'Yearly Sales Report by Customer | ')

@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">

    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="fa fa-money-bill-wave"></i>
                Yearly Sales Report by Customer
            </h1>
            <p class="text-muted mb-0">
                View total sales for each customer broken down by month for a selected year.
                 This report helps you analyze customer buying patterns, identify seasonal trends,
                    and evaluate customer performance over the year.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">
                <form method="GET"
                    action="{{ route('reports.customer_sales_yearly') }}"
                    class="d-flex flex-column align-items-center gap-4 mb-4">

                    {{-- FILTERS ROW --}}
                    <div class="d-flex flex-wrap justify-content-center gap-4 w-100">

                        {{-- YEAR --}}
                        <div class="d-flex flex-column" style="min-width:140px;">
                            <label class="form-label mb-1">Year</label>
                            <select name="year" class="form-control form-control-sm w-100">
                                @for ($i = date('Y'); $i >= 2020; $i--)
                                    <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- MONTH --}}
                        <div class="d-flex flex-column" style="min-width:160px;">
                            <label class="form-label mb-1">Month</label>
                            <select name="month" class="form-control form-control-sm w-100">
                                <option value="">All Months</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                        {{ date('F', mktime(0,0,0,$i,1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- QUARTER --}}
                        <div class="d-flex flex-column" style="min-width:160px;">
                            <label class="form-label mb-1">Quarter</label>
                            <select name="quarter" class="form-control form-control-sm w-100">
                                <option value="">All Quarters</option>
                                <option value="1" {{ request('quarter') == 1 ? 'selected' : '' }}>Q1</option>
                                <option value="2" {{ request('quarter') == 2 ? 'selected' : '' }}>Q2</option>
                                <option value="3" {{ request('quarter') == 3 ? 'selected' : '' }}>Q3</option>
                                <option value="4" {{ request('quarter') == 4 ? 'selected' : '' }}>Q4</option>
                            </select>
                        </div>

                        {{-- LOCATION --}}
                        <div class="d-flex flex-column" style="min-width:220px;">
                            <label class="form-label mb-1">Location</label>
                            <select name="location" id="locationSelect" class="form-control form-control-sm w-100">
                                <option value="">All Locations</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->location }}"
                                        {{ request('location') == $loc->location ? 'selected' : '' }}>
                                        {{ $loc->location }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SALESMAN --}}
                        <div class="d-flex flex-column" style="min-width:220px;">
                            <label class="form-label mb-1">Salesman</label>
                            <select name="salesman" id="salesman" class="form-control form-control-sm w-100">
                                <option value="">All Salesmen</option>
                                @foreach($salesman as $s)
                                    <option value="{{ $s->salesman }}"
                                        {{ request('salesman') == $s->salesman ? 'selected' : '' }}>
                                        {{ $s->salesman_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- BUTTONS ROW --}}
                    <div class="d-flex gap-2 mt-2">

                        <button type="submit" class="btn btn-primary">
                            Filter
                        </button>

                        <a href="{{ route('reports.customer_sales_yearly') }}"
                        class="btn btn-secondary">
                            Reset
                        </a>

                        <a href="{{ route('reports.customer_sales_yearly_export', request()->all()) }}"
                        class="btn btn-success">
                            Export Excel
                        </a>

                    </div>

                </form>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="summarycustomerYearlyReportTable">
                        <thead>
                            <tr>
                                <th>Customer Code</th>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Salesman</th>
                                <th>January</th>
                                <th>February</th>
                                <th>March</th>
                                <th>April</th>
                                <th>May</th>
                                <th>June</th>
                                <th>July</th>
                                <th>August</th>
                                <th>September</th>
                                <th>October</th>
                                <th>November</th>
                                <th>December</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($results as $customer)
                                <tr>
                                    <td>{{ $customer->customer_code }}</td>
                                    <td>{{ $customer->customer_name }}</td>
                                    <td>{{ $customer->location }}</td>
                                    <td>{{ $customer->salesman_name }}</td>
                                    <td>{{ number_format($customer->january ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->february ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->march ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->april ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->may ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->june ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->july ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->august ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->september ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->october ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->november ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->december ?? 0, 2) }}</td>
                                    <td>
                                        <strong>
                                            {{ number_format($customer->total_sales ?? 0, 2) }}
                                        </strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center">
                                        No records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
        $('#summarycustomerYearlyReportTable').DataTable({
            pageLength: 25,
            order: [[1, 'asc']],
            responsive: true,
            drawCallback: function(settings) {
                var api = this.api();
                if (api.rows({ page: 'current' }).count() === 0) {
                    $('.subtotal-row').remove();
                }
            }
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