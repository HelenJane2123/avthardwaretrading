@extends('layouts.master')

@section('title', 'Top Selling Products | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-bar-chart"></i> Top Selling Products</h1>
            <p>Monthly breakdown of product sales from highest to lowest.</p>
        </div>
    </div>
    <div class="tile shadow-sm">
        <form method="GET"
              action="{{ route('reports.top_selling_products') }}"
              class="d-flex flex-column align-items-center gap-4 mb-4">

            <div class="d-flex flex-wrap justify-content-center gap-4 w-100">
                <div class="col-md-2">
                    <label>Year</label>
                    <select name="year" class="form-control form-control-sm">
                        @for ($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Month</label>
                    <select name="month" class="form-control form-control-sm">
                        <option value="">All</option>
                        @for ($i=1; $i<=12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0,0,0,$i,1)) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Quarter</label>
                    <select name="quarter" class="form-control form-control-sm">
                        <option value="">All</option>
                        @for ($i=1; $i<=4; $i++)
                            <option value="{{ $i }}" {{ request('quarter') == $i ? 'selected' : '' }}>
                                Q{{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Location</label>
                    <select name="location" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->location }}" {{ request('location') == $loc->location ? 'selected' : '' }}>
                                {{ $loc->location }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Salesman</label>
                    <select name="salesman" id="salesman" class="form-control form-control-sm">
                        <option value="">All Salesmen</option>
                        @foreach($salesman as $s)
                            <option value="{{ $s->id }}"
                                {{ request('salesman') == $s->id ? 'selected' : '' }}>
                                {{ $s->salesman_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('reports.top_selling_products') }}"
                   class="btn btn-secondary">Reset</a>
                <a href="{{ route('reports.top_selling_products_export', request()->all()) }}"
                   class="btn btn-success">Export Excel</a>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped" id="topSellingProductsTable">
                <thead class="table-dark">
                    <tr>
                        <th>Product Code</th>
                        <th>Product</th>
                        <th>Salesman</th>
                        <th>Location</th>
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
                        <th>Total Sales</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($results as $row)
                        <tr>
                            <td>{{ $row->product_code }}</td>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->salesman }}</td>
                            <td>{{ $row->location }}</td>
                            <td>{{ $row->january }}</td>
                            <td>{{ $row->february }}</td>
                            <td>{{ $row->march }}</td>
                            <td>{{ $row->april }}</td>
                            <td>{{ $row->may }}</td>
                            <td>{{ $row->june }}</td>
                            <td>{{ $row->july }}</td>
                            <td>{{ $row->august }}</td>
                            <td>{{ $row->september }}</td>
                            <td>{{ $row->october }}</td>
                            <td>{{ $row->november }}</td>
                            <td>{{ $row->december }}</td>

                            <td><b>{{ $row->total_quantity }}</b></td>
                            <td><b>{{ number_format($row->total_sales,2) }}</b></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="text-center">No data found</td>
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
        $('#topSellingProductsTable').DataTable({
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
