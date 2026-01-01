<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DR #{{ $invoice->invoice_number }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        .invoice-box {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            margin-top: 15px;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            line-height: 1.1;
            margin-bottom: 3px;
        }

        .header h4 {
            font-size: 18px;
            margin: 0;
            font-weight: bold;
        }

        .header-contact {
            font-size: 11px;
            margin: 0;
            line-height: 1.1;
        }

        hr {
            margin: 2px 0;
            border: 0;
            border-top: 1px solid #000;
        }

        @page {
            size: A4;
            margin: 15mm 15mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
            }
            .invoice-box {
                border: none;
                padding: 0 10px;
            }
        }

        .table {
            width: 100%;
            margin: 0;
            padding: 0;
            border-collapse: collapse;
            font-size: 14px;
        }

        .table th,
        .table td {
            padding: 4px 5px;  /* less padding to save space */
            vertical-align: middle;
        }

        .table th {
            text-align: center;
        }

        .check-note {
            text-align: left;
            font-weight: 600;
            margin: 10px 0 5px 0;
        }

        .wrapper {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2px;
            margin-top: 5px;
        }

        .item-box {
            border: 2px solid #000;
            border-radius: 6px;
            padding: 4px 6px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 55px;
            font-size: 11px;
        }

        .item1,
        .item2 {
            border-bottom: 1px solid #000;
            padding-bottom: 40px;
            margin-bottom: 5px;
        }

        .signature-line {
            font-weight: bold;
            margin-top: 5px;
        }

        .signature-label {
            font-size: 10px;
            font-style: italic;
            margin-top: -15px;
        }

        .received-box {
            min-height: 60px !important;
            /* width:450px; */
        }

        .text-center {
            text-align: center !important;
        }
    </style>
</head>
<body>
<div class="invoice-box">

    <!-- Header -->
    <div class="header text-center">
        <h4>AVT HARDWARE TRADING</h4>
        <p class="header-contact">
            Wholesale of hardware, electricals, & plumbing supply etc.<br>
            Contact: 0936-8834-275 / 0999-3669-539
        </p>
    </div>
    <hr>

    <!-- Invoice Info -->
    <div class="d-flex justify-content-between">
        <div>
            <h6><strong>DELIVERY RECEIPT</strong></h6>
            <p>
                CUSTOMER: {{ $invoice->customer->name ?? 'N/A' }} <br>
                ADDRESS: {{ $invoice->customer->address ?? 'N/A' }} <br>
                CONTACT NO: {{ $invoice->customer->phone ?? 'N/A' }}
            </p> 
        </div>
        <div class="text-end" style="margin-right:20px;">
            <p>
                DR #: {{ $invoice->invoice_number }} <br>
                DR Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }} <br>
                Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }} <br>
                Terms: {{ $invoice->paymentMode->name ?? 'N/A' }} ({{ $invoice->paymentMode->term ?? 'N/A' }} Days) <br>
                Salesman: {{ optional($invoice->salesman_relation)->salesman_name ?? 'N/A' }}
            </p>
        </div>
    </div>
    <hr>

    <!-- Remarks -->
    @if(!empty($invoice->remarks))
        <p style="margin:0 0 5px 0;">Remarks: {{ $invoice->remarks }}</p>
    @endif

    <!-- Products Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>QTY</th>
                <th>UNIT</th>
                <th>PRODUCT DESCRIPTION</th>
                <th>UNIT PRICE</th>
                <th>DISCOUNT</th>
                <th>AMOUNT</th>
            </tr>
        </thead>
        <tbody>
        @forelse($invoice->sales as $item)
            <tr>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->product->unit->name ?? 'pcs' }}</td>
                <td>{{ $item->product->product_name ?? 'N/A' }}</td>
                <td class="text-center">{{ number_format($item->price, 2) }}</td>
                <td class="text-center" style="font-size:14px;"> 
                    @php
                        $discounts = [];
                        if ($item->discount_1 > 0) $discounts[] = (int)$item->discount_1 . '%';
                        if ($item->discount_2 > 0) $discounts[] = (int)$item->discount_2 . '%';
                        if ($item->discount_3 > 0) $discounts[] = (int)$item->discount_3 . '%';
                    @endphp

                    @if(count($discounts) > 0)
                        {{ ucfirst($item->discount_less_add) }} {{ implode(' ', $discounts) }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ number_format($item->amount, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No products found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <!-- Totals Table -->
    <table class="table" style="margin-top:0;">
        @if($invoice->discount_type === 'overall' && $invoice->discount_value > 0)
        <tr>
            <td class="no-border text-end" style="width:80%;"><strong>Overall Discount:</strong></td>
            <td class="text-center" style="width:20%;">{{ $invoice->discount_value }}%</td>
        </tr>
        @endif
        @if($invoice->shipping_fee > 0)
        <tr>
            <td class="no-border text-end" style="width:80%;"><strong>Shipping Fee:</strong></td>
            <td class="text-center" style="width:20%;">{{ number_format($invoice->shipping_fee, 2) }}</td>
        </tr>
        @endif
        @if($invoice->other_charges > 0)
        <tr>
            <td class="no-border text-end" style="width:80%;"><strong>Other Charges:</strong></td>
            <td class="text-center" style="width:20%;">{{ number_format($invoice->other_charges, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="no-border text-end" style="width:90%;"><strong>Total Amount Due:</strong></td>
            <td class="text-center" style="width:30%;"><strong>{{ number_format($invoice->grand_total, 2) }}</strong></td>
        </tr>
    </table>

    <p class="check-note">PLEASE MAKE ALL CHECKS PAYABLE TO: <b>AVT HARDWARE TRADING</b></p>

    <!-- Signature boxes -->
    <div class="wrapper">
        <div class="item-box">
            <div class="item1">Prepared by:</div>
        </div>
        <div class="item-box">
            <div class="item2">Checked by:</div>
        </div>
        <div class="item-box received-box">
            <div class="item3">
                <p>Received the above articles in good order and conditions.</p>
                <p class="signature-line">By: ____________________________</p>
                <p class="signature-label">(Authorized Signature over Printed Name)</p>
            </div>
        </div>
    </div>
     <div class="warning-text" style="margin-bottom: 20px; font-weight: bold; color: black; font-size:14px; text-transform: uppercase;">
        Strictly no cash advances, any cash or stock given to designated agent will not be deducted from your account.
    </div>

    <br>
    <div class="text-center no-print">
        <button onclick="window.print()" class="btn btn-primary btn-print">
            <i class="fa fa-print"></i> Print DR
        </button>
    </div>

</div>
</body>
</html>
