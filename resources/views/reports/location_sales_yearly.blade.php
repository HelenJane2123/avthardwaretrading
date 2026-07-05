@extends('layouts.master')

@section('title', 'Yearly Sales Report by Location | ')

@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">

    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1>
                <i class="fa fa-map-marker-alt"></i>
                Yearly Sales Report by Location
            </h1>
            <p class="text-muted mb-0">
                View total sales per location broken down by month for a selected year.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile">

                {{-- FILTER FORM --}}
                <form method="GET"
                    action="{{ route('reports.location_sales_yearly') }}"
                    class="d-flex flex-column align-items-center gap-4 mb-4">

                    <div class="d-flex flex-wrap justify-content-center gap-4 w-100">

                        {{-- YEAR --}}
                        <div class="d-flex flex-column" style="min-width:140px;">
                            <label>Year</label>
                            <select name="year" class="form-control form-control-sm">
                                @for ($i = date('Y'); $i >= 2020; $i--)
                                    <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- MONTH --}}
                        <div class="d-flex flex-column" style="min-width:160px;">
                            <label>Month</label>
                            <select name="month" class="form-control form-control-sm">
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
                            <label>Quarter</label>
                            <select name="quarter" class="form-control form-control-sm">
                                <option value="">All Quarters</option>
                                @for ($q=1; $q<=4; $q++)
                                    <option value="{{ $q }}" {{ request('quarter') == $q ? 'selected' : '' }}>
                                        Q{{ $q }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        {{-- LOCATION --}}
                        <div class="d-flex flex-column" style="min-width:220px;">
                            <label>Location</label>
                            <select name="location" id="locationSelect" class="form-control form-control-sm">
                                <option value="">All Locations</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->location }}" {{ request('location') == $loc->location ? 'selected' : '' }}>
                                        {{ $loc->location }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- SALESMAN --}}
                        <div class="d-flex flex-column" style="min-width:220px;">
                            <label>Salesman</label>
                            <select name="salesman" id="salesmanSelect" class="form-control form-control-sm">
                                <option value="">All Salesmen</option>
                                @foreach($salesman as $s)
                                    <option value="{{ $s->id }}" {{ request('salesman') == $s->id ? 'selected' : '' }}>
                                        {{ $s->salesman_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- BUTTONS --}}
                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-primary">Filter</button>

                        <a href="{{ route('reports.location_sales_yearly') }}"
                           class="btn btn-secondary">Reset</a>

                        <a href="{{ route('reports.location_sales_yearly_export', request()->all()) }}"
                           class="btn btn-success">Export Excel</a>
                    </div>

                </form>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="locationYearlyTable">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Jan</th><th>Feb</th><th>Mar</th>
                                <th>Apr</th><th>May</th><th>Jun</th>
                                <th>Jul</th><th>Aug</th><th>Sep</th>
                                <th>Oct</th><th>Nov</th><th>Dec</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($results as $r)
                                <tr>
                                    <td>{{ $r->location }}</td>
                                    <td>{{ number_format($r->january ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->february ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->march ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->april ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->may ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->june ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->july ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->august ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->september ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->october ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->november ?? 0, 2) }}</td>
                                    <td>{{ number_format($r->december ?? 0, 2) }}</td>
                                    <td><strong>{{ number_format($r->total_sales ?? 0, 2) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center">No records found.</td>
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
        $('#locationYearlyTable').DataTable({
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