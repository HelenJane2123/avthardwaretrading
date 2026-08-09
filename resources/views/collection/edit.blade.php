@extends('layouts.master')

@section('title', 'Edit Collection | ')

@section('content')

@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-money"></i> Edit Collection</h1>
            <p class="text-muted mb-0">Update customer payment information.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Collection</li>
            <li class="breadcrumb-item active">Edit</li>
        </ul>
    </div>

    <div class="mb-3">
        <a href="{{ route('collection.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-list"></i> Manage Collections
        </a>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success">{{ session()->get('message') }}</div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <h3 class="tile-title"><i class="fa fa-edit"></i> Update Collection</h3>
                <form method="POST" action="{{ route('collection.update', $collection->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong><i class="fa fa-file-text"></i> Invoice Details</strong>
                                </div>
                                <div class="card-body p-2">
                                    <table class="table table-bordered table-sm mb-0">
                                        <tr>
                                            <th width="40%">Invoice #</th>
                                            <td>{{ $collection->invoice->invoice_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Amount</th>
                                            <td>₱{{ number_format($collection->invoice->grand_total, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Current Balance</th>
                                            <td class="text-danger">₱{{ number_format($collection->invoice->outstanding_balance, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Mode</th>
                                            <td>{{ $collection->invoice->paymentMode->name ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong><i class="fa fa-user"></i> Customer Details</strong>
                                </div>
                                <div class="card-body p-2">
                                    <table class="table table-bordered table-sm mb-0">
                                        <tr>
                                            <th width="35%">Name</th>
                                            <td>{{ $collection->invoice->customer->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $collection->invoice->customer->email ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone</th>
                                            <td>{{ $collection->invoice->customer->mobile ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Address</th>
                                            <td>{{ $collection->invoice->customer->address ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong><i class="fa fa-credit-card"></i> Payment Information</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Collection Number</label>
                                        <input type="text"
                                            name="collection_number"
                                            class="form-control form-control-sm"
                                            value="{{ $collection->collection_number }}"
                                            readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Date</label>
                                        <input type="date"
                                            name="payment_date"
                                            class="form-control form-control-sm"
                                            value="{{ 
                                        \Carbon\Carbon::parse($collection->payment_date)->format('Y-m-d') }}"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Amount Paid</label>
                                        <input type="number"
                                            step="0.01"
                                            name="amount_paid"
                                            id="amountPaid"
                                            class="form-control form-control-sm"
                                            value="{{ $collection->amount_paid }}"
                                            required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Remaining Balance</label>
                                        <input type="number"
                                            step="0.01"
                                            id="balance"
                                            name="balance"
                                            class="form-control form-control-sm"
                                            value="{{ $collection->invoice->outstanding_balance }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                            <div id="pdcSection" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Bank Name</label>
                                            <input type="text"
                                                name="bank_name"
                                                class="form-control form-control-sm"
                                                value="{{ $collection->bank_name ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Check Date</label>
                                            <input type="date"
                                                name="check_date"
                                                class="form-control form-control-sm"
                                                value="{{ $collection->check_date ? \Carbon\Carbon::parse($collection->check_date)->format('Y-m-d') : '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Check Number</label>
                                            <input type="text"
                                                name="check_number"
                                                class="form-control form-control-sm"
                                                value="{{ $collection->check_number ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Check Amount</label>
                                            <input type="number"
                                                step="0.01"
                                                name="check_amount"
                                                class="form-control form-control-sm"
                                                value="{{ $collection->check_amount ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="gcashSection" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>GCash Name</label>
                                            <input type="text"
                                                name="gcash_name"
                                                class="form-control form-control-sm"
                                                value="{{ $collection->gcash_name ?? '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>GCash Mobile Number</label>
                                            <input type="text"
                                                name="gcash_number"
                                                class="form-control form-control-sm"
                                                value="{{ $collection->gcash_number ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Payment Status</label>
                                <select name="payment_status" class="form-control form-control-sm" required>
                                    <option value="pending" {{ $collection->invoice->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="partial" {{ $collection->invoice->payment_status == 'partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="paid" {{ $collection->invoice->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="overdue" {{ $collection->invoice->payment_status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    <option value="approved" {{ $collection->invoice->payment_status == 'approved' ? 'selected' : '' }}>Approved</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea name="remarks" rows="3" class="form-control form-control-sm">{{ $collection->remarks }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3" id="paymentHistorySection">
                        <div class="card-header bg-light">
                            <strong><i class="fa fa-history"></i> Payment History</strong>
                        </div>
                        <div class="card-body p-2">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Bank</th>
                                            <th>Check #</th>
                                        </tr>
                                    </thead>
                                    <tbody id="paymentHistoryBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa fa-save"></i> Update Collection
                    </button>
                    <a href="{{ route('collection.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

@push('js')
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<script>
$(document).ready(function() {
    let paymentMode = "{{ strtolower($collection->invoice->paymentMode->name ?? '') }}";
    if (paymentMode === 'pdc/check') {
        $('#pdcSection').show();
    } else if (paymentMode === 'gcash') {
        $('#gcashSection').show();
    }

    let invoiceId = "{{ $collection->invoice_id }}";
    $.ajax({
        url: `/invoice/${invoiceId}/collections`,
        type: 'GET',
        success: function(data) {
            let tbody = $('#paymentHistoryBody');
            tbody.empty();
            if (!Array.isArray(data) || data.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="4" class="text-center">No previous payments found.</td>
                    </tr>
                `);
                return;
            }
            $.each(data, function(index, row) {
                tbody.append(`
                    <tr>
                        <td>${formatDate(row.payment_date)}</td>
                        <td>${formatCurrency(row.amount_paid)}</td>
                        <td>${row.bank_name ?? ''}</td>
                        <td>${row.check_number ?? ''}</td>
                    </tr>
                `);
            });
        },
        error: function() {
            console.log('Unable to load payment history');
        }
    });

    $('form').submit(function(e) {
        let currentBalance = parseFloat($('#balance').val()) || 0;
        let oldPayment = parseFloat("{{ $collection->amount_paid }}") || 0;
        let newPayment = parseFloat($('#amountPaid').val()) || 0;
        let availableBalance = currentBalance + oldPayment;

        if (newPayment > availableBalance) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Payment',
                text: `Amount paid cannot exceed remaining balance ₱${availableBalance.toFixed(2)}`,
                confirmButtonColor: '#d33'
            });
            return false;
        }

        let status = $('select[name="payment_status"]');
        let remaining = availableBalance - newPayment;
        if (remaining <= 0) {
            status.val('paid');
        } else if (newPayment > 0) {
            status.val('partial');
        }
    });
});
</script>
@endpush
