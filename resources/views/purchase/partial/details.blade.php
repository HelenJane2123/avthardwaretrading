<div class="details" style="margin-bottom: 20px;">

    <!-- Purchase & Supplier Info Side by Side -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">

        <!-- ✅ Purchase Info (Left) -->
        <div style="width: 48%;">
            <h4 style="margin-bottom: 8px; border-bottom: 1px solid #000;">Purchase Info</h4>
            <p><strong>PO Number:</strong> {{ $purchase->po_number }}</p>
            <p><strong>Salesman:</strong> {{ $purchase->salesman }}</p>
            <p><strong>Date Purchased:</strong> {{ \Carbon\Carbon::parse($purchase->date)->format('F d, Y') }}</p>
        </div>

        <!-- ✅ Supplier Info (Right) -->
        <div style="width: 48%;">
            <h4 style="margin-bottom: 8px; border-bottom: 1px solid #000;">Supplier Info</h4>
            <p><strong>Name:</strong> {{ $purchase->supplier->name }}</p>
            <p><strong>Email:</strong> {{ $purchase->supplier->email ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $purchase->supplier->phone ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $purchase->supplier->address ?? 'N/A' }}</p>
        </div>

    </div>
</div>

<hr>

<!-- ✅ Purchase Items in Table -->
<h4 style="margin-top: 20px; margin-bottom: 10px;">Purchase Items</h4>
<table width="100%" border="1" cellspacing="0" cellpadding="6" style="border-collapse: collapse; font-size: 13px;">
    <thead style="background: #f2f2f2;">
        <tr>
            <th style="text-align: left;">Product Code</th>
            <th style="text-align: left;">Description</th>
            <th style="text-align: center;">Quantity</th>
            <th style="text-align: center;">Discount</th>
            <th style="text-align: right;">Price</th>
            <th style="text-align: right;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchase->items as $item)
            <tr>
                <td>{{ $item->product_code }}</td>
                <td>{{ $item->supplierItem->item_description ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ $item->qty }}</td>
                <td style="text-align: center;">{{ $item->discount }}%</td>
                <td style="text-align: right;">₱{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right;">₱{{ number_format($item->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<table width="100%" style="margin-top: 30px;">
    <tr>
        <!-- Comments / Special Instructions -->
        <td width="60%" valign="top" style="padding-right: 20px; border: none !important;">
            <strong>Comments or Special Instructions:</strong><br>
            <div style="border: 1px solid #000; min-height: 80px; padding: 10px;">
                {{ $purchase->remarks ?? '' }}
            </div>
        </td>
        <td width="80%" valign="top" style="border: none !important;float:right;">
            <table class="totals">
                <tr>
                    <td><strong>SUBTOTAL: </strong></td>
                    <td>{{ number_format($purchase->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>TAX/DISCOUNT: </strong></td>
                    <td>{{$purchase->discount_value }}</td>
                </tr>
                <tr>
                    <td><strong>SHIPPING: </strong></td>
                    <td>{{ number_format($purchase->shipping, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>OTHER: </strong></td>
                    <td>{{ number_format($purchase->other_charges, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>TOTAL: </strong></td>
                    <td><strong>{{ number_format($purchase->grand_total, 2) }}</strong></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
