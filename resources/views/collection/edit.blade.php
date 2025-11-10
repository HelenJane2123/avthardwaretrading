@extends('layouts.master')

@section('title', 'Edit Collection | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-money"></i> Edit Collection</h1>
            <p class="text-muted mb-0">Update collection details for the selected invoice.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Collection</li>
            <li class="breadcrumb-item active">Edit Collection</li>
        </ul>
    </div>

    <div class="mb-3">
        <a class="btn btn-outline-primary" href="{{ route('collection.index') }}">
            <i class="fa fa-list"></i> Manage Collections
        </a>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <h3 class="tile-title mb-4"><i class="fa fa-money"></i> Edit Collection</h3>
                <div class="container">
                    <form action="{{ route('collection.update', $collection->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Invoice Details --}}
                        <div class="mb-3">
                            <h5>Invoice Details</h5>
                            <table class="table table-bordered table-sm">
                                <tbody>
                                    <tr>
                                        <th width="30%">Invoice #</th>
                                        <td>{{ $collection->invoice->invoice_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Total Amount</th>
                                        <td>₱{{ number_format($collection->invoice->grand_total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Balance</th>
                                        <td>₱<span id="detailBalance">{{ number_format($collection->invoice->outstanding_balance, 2) }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Payment Mode</th>
                                        <td>{{ $collection->invoice->paymentMode->name ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Customer Details --}}
                        <div class="mb-3">
                            <h5>Customer Details</h5>
                            <table class="table table-bordered table-sm">
                                <tbody>
                                    <tr>
                                        <th width="30%">Name</th>
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
                                </tbody>
                            </table>
                        </div>

                        {{-- Editable Fields --}}
                        <div class="mb-3">
                            <label>Collection Number</label>
                            <input type="text" name="collection_number" class="form-control" 
                                   value="{{ $collection->collection_number }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Last Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" 
                                   value="{{ \Carbon\Carbon::parse($collection->updated_at)->format('Y-m-d') }}" required disabled>
                        </div>

                        <div class="mb-3">
                            <label>Last Amount Paid</label>
                            <input type="number" step="0.01" name="last_paid_amount" class="form-control" 
                                   value="{{ $collection->last_paid_amount }}" required disabled>
                        </div>

                        <div class="mb-3">
                            <label>Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" 
                                   value="{{ \Carbon\Carbon::parse($collection->payment_date)->format('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label>Amount Paid</label>
                            <input type="number" step="0.01" name="amount_paid" class="form-control" 
                                   required>
                        </div>
                        <div id="pdcCheck" class="mb-3" style="display: none;">
                            <label>Check Date</label>
                            <input type="date" name="payment_date" class="form-control" 
                                   value="{{ \Carbon\Carbon::parse($collection->check_date)->format('Y-m-d') }}" required>
                        </div>
                        <div id="pdcFields" class="mb-3" style="display: none;">
                            <label>Check Number</label>
                            <input type="text" name="check_number" class="form-control"
                                value="{{ old('check_number', $collection->check_number ?? '') }}"
                                placeholder="Enter check number">
                        </div>

                        <div id="gcashFields" style="display: none;">
                            <div class="mb-3">
                                <label>GCash Name</label>
                                <input type="text" name="gcash_name" class="form-control"
                                    value="{{ old('gcash_name', $collection->gcash_name ?? '') }}"
                                    placeholder="Enter GCash account name">
                            </div>
                            <div class="mb-3">
                                <label>GCash Mobile Number</label>
                                <input type="text" name="gcash_number" class="form-control"
                                    value="{{ old('gcash_number', $collection->gcash_number ?? '') }}"
                                    placeholder="Enter GCash mobile number">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Balance</label>
                            <input type="number" step="0.01" name="balance" 
                                   class="form-control" 
                                   value="{{ $collection->invoice->outstanding_balance }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-control" required>
                                <option value="pending" {{ $collection->invoice->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="partial" {{ $collection->invoice->payment_status == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ $collection->invoice->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ $collection->invoice->payment_status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                <option value="approved" {{ $collection->invoice->payment_status == 'approved' ? 'selected' : '' }}>Approved</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2">{{ $collection->remarks }}</textarea>
                        </div>

                        <button class="btn btn-success"><i class="fa fa-save"></i> Update Collection</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
@push('js')
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paymentMode = "{{ strtolower($collection->invoice->paymentMode->name ?? '') }}";

    // Hide all first
    document.getElementById('pdcFields').style.display = 'none';
    document.getElementById('pdcCheck').style.display = 'none';
    document.getElementById('gcashFields').style.display = 'none';

    // Show based on payment method
    if (paymentMode === 'pdc/check') {
        document.getElementById('pdcCheck').style.display = 'block';
        document.getElementById('pdcFields').style.display = 'block';
    } else if (paymentMode === 'gcash') {
        document.getElementById('gcashFields').style.display = 'block';
    }
});

$(document).on("submit", "form", function (e) {
    // Only check if this is the collection/payment form
    const $form = $(this);

    // Clean and parse numeric values (remove ₱, commas, etc.)
    const balance = parseFloat(($form.find("input[name='balance']").val() || "0").replace(/[^0-9.-]/g, ""));
    const amountPaid = parseFloat(($form.find("input[name='amount_paid']").val() || "0").replace(/[^0-9.-]/g, ""));
    const paymentStatus = $form.find("select[name='payment_status']");

    // Debugging helper (optional)
    console.log("Balance:", balance, "Amount Paid:", amountPaid);

    // Prevent overpayment
    if (amountPaid > balance) {
        e.preventDefault();
        Swal.fire({
            icon: "error",
            title: "Invalid Payment",
            text: `The amount paid (₱${amountPaid.toFixed(2)}) cannot exceed the balance (₱${balance.toFixed(2)}).`,
            confirmButtonColor: "#d33",
        });
        return false;
    }

    // Auto-update payment status
    if (amountPaid < balance) {
        paymentStatus.val("partial");
    } else if (amountPaid === balance) {
        paymentStatus.val("paid");
    }
});
</script>
@endpush
