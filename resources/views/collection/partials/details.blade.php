<h5>Invoice Details</h5>
<table class="table table-sm table-bordered">
    <tr>
        <th>Invoice #</th>
        <td>{{ $collection->invoice->invoice_number }}</td>
    </tr>
    <tr>
        <th>Invoice Date</th>
        <td>{{ \Carbon\Carbon::parse($collection->invoice->invoice_date)->format('M d, Y') }}</td>
    </tr>
    <tr>
        <th>Due Date</th>
        <td>{{ \Carbon\Carbon::parse($collection->invoice->due_date)->format('M d, Y') }}</td>
    </tr>
    <tr>
        <th>Invoice Amount</th>
        <td>{{ number_format($collection->invoice->grand_total, 2) }}</td>
    </tr>
</table>

<h5>Customer Details</h5>
<table class="table table-sm table-bordered">
    <tr>
        <th>Name</th>
        <td>{{ $collection->invoice->customer->name }}</td>
    </tr>
    <tr>
        <th>Email</th>
        <td>{{ $collection->invoice->customer->email ?? '-' }}</td>
    </tr>
    <tr>
        <th>Phone</th>
        <td>{{ $collection->invoice->customer->mobile ?? '-' }}</td>
    </tr>
</table>

<h5>Collection Details</h5>
<table class="table table-sm table-bordered">
    <tr>
        <th>Payment Date</th>
        <td>{{ \Carbon\Carbon::parse($collection->payment_date)->format('M d, Y') }}</td>
    </tr>
    <tr>
        <th>Amount Paid</th>
        <td>{{ number_format($collection->amount_paid, 2) }}</td>
    </tr>
     <tr>
        <th>Outstanding Balance</th>
        <td>{{ number_format($collection->invoice->outstanding_balance, 2) }}</td>
    </tr>
    <tr>
        <th>Payment Status</th>
        <td>{{ ucfirst($collection->invoice->payment_status) }}</td>
    </tr>
    <tr>
        <th>Payment Method</th>
        <td>{{ $collection->invoice->paymentMode->name ?? '-' }}</td>
    </tr>
</table>