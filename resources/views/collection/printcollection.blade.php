<!DOCTYPE html>
<html>
<head>
    <title>Collection Receipt</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h2 {
            margin: 0;
            color: #004085;
        }
        .header p {
            margin: 2px 0;
            font-size: 14px;
            color: #555;
        }
        .details, .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details td {
            padding: 8px 5px;
            vertical-align: top;
        }
        .details strong {
            color: #004085;
        }
        .items th, .items td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .items th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            font-size: 16px;
            margin-top: 20px;
        }
        .print-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .print-btn:hover {
            background-color: #218838;
        }
        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:void(0)" class="print-btn" onclick="window.print()">Print Receipt</a>

        <div class="header">
            <h2>My Company Name</h2>
            <p>Company ID: 123456789</p>
            <p>Address: 123 Business St., City, Country</p>
            <hr style="margin: 10px 0; border: 0; border-top: 1px solid #004085;">
            <h3>Collection Receipt</h3>
            <p>Receipt #: {{ $collection->id }}</p>
            <p>Date: {{ \Carbon\Carbon::parse($collection->payment_date)->format('M d, Y') }}</p>
        </div>

        <table class="details">
            <tr>
                <td><strong>Customer:</strong> {{ $collection->invoice->customer->name }}</td>
                <td><strong>Invoice #:</strong> {{ $collection->invoice->invoice_number }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong> {{ $collection->invoice->customer->email ?? '-' }}</td>
                <td><strong>Payment Method:</strong> {{ $collection->invoice->paymentMode->name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Mobile:</strong> {{ $collection->invoice->customer->mobile ?? '-' }}</td>
                <td><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($collection->invoice->invoice_date)->format('M d, Y') }}</td>
            </tr>
        </table>

        <table class="items">
            <tr>
                <th>Description</th>
                <th>Amount Paid</th>
            </tr>
            <tr>
                <td>Payment for Invoice #{{ $collection->invoice->invoice_number }}</td>
                <td>{{ number_format($collection->amount_paid, 2) }}</td>
            </tr>
        </table>

        <div class="total">
            <p><strong>Outstanding Balance:</strong> {{ number_format($collection->invoice->outstanding_balance, 2) }}</p>
            <p><strong>Payment Status:</strong> {{ ucfirst($collection->invoice->payment_status) }}</p>
        </div>

        <p>Thank you for your payment!</p>
    </div>
</body>
</html>
