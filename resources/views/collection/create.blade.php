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
                                <label for="invoiceSearch">Select Invoice</label>
                                <div class="input-group">
                                    <input type="text" id="invoiceSearch" class="form-control" placeholder="Search Invoice..." readonly>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#invoiceModal">
                                    Search Invoice
                                    </button>
                                </div>
                                <input type="hidden" name="invoice_id" id="invoiceId">
                                <input type="hidden" name="customer_id" id="customerId">
                            </div>

                            <div id="invoiceDetails" class="mt-3" style="display:none;">
                                <h5>Invoice Details</h5>
                                <table class="table table-bordered table-sm">
                                    <tbody>
                                        <tr>
                                            <th width="30%">Invoice #</th>
                                            <td id="detailInvoiceNumber"></td>
                                        </tr>
                                        <tr>
                                            <th>Total Amount</th>
                                            <td>₱<span id="detailGrandTotal"></span></td>
                                        </tr>
                                        <tr>
                                            <th>Balance</th>
                                            <td>₱<span id="detailBalance"></span></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Mode:</th>
                                            <td id="ModeofPayment"></td>
                                        </tr>                              
                                    </tbody>
                                </table>

                                <h5>Customer Details</h5>
                                <table class="table table-bordered table-sm">
                                    <tbody>
                                        <tr>
                                            <th width="30%">Name</th>
                                            <td id="detailCustomerName"></td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td id="detailCustomerEmail"></td>
                                        </tr>
                                        <tr>
                                            <th>Phone</th>
                                            <td id="detailCustomerPhone"></td>
                                        </tr>
                                        <tr>
                                            <th>Address</th>
                                            <td id="detailCustomerAddress"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mb-3">
                                <label>Collection Number</label>
                                <input type="text" name="collection_number" class="form-control" id="collectionNumber" readonly>
                            </div>
                            <div class="mb-3">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label>Amount Paid</label>
                                <input type="number" step="0.01" name="amount_paid" class="form-control" required>
                            </div>
                            <!-- Extra Fields Based on Payment Method -->
                            <div id="pdcCheck" class="mb-3" style="display: none;">
                                <label>Check Date</label>
                                <input type="date" name="check_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div id="pdcFields" class="mb-3" style="display: none;">
                                <label>Check Number</label>
                                <input type="text" name="check_number" class="form-control" placeholder="Enter check number">
                            </div>

                            <div id="gcashFields" style="display: none;">
                                <div class="mb-3">
                                    <label>GCash Name</label>
                                    <input type="text" name="gcash_name" class="form-control" placeholder="Enter GCash account name">
                                </div>
                                <div class="mb-3">
                                    <label>GCash Mobile Number</label>
                                    <input type="text" name="gcash_number" class="form-control" placeholder="Enter mobile number">
                                </div>
                            </div>

                            <!-- <div class="mb-3">
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
                            </div> -->

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
<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select Invoice</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table id="invoiceTable" class="table table-bordered table-striped table-hover w-100">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Balance</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script src="{{ asset('/') }}js/plugins/jquery.dataTables.min.js"></script>
<script src="{{ asset('/') }}js/plugins/dataTables.bootstrap.min.js"></script>
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<script>
    $(document).ready(function () {
        function togglePaymentFields(mode) {
            $('#pdcFields').hide();
            $('#pdcCheck').hide();
            $('#gcashFields').hide();

            if (mode.toLowerCase() === 'pdc/check') {
                $('#pdcFields').show();
                $('#pdcCheck').show();
            } else if (mode.toLowerCase() === 'gcash') {
                $('#gcashFields').show();
            }
        }

        let table = $('#invoiceTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('invoices.search') }}",
                dataSrc: ""
            },
            columns: [
                { data: "invoice_number" },
                { data: "customer.name", defaultContent: "" },
                { data: "grand_total", render: data => `₱${parseFloat(data).toFixed(2)}` },
                { data: "balance", render: data => `₱${parseFloat(data).toFixed(2)}` },
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-primary select-invoice"
                                    data-id="${data.id}" 
                                    data-number="${data.invoice_number}"
                                    data-total="${data.grand_total}"
                                    data-balance="${data.balance}"
                                    data-customer="${data.customer?.id ?? ''}"
                                    data-name="${data.customer?.name ?? ''}"
                                    data-email="${data.customer?.email ?? ''}"
                                    data-phone="${data.customer?.mobile ?? ''}"
                                    data-modeofpayment="${data.payment_mode?.name ?? ''}"
                                    data-address="${data.customer?.address ?? ''}">
                                    Select
                                </button>`;
                    }
                }
            ],
            autoWidth: false,
            responsive: true,
            dom: 'frtip'
        });

    // Reload invoices when modal opens
    $('#invoiceModal').on('shown.bs.modal', function () {
        table.ajax.reload();
    });

    // Handle Select button
    $(document).on('click', '.select-invoice', function () {
        const paymentMode = $(this).data("modeofpayment") || "N/A";
        
        $('#invoiceId').val($(this).data('id'));
        $('#customerId').val($(this).data('customer'));
        $('#invoiceSearch').val($(this).data('number'));

        // Show details in table format
        $("#invoiceDetails").show();
        $("#detailInvoiceNumber").text($(this).data("number"));
        $("#detailGrandTotal").text(parseFloat($(this).data("total")).toFixed(2));
        $("#detailBalance").text(parseFloat($(this).data("balance")).toFixed(2));
        $("#detailCustomerName").text($(this).data("name"));
        $("#detailCustomerEmail").text($(this).data("email") || "N/A");
        $("#detailCustomerPhone").text($(this).data("phone") || "N/A");
        $("#detailCustomerAddress").text($(this).data("address") || "N/A");
        $("#ModeofPayment").text(paymentMode);

        // Show appropriate extra fields
        togglePaymentFields(paymentMode);

        // Close modal safely
        $('#invoiceModal').modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
});
function generateCollectionNumber() {
    // Format: COL-YYYYMMDD-RANDOM
    const date = new Date();
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    const random = Math.floor(1000 + Math.random() * 9000); // 4-digit random number
    return `COL-${y}${m}${d}-${random}`;
}

// Set number when page loads
document.addEventListener('DOMContentLoaded', function() {
    const collectionNumberField = document.getElementById('collectionNumber');
    collectionNumberField.value = generateCollectionNumber();
});

// Validation: Prevent overpayment
$(document).on("submit", "form", function (e) {
    const balanceText = $("#detailBalance").text().trim();
    const amountPaid = parseFloat($("input[name='amount_paid']").val()) || 0;
    const balance = parseFloat(balanceText.replace(/,/g, "")) || 0;

    if (amountPaid > balance) {
        e.preventDefault();
        Swal.fire({
            icon: "error",
            title: "Invalid Payment",
            text: `The amount paid (₱${amountPaid.toFixed(2)}) cannot exceed the balance (₱${balance.toFixed(2)}).`,
            confirmButtonColor: "#d33",
        });
    }
});
</script>
@endpush