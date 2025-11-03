<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        body { font-size: 14px; }
        .invoice-box { max-width: 900px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .table th, .table td { vertical-align: middle; }
        .no-border { border: none !important; }
        .text-right { text-align: right; }
        .btn-print { margin: 20px 0; }
        .logo { max-height: 100px; }
        @media print {
            .no-print {
                display: none !important;
            }
        }
        @page {
            margin: 20mm; /* adjust as needed */
        }
    </style>
</head>
<body>
<div class="invoice-box">

    <!-- Header with Logo -->
    <div class="d-flex justify-content-between mb-4 align-items-center">
        <div class="text-end">
            <h4><strong>AVT HARDWARE</strong></h4>
            <p>
                Test Address <br>
                VAT Reg. TIN: 255-670-536-000 <br>
                Contact: (053) 123-4567
            </p>
        </div>
        <div>
            <img src="{{ asset('images/avt_logo.png') }}" alt="Company Logo" class="logo">
        </div>
    </div>

    <hr>

    <!-- Invoice Info -->
    <div class="d-flex justify-content-between mb-3">
        <div>
            <h5><strong>CUSTOMER DETAILS</strong></h5>
            <p>
                <strong>Sold to:</strong> {{ $invoice->customer->name ?? 'N/A' }} <br>
                <strong>Address:</strong> {{ $invoice->customer->address ?? 'N/A' }} <br>
                <strong>Contact:</strong> {{ $invoice->customer->phone ?? 'N/A' }}
            </p>
        </div>
        <div class="text-end">
            <h5><strong>SALES INVOICE DETAILS</strong></h5>
            <p>
                <strong>Invoice #:</strong> {{ $invoice->invoice_number }} <br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }} <br>
                <strong>Due:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }} <br>
                <strong>Payment Method:</strong> {{ $invoice->paymentMode->name ?? 'N/A' }}
            </p>
        </div>
    </div>
  <!-- Remarks -->
    @if(!empty($invoice->remarks))
    <p><strong>Remarks:</strong> {{ $invoice->remarks }}</p>
    @endif

    <!-- Products Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>QTY</th>
                <th>UNIT</th>
                <th>PRODUCT</th>
                <th>DISCOUNT</th>
                <th>UNIT PRICE</th>
                <th>AMOUNT</th>
            </tr>
        </thead>
        <tbody>
        @forelse($invoice->sales as $item)
            <tr>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->product->unit->name ?? 'pcs' }}</td>
                <td>{{ $item->product->product_code . ' - ' . $item->product->product_name ?? 'N/A' }}</td>
                <td>
                    @if($item->discounts->count() > 0)
                        @foreach($item->discounts as $discount)
                            <span class="badge bg-info text-dark">{{ $discount->discount_value }} %</span>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td class="text-end">{{ number_format($item->price, 2) }}</td>
                <td class="text-end">{{ number_format($item->amount, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No products found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <!-- Totals -->
    <table class="table">
        <tr>
            <td class="no-border text-end"><strong>Subtotal:</strong></td>
            <td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->discount_type === 'overall' && $invoice->discount_value > 0)
        <tr>
            <td class="no-border text-end"><strong>Overall Discount:</strong></td>
            <td class="text-end">{{ $invoice->discount_value }}%</td>
        </tr>
        @endif
        @if($invoice->shipping_fee > 0)
        <tr>
            <td class="no-border text-end"><strong>Shipping Fee:</strong></td>
            <td class="text-end">{{ number_format($invoice->shipping_fee, 2) }}</td>
        </tr>
        @endif
        @if($invoice->other_charges > 0)
        <tr>
            <td class="no-border text-end"><strong>Other Charges:</strong></td>
            <td class="text-end">{{ number_format($invoice->other_charges, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="no-border text-end"><strong>Grand Total:</strong></td>
            <td class="text-end"><strong>{{ number_format($invoice->grand_total, 2) }}</strong></td>
        </tr>
    </table>

    <!-- Print Button -->
    <div class="text-center no-print">
        <button onclick="window.print()" class="btn btn-primary btn-print">
            <i class="fa fa-print"></i> Print Invoice
        </button>
    </div>

</div>
</body>
</html>
