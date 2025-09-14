@extends('layouts.master')

@section('title', 'Collection | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-money"></i> Add Collection</h1>
            <p class="text-muted mb-0">Create a new collection for customer's invoice.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Collection</li>
            <li class="breadcrumb-item active">Add Collection</li>
        </ul>
    </div>

    <div class="mb-3">
        <a class="btn btn-outline-primary" href="{{ route('collection.index') }}">
            <i class="fa fa-list"></i> Manage Collections
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
                <h3 class="tile-title mb-4"><i class="fa fa-money"></i> Collection </h3>
                <div class="container">
                    @if($invoices->isEmpty())
                        <div class="alert alert-warning">
                            No invoices found. Please <a href="{{ route('invoice.create') }}">create an invoice</a> first before recording a collection.
                        </div>
                    @else
                        <form action="{{ route('collection.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label>Select Invoice</label>
                                <select name="invoice_id" id="invoiceSelect" class="form-control" required>
                                    <option value="">-- Select invoice --</option>
                                    @foreach($invoices as $inv)
                                        <option value="{{ $inv->id }}">
                                            {{ $inv->invoice_number }} — {{ $inv->customer->name }} — ₱{{ number_format($inv->grand_total, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="customer_id" id="customerId">

                            <div id="invoiceDetails" class="mb-3" style="display:none;">
                                <h5>Invoice Details</h5>
                                <p><strong>Invoice #:</strong>
                                    <a id="detailInvoiceLink" href="#" target="_blank">
                                        <span id="detailInvoiceNumber"></span>
                                    </a>
                                </p>
                                <p><strong>Total Amount:</strong> ₱<span id="detailGrandTotal"></span></p>
                                <p><strong>Balance:</strong> ₱<span id="detailBalance"></span></p>

                                <h5>Customer Details</h5>
                                <p><strong>Name:</strong> <span id="detailCustomerName"></span></p>
                                <p><strong>Email:</strong> <span id="detailCustomerEmail"></span></p>
                                <p><strong>Phone:</strong> <span id="detailCustomerPhone"></span></p>
                                <p><strong>Address:</strong> <span id="detailCustomerAddress"></span></p>
                            </div>

                            <div class="mb-3">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label>Amount Paid</label>
                                <input type="number" step="0.01" name="amount_paid" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Balance</label>
                                <input type="number" step="0.01" name="balance" id="balanceField" class="form-control" readonly>
                            </div>

                            <div class="mb-3">
                                <label>Payment Status</label>
                                <select name="payment_status" class="form-control" required>
                                    <option value="pending">Pending</option>
                                    <option value="partial">Partial</option>
                                    <option value="paid">Paid</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="approved">Approved</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Optional remarks"></textarea>
                            </div>

                            <button class="btn btn-success"><i class="fa fa-save"></i> Save Collection</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script>
    $(document).ready(function(){
        document.getElementById('invoiceSelect').addEventListener('change', function () {
            let invoiceId = this.value;

            if (invoiceId) {
                fetch(`/invoices/${invoiceId}/details`)
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        document.getElementById('invoiceDetails').style.display = 'block';

                        // Fill Invoice Info
                        document.getElementById('detailInvoiceNumber').textContent = data.invoice_number;
                        document.getElementById('detailGrandTotal').textContent = parseFloat(data.grand_total).toFixed(2);
                        document.getElementById('detailBalance').textContent = parseFloat(data.balance).toFixed(2);

                        // Fill Customer Info
                        document.getElementById('detailCustomerName').textContent = data.customer.name;
                        document.getElementById('detailCustomerEmail').textContent = data.customer.email ?? 'N/A';
                        document.getElementById('detailCustomerPhone').textContent = data.customer.phone ?? 'N/A';
                        document.getElementById('detailCustomerAddress').textContent = data.customer.address ?? 'N/A';

                        // Hidden input for customer_id
                        document.getElementById('customerId').value = data.customer.id;

                        // Invoice link
                        let link = document.getElementById('detailInvoiceLink');
                        link.href = `/invoice/${invoiceId}`;
                    });
            } else {
                document.getElementById('invoiceDetails').style.display = 'none';
            }
        });
    });
</script>
@endsection
