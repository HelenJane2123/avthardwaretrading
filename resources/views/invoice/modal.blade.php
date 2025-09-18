<div>
    <h5 class="mb-3">Invoice #{{ $invoice->invoice_number }}</h5>

    <!-- Partition: Customer & Invoice Info -->
    <div class="row">
        <!-- Customer Details -->
        <div class="col-md-6">
            <div class="card border p-3 mb-3">
                <h6 class="fw-bold">Customer Details</h6>
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th width="40%">Name</th>
                        <td>{{ $invoice->customer->name }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $invoice->customer->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Contact</th>
                        <td>{{ $invoice->customer->phone ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="col-md-6">
            <div class="card border p-3 mb-3">
                <h6 class="fw-bold">Invoice Details</h6>
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th width="40%">Invoice Date</th>
                        <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Due Date</th>
                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Payment Terms</th>
                        <td>{{ $invoice->paymentMode->name ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Product / Item Table -->
    <h6 class="fw-bold mt-3">Invoice Items</h6>
    <table class="table table-sm table-bordered">
        <thead>
            <tr class="table-light">
                <th>Code</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->product->product_code ?? '-' }}</td>
                    <td>{{ $item->product->product_name ?? 'Unknown' }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>₱{{ number_format($item->price, 2) }}</td>
                    <td>
                        @if($item->discount > 0)
                            {{ $item->discount }} {{ $item->discount_type == 'percent' ? '%' : '₱' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>₱{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="card border p-3 mt-3">
        <h6 class="fw-bold">Totals</h6>
        <table class="table table-sm table-bordered mb-0">
            <tr>
                <th width="40%">Subtotal</th>
                <td>₱{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <th>Discount</th>
                <td>
                    @if($invoice->discount_value > 0)
                        {{ $invoice->discount_value }} {{ $invoice->discount_type == 'percent' ? '%' : '₱' }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <th>Shipping</th>
                <td>₱{{ number_format($invoice->shipping ?? 0, 2) }}</td>
            </tr>
            <tr>
                <th>Other Charges</th>
                <td>₱{{ number_format($invoice->other_charges ?? 0, 2) }}</td>
            </tr>
            <tr class="table-light">
                <th>Grand Total</th>
                <td><strong>₱{{ number_format($invoice->grand_total, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Status Update -->
    <form id="statusForm" class="mt-3">
        @csrf
        <div class="mb-3">
            <label class="fw-bold">Status</label>
            <select name="invoice_status" class="form-control">
                <option value="pending" {{ $invoice->invoice_status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $invoice->invoice_status == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="canceled" {{ $invoice->invoice_status == 'cancelled' ? 'selected' : '' }}>Canceled</option>
            </select>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-success update-status" data-id="{{ $invoice->id }}">Update</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
    </form>
</div>

<script>
    $('.update-status').on('click', function () {
        let id = $(this).data('id');
        let formData = $('#statusForm').serialize();

        $.ajax({
            url: "{{ url('invoice') }}/" + id + "/status",
            type: "PATCH",
            data: formData,
            success: function () {
                alert('Status updated!');
                location.reload();
            }
        });
    });
</script>
