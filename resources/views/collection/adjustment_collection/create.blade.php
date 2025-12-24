@extends('layouts.master')

@section('title', 'Adjustment Collection | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fa fa-exchange"></i> Add Collection Adjustment</h1>
            <small class="text-muted">Fill in the details below to create collection adjustment</small>
        </div>
         <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Collection</li>
            <li class="breadcrumb-item active">Add Collection Adjustment</li>
        </ul>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <a class="btn btn-sm btn-outline-primary shadow-sm" href="{{ route('adjustment_collection.index') }}">
            <i class="fa fa-list"></i> Manage Collection Adjustment
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <div class="tile-body">
                    <h4 class="tile-title mb-4">Adjustment Collection Entry</h4>
                    <form action="{{ route('adjustment_collection.store') }}" method="POST" id="adjustmentForm">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="adjustment_no">Adjustment Number</label>
                            <input type="text" name="adjustment_no" id="adjustment_no" class="form-control form-control-sm" 
                                value="{{ $nextAdjustmentNumber }}" readonly>
                        </div>
                        <!-- Search Invoice -->
                        <div class="form-group mb-3">
                            <label for="invoice_no">Invoice Number</label>
                            <input type="text" name="invoice_no" id="invoice_no" class="form-control form-control-sm" placeholder="Search or enter Invoice Number">
                            <small class="form-text text-muted">Start typing to search existing invoices.</small>
                        </div>

                        <!-- Entry Type -->
                        <div class="form-group mb-3">
                            <label>Entry Type</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="entry_type" id="debit" value="Debit" checked>
                                <label class="form-check-label" for="debit">Debit</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="entry_type" id="credit" value="Credit">
                                <label class="form-check-label" for="credit">Credit</label>
                            </div>
                        </div>

                        <!-- Date Adjustment -->
                        <div class="form-group mb-3">
                            <label for="collection_date">Collection Date Adjustment</label>
                            <input type="date" name="collection_date" id="collection_date" class="form-control form-control-sm" required>
                        </div>

                        <!-- Account Name -->
                        <div class="form-group mb-3">
                            <label for="account_name">Account Name</label>
                            <input type="text" name="account_name" id="account_name" class="form-control form-control-sm" readonly>
                            <small class="text-muted" id="accountHint">Account will auto-fill based on invoice and entry type.</small>
                        </div>

                        <!-- Adjustment Amount -->
                        <div class="form-group mb-3">
                            <label for="amount">Adjustment Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control form-control-sm" min="0"
                                step="0.01" placeholder="Enter adjustment amount" required>
                        </div>

                        <!-- Remarks -->
                        <div class="form-group mb-4">
                            <label for="remarks">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control form-control-sm" rows="2" placeholder="Enter reason for adjustment (optional)"></textarea>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa fa-save"></i> Save Adjustment
                        </button>
                    </form>
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
<script>
   $(document).ready(function() {
        // Autocomplete invoice number (dummy example)
        $('#invoice_no').on('input', function() {
            const invoice = $(this).val().trim();
            if (invoice !== '') {
                // You can replace this with AJAX call to search invoice
                $('#account_name').val('Invoice ' + invoice + ' (' + $('input[name="entry_type"]:checked').val() + ')');
            } else {
                $('#account_name').val('');
            }
        });

        // Update account name dynamically when switching debit/credit
        $('input[name="entry_type"]').on('change', function() {
            const invoice = $('#invoice_no').val().trim();
            if (invoice !== '') {
                $('#account_name').val('Invoice ' + invoice + ' (' + $(this).val() + ')');
            }
        });
    });
</script>
@endpush