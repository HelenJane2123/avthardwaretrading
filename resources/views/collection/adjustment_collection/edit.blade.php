@extends('layouts.master')

@section('title', 'Edit Adjustment Collection | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0"><i class="fa fa-exchange"></i> Edit Collection Adjustment</h1>
            <small class="text-muted">Modify the details below to update the collection adjustment</small>
        </div>
         <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Collection</li>
            <li class="breadcrumb-item active">Edit Collection Adjustment</li>
        </ul>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-3">
        <a class="btn btn-outline-primary btn-sm shadow-sm" href="{{ route('adjustment_collection.index') }}">
            <i class="fa fa-list"></i> Manage Collection Adjustment
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <div class="tile-body">
                    <h4 class="tile-title mb-4">Edit Adjustment Collection Entry</h4>

                    <form action="{{ route('adjustment_collection.update', $adjustment->id) }}" method="POST" id="adjustmentForm">
                        @csrf
                        @method('PUT')

                        <!-- Adjustment Number -->
                        <div class="form-group mb-3">
                            <label for="adjustment_no">Adjustment Number</label>
                            <input type="text" name="adjustment_no" id="adjustment_no" 
                                class="form-control form-control-sm" value="{{ $adjustment->adjustment_no }}" readonly>
                        </div>

                        <!-- Search Invoice -->
                        <div class="form-group mb-3">
                            <label for="invoice_no">Invoice Number</label>
                            <input type="text" name="invoice_no" id="invoice_no" 
                                class="form-control form-control-sm" value="{{ $adjustment->invoice_no }}" placeholder="Search or enter Invoice Number" readonly>
                            <small class="form-text text-muted">Start typing to search existing invoices.</small>
                        </div>

                        <!-- Entry Type -->
                        <div class="form-group mb-3">
                            <label>Entry Type</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="entry_type" id="debit" value="Debit" 
                                    {{ $adjustment->entry_type == 'Debit' ? 'checked' : '' }}>
                                <label class="form-check-label" for="debit">Debit</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="entry_type" id="credit" value="Credit"
                                    {{ $adjustment->entry_type == 'Credit' ? 'checked' : '' }}>
                                <label class="form-check-label" for="credit">Credit</label>
                            </div>
                        </div>

                        <!-- Date Adjustment -->
                        <div class="form-group mb-3">
                            <label for="collection_date">Collection Date Adjustment</label>
                            <input type="date" name="collection_date" id="collection_date" 
                                class="form-control form-control-sm" value="{{ $adjustment->collection_date }}" required>
                        </div>

                        <!-- Account Name -->
                        <div class="form-group mb-3">
                            <label for="account_name">Account Name</label>
                            <input type="text" name="account_name" id="account_name" 
                                class="form-control form-control-sm" value="{{ $adjustment->account_name }}" readonly>
                            <small class="text-muted" id="accountHint">Account will auto-fill based on invoice and entry type.</small>
                        </div>

                        <!-- Account Name -->
                        <div class="form-group mb-3">
                            <label for="amount">Amount</label>
                            <input type="text" name="amount" id="amount" 
                                class="form-control form-control-sm" value="{{ $adjustment->amount }}">
                        </div>

                        <!-- Remarks -->
                        <div class="form-group mb-4">
                            <label for="remarks">Remarks</label>
                            <textarea name="remarks" id="remarks" class="form-control form-control-sm" rows="2" placeholder="Enter reason for adjustment (optional)">{{ $adjustment->remarks }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Adjustment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('js')
<script>
   $(document).ready(function() {
        $('#invoice_no').on('input', function() {
            const invoice = $(this).val().trim();
            if (invoice !== '') {
                $('#account_name').val('Invoice ' + invoice + ' (' + $('input[name="entry_type"]:checked').val() + ')');
            } else {
                $('#account_name').val('');
            }
        });

        $('input[name="entry_type"]').on('change', function() {
            const invoice = $('#invoice_no').val().trim();
            if (invoice !== '') {
                $('#account_name').val('Invoice ' + invoice + ' (' + $(this).val() + ')');
            }
        });
    });
</script>
@endpush