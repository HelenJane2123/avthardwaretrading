@extends('layouts.master')

@section('title', 'PDC Collection | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-money"></i> Add PDC Collection</h1>
            <p class="text-muted mb-0">Create a new pdc collection for customer's invoice.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">PDC Collection</li>
            <li class="breadcrumb-item active">Add PDC Collection</li>
        </ul>
    </div>

    <div class="mb-3">
        <a class="btn btn-outline-primary" href="{{ route('pdc.index') }}">
            <i class="fa fa-list"></i> Manage PDC Collections
        </a>
    </div>
    {{-- Success Message --}}
    @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <h3 class="tile-title mb-4"><i class="fa fa-money"></i> PDC Collection </h3>
                <div class="container">
                    <form action="{{ route('pdc.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer Name</label>
                                <input type="text" name="client_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Check Number</label>
                                <input type="text" name="check_number" class="form-control" required>
                            </div>

                            <!-- BANK -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bank</label>
                                <input type="text" name="bank" class="form-control" required>
                            </div>

                            <!-- AMOUNT -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Check Amount</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>

                            <!-- DUE DATE -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Due Date (PDC Date)</label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>

                            <!-- TERM -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Terms</label>
                                <select name="term_days" class="form-select">
                                    <option value="30">30 Days</option>
                                    <option value="60">60 Days</option>
                                    <option value="90" selected>90 Days</option>
                                    <option value="120">120 Days</option>
                                </select>
                            </div>

                            <!-- REMARKS -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3"></textarea>
                            </div>

                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save PDC
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select Invoice</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table id="invoiceTable" class="table table-bordered table-striped table-hover w-100">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Balance</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script src="{{ asset('/') }}js/plugins/jquery.dataTables.min.js"></script>
<script src="{{ asset('/') }}js/plugins/dataTables.bootstrap.min.js"></script>
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<script>
    $(document).ready(function () {
        
    });
</script>
@endpush