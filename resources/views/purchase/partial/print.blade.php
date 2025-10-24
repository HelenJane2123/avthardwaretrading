<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 20px;
            color: #000;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-left {
            width: 60%;
        }
        .header-right {
            text-align: right;
            width: 40%;
        }
        h2 {
            margin: 0;
            color: #004080;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background: #004080;
            color: #fff;
            font-size: 12px;
        }
        .no-border td {
            border: none;
            padding: 3px;
        }
        .totals {
            width: 100%;
            float: right;
        }
        .totals td {
            text-align: right;
        }
        .comments {
            border: 1px solid #000;
            padding: 10px;
            margin-top: 30px;
            height: 80px;
        }
        .footer {
            margin-top: 40px;
            font-size: 11px;
            text-align: center;
        }
        .print-btn {
            margin-bottom: 20px;
        }

        /* Hide print button when printing */
        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="print-btn">
    <button onclick="window.print()">Print Purchase Order</button>
</div>

<div class="header" style="border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px;">
    <div style="text-align: left;">
        <h2 style="margin:0; font-size:26px; text-decoration: underline;">
            PURCHASE ORDER
        </h2>
    </div>
    <div style="display: flex; align-items: center; position: relative;">
        <div style="display: flex; align-items: center;">
            <img src="{{ asset('images/avt_logo.png') }}" alt="Company Logo" style="height:60px; margin-right: 12px;">
            <div>
                <p style="margin:0; font-weight:bold;">[Your Company Name]</p>
                <p style="margin:0; font-size:12px; line-height:1.4;">
                    [Street Address]<br>
                    Phone: (000) 000-0000<br>
                    Website: www.company.com
                </p>
            </div>
        </div>
    </div>
</div>

<table width="100%" style="margin-bottom: 20px;">
    <tr>
        <td>
            <strong>Vendor:</strong><br>
            {{ $purchase->supplier->name }}<br>
            {{ $purchase->supplier->address }}<br>
            {{ $purchase->supplier->phone }}
        </td>
        <td>
            <strong>Ship To:</strong><br>
            {{ $company->name ?? 'Company Name' }}<br>
            {{ $company->address ?? 'Company Address' }}<br>
            {{ $company->phone ?? 'Company Phone' }}
        </td>
        <td>
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($purchase->created_at)->format('m/d/Y') }}<br>
            <strong>PO #:</strong> {{ $purchase->po_number }}
        </td>
    </tr>
    <tr>
        <td colspan="3" style="padding-top: 10px;">
            <table width="100%" border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse;">
                <tr>
                    @if($purchase->paymentMode && strtolower($purchase->paymentMode->name) === 'pdc/check')
                        <td>
                            <strong>Payment Terms:</strong> {{ $purchase->paymentMode->term ?? 'N/A' }} days
                        </td>
                    @else
                        <td>
                            <strong>Payment Terms:</strong> -
                        </td>
                    @endif

                    <td>
                        <strong>Payment Method:</strong> {{ $purchase->paymentMode->name ?? '-' }}
                    </td>

                    <td colspan="2">
                        <strong>Salesman:</strong> {{ $purchase->salesman['salesman_name'] ?? '-' }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th style="width: 15%;">ITEM #</th>
            <th style="width: 45%;">DESCRIPTION</th>
            <th style="width: 10%;">QTY</th>
            <th style="width: 15%;">UNIT PRICE</th>
            <th style="width: 15%;">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @php $subtotal = 0; @endphp
        @foreach($purchase->items as $item)
            <tr>
                <td>{{ $item->product_code ?? '-' }}</td>
                <td>
                    {{ $item->supplierItem->item_description ?? 'N/A' }}
                    @if(!empty($item->discount) && $item->discount > 0)
                        <br><small><strong>Discount:</strong> {{ number_format($item->discount, 2) }}%</small>
                    @endif
                </td>
                <td>{{ $item->qty }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table width="100%" style="margin-top: 30px;">
    <tr>
        <td width="60%" valign="top" style="padding-right: 20px; border: none !important;">
            <strong>Comments or Special Instructions:</strong><br>
            <div style="border: 1px solid #000; min-height: 80px; padding: 10px;">
                {{ $purchase->remarks ?? '' }}
            </div>
        </td>
        <td width="40%" valign="top" style="border: none !important;">
            <table class="totals">
                <tr>
                    <td><strong>SUBTOTAL</strong></td>
                    <td>{{ number_format($purchase->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>DISCOUNT</strong></td>
                    <td>{{ $purchase->discount_value }}</td>
                </tr>
                <tr>
                    <td><strong>SHIPPING</strong></td>
                    <td>{{ number_format($purchase->shipping, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>OTHER</strong></td>
                    <td>{{ number_format($purchase->other_charges, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td><strong>{{ number_format($purchase->grand_total, 2) }}</strong></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ✅ PAYMENT SUMMARY --}}
<table width="100%" style="margin-top: 20px; border: 1px solid #000; border-collapse: collapse;">
    <tr style="background-color: #004080; color: #fff;">
        <th colspan="4" style="padding: 8px; text-align: left;">PAYMENT SUMMARY</th>
    </tr>
    <tr>
        <td><strong>Total Amount:</strong></td>
        <td>₱{{ number_format($purchase->grand_total, 2) }}</td>
        <td><strong>Total Paid:</strong></td>
        <td>₱{{ number_format($totalPaid, 2) }}</td>
    </tr>
    <tr>
        <td><strong>Outstanding Balance:</strong></td>
        <td>₱{{ number_format($outstanding, 2) }}</td>
        <td><strong>Status:</strong></td>
        <td style="font-weight:bold; color:
            {{ $paymentStatus === 'Fully Paid' ? 'green' : ($paymentStatus === 'Partial Payment' ? 'orange' : 'red') }}">
            {{ $paymentStatus }}
        </td>
    </tr>
</table>

{{-- ✅ PAYMENT HISTORY --}}
@if($purchase->payments->count() > 0)
    <table width="100%" style="margin-top: 15px; border: 1px solid #000; border-collapse: collapse;">
        <tr style="background-color: #004080; color: #fff;">
            <th colspan="4" style="padding: 8px; text-align: left;">PAYMENT HISTORY</th>
        </tr>
        <tr>
            <th style="width: 20%;">Date</th>
            <th style="width: 30%;">Amount Paid</th>
            <th style="width: 25%;">Outstanding Balance</th>
            <th style="width: 25%;">Payment Status</th>
        </tr>
        @foreach($purchase->payments as $payment)
            <tr>
                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('m/d/Y') }}</td>
                <td>₱{{ number_format($payment->amount_paid, 2) }}</td>
                <td>₱{{ number_format($payment->outstanding_balance, 2) }}</td>
                <td>{{ ucfirst($payment->payment_status) }}</td>
            </tr>
        @endforeach
    </table>
@else
    <p style="margin-top: 15px; font-style: italic;">No payments recorded yet.</p>
@endif

<div style="clear: both;"></div>
<div class="footer">
    If you have any questions about this purchase order, please contact<br>
    [Name, Phone #, E-mail]
</div>

</body>
</html>
