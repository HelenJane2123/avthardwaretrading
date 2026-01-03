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
                        <td>{{ $invoice->customer->mobile ?? '-' }}</td>
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
                        <td>{{ $invoice->paymentMode->name ?? 'N/A' }} - {{ $invoice->paymentMode->term ?? '' }}</td>
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
                        @php
                            $discounts = [];

                            foreach ([$item->discount_1, $item->discount_2, $item->discount_3] as $discount) {
                                if ($discount > 0) {
                                    $formatted = fmod($discount, 1) == 0
                                        ? (int)$discount      
                                        : rtrim(rtrim($discount, '0'), '.'); // decimal

                                    $discounts[] = $formatted . '%';
                                }
                            }
                        @endphp

                        @if(count($discounts))
                            {{ ucfirst($item->discount_less_add) }} {{ implode(' ', $discounts) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>₱{{ number_format($item->amount, 2) }}</td>
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
                        {{ $invoice->discount_value }} {{ $invoice->discount_type == 'percent' ? '%' : '' }}
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
            <select name="invoice_status" class="form-control"
                {{ $invoice->invoice_status == 'canceled' ? 'disabled' : '' }}>
                <option value="pending" {{ $invoice->invoice_status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $invoice->invoice_status == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="canceled" {{ $invoice->invoice_status == 'canceled' ? 'selected' : '' }}>Canceled</option>
            </select>
        </div>

        <div class="modal-footer">
            @if($invoice->invoice_status != 'canceled')
                <button type="button" class="btn btn-success update-status" data-id="{{ $invoice->id }}">
                    Update
                </button>
            @else
                <span class="badge bg-danger">This invoice is already canceled</span>
            @endif
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
    </form>
</div>
<!-- Approve Confirmation Modal -->
<div class="modal fade" id="confirmApproveModal" tabindex="-1" role="dialog" aria-labelledby="confirmApproveLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="confirmApproveLabel">Confirm Approval</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Enter the Super Admin password to approve this invoice:</p>
        <input type="password" id="adminPassword" class="form-control" placeholder="Enter password">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="confirmApproveBtn">Approve</button>
      </div>
    </div>
  </div>
</div>

<script>
$('.update-status').on('click', function () {
    const id = $(this).data('id');
    const selectedStatus = $('select[name="invoice_status"]').val();

    // If approving, show inline password box
    if (selectedStatus === 'approved') {
        // Build password prompt markup
        const passwordPrompt = `
            <div id="approveBox" class="mt-3 p-3 border rounded bg-light">
                <label class="fw-bold mb-2">Super Admin Password:</label>
                <input type="password" id="adminPassword" class="form-control mb-2" placeholder="Enter password">
                <div class="text-end">
                    <button type="button" class="btn btn-success btn-sm" id="confirmApproveBtn">Confirm Approval</button>
                    <button type="button" class="btn btn-secondary btn-sm" id="cancelApproveBtn">Cancel</button>
                </div>
            </div>
        `;

        // Prevent duplicate prompt
        if (!$('#approveBox').length) {
            $('.modal-footer').before(passwordPrompt);
        }

        // Handle cancel button
        $(document).on('click', '#cancelApproveBtn', function () {
            $('#approveBox').remove();
        });

        // Handle confirm button
        $(document).on('click', '#confirmApproveBtn', function () {
            const password = $('#adminPassword').val().trim();
            if (!password) {
                alert('Please enter password.');
                return;
            }

            $.ajax({
                url: "{{ url('invoice') }}/" + id + "/approve",
                type: "PUT",
                data: {
                    _token: '{{ csrf_token() }}',
                    password: password
                },
                success: function (response) {
                    if (response.error) {
                        $('#statusMessageTitle').text('Approval Failed!');
                        $('#statusMessageText').text(response.error);
                        $('#statusMessageModal .modal-body i').removeClass('text-success').addClass('text-danger');
                        $('#statusMessageModal').modal('show');
                    } else {
                        $('#statusMessageTitle').text('Invoice Approved!');
                        $('#statusMessageText').text('The invoice was successfully approved.');
                        $('#statusMessageModal .modal-body i').removeClass('text-danger').addClass('text-success');
                        $('#statusMessageModal').modal('show');
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.error || 'Approval failed.');
                }
            });
        });

    } else {
        // Regular update (Pending or Canceled)
        $.ajax({
            url: "{{ url('invoice') }}/" + id + "/status",
            type: "PATCH",
            data: $('#statusForm').serialize(),
            success: function () {
                $('#statusMessageTitle').text('Invoice Status Updated!');
                $('#statusMessageText').text('Status successfully approved.');
                $('#statusMessageModal .modal-body i').removeClass('text-danger').addClass('text-success');
                $('#statusMessageModal').modal('show');
                setTimeout(() => location.reload(), 1500);
            }
        });
    }
});
</script>