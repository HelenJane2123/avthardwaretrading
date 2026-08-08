@extends('layouts.master')

@section('title', 'Add Collection | ')

@section('content')

@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-money"></i> Add Collection</h1>
            <p class="text-muted mb-0">Record customer payments and update invoice balances.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="breadcrumb-item">Collection</li>
            <li class="breadcrumb-item active">Add Collection</li>
        </ul>
    </div>
    <div class="mb-3">
        <a href="{{ route('collection.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-list"></i> Manage Collections
        </a>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success">{{ session()->get('message') }}</div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <h3 class="tile-title"><i class="fa fa-money"></i> Collection Information</h3>
                <div class="tile-body">
                    @if($invoices->isEmpty())
                        <div class="alert alert-warning">
                            No invoice available for collection.
                            <a href="{{ route('invoice.create') }}">Create Invoice</a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('collection.store') }}">
                            @csrf

                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong><i class="fa fa-file-text"></i> Invoice Selection</strong>
                                </div>
                                <div class="card-body">
                                    <label>Select Invoice</label>
                                    <div class="input-group">
                                        <input type="text"
                                            id="invoiceSearch"
                                            class="form-control form-control-sm"
                                            placeholder="Search Invoice..."
                                            readonly>
                                        <div class="input-group-append">
                                            <button type="button"
                                                class="btn btn-primary btn-sm"
                                                data-toggle="modal"
                                                data-target="#invoiceModal">
                                                <i class="fa fa-search"></i> Search
                                            </button>
                                        </div>
                                    </div>

                                    <input type="hidden" name="invoice_id" id="invoiceId">
                                    <input type="hidden" name="customer_id" id="customerId">
                                </div>
                            </div>

                            <div id="invoiceDetails" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <strong><i class="fa fa-file"></i> Invoice Details</strong>
                                            </div>
                                            <div class="card-body p-2">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <tr>
                                                        <th width="40%">Invoice #</th>
                                                        <td id="detailInvoiceNumber"></td>
                                                    </tr>
                                                    <tr>
                                                        <th width="40%">Invoice Date</th>
                                                        <td id="detailInvoiceDate"></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total Amount</th>
                                                        <td><span id="detailGrandTotal"></span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Balance</th>
                                                        <td class="text-danger"><span id="detailBalance"></span></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Payment Mode</th>
                                                        <td id="ModeofPayment"></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <strong><i class="fa fa-user"></i> Customer Details</strong>
                                            </div>
                                            <div class="card-body p-2">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <tr>
                                                        <th width="35%">Name</th>
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
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Collection Number</label>
                                        <input type="text"
                                            name="collection_number"
                                            id="collectionNumber"
                                            class="form-control form-control-sm"
                                            readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Date</label>
                                        <input type="text"
                                            id="payment_date_display"
                                            class="form-control form-control-sm"
                                            value="{{ date('F d, Y') }}"
                                            required>

                                        <input type="hidden"
                                            id="paymentDateInput"
                                            name="payment_date"
                                            class="form-control form-control-sm"
                                            value="{{ date('Y-m-d') }}"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong><i class="fa fa-credit-card"></i> Payment Details</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6" id="pdcBankName" style="display:none;">
                                            <div class="form-group">
                                                <label>Bank Name</label>
                                                <input type="text"
                                                    name="bank_name"
                                                    class="form-control form-control-sm"
                                                    placeholder="Enter bank name">
                                            </div>
                                        </div>

                                        <div class="col-md-6" id="pdcCheck" style="display:none;">
                                            <div class="form-group">
                                                <label>Check Date</label>
                                                <input type="text"
                                                    id="check_date_display"
                                                    class="form-control form-control-sm"
                                                    value="{{ date('F d, Y') }}"
                                                    required>
                                                <input type="hidden"
                                                    id="checkDateInput"
                                                    name="check_date"
                                                    class="form-control form-control-sm"
                                                    value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6" id="pdcFields" style="display:none;">
                                            <div class="form-group">
                                                <label>Check Number</label>
                                                <input type="text"
                                                    name="check_number"
                                                    class="form-control form-control-sm"
                                                    placeholder="Enter check number">
                                            </div>
                                        </div>

                                        <div class="col-md-6" id="pdcCheckAmount" style="display:none;">
                                            <div class="form-group">
                                                <label>Check Amount</label>
                                                <input type="text"
                                                    id="checkAmountDisplay"
                                                    class="form-control form-control-sm"
                                                    inputmode="decimal"
                                                    placeholder="Enter check amount">
                                                <input type="hidden"
                                                    name="check_amount"
                                                    id="checkAmountHidden">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Amount Paid</label>
                                                <input type="text"
                                                    id="amountPaidDisplay"
                                                    class="form-control form-control-sm"
                                                    inputmode="decimal"
                                                    placeholder="Enter amount paid"
                                                    required>
                                                <input type="hidden"
                                                    name="amount_paid"
                                                    id="amountPaidHidden">
                                            </div>
                                        </div>
                                    </div>

                                    <div id="gcashFields" style="display:none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>GCash Name</label>
                                                    <input type="text"
                                                        name="gcash_name"
                                                        class="form-control form-control-sm"
                                                        placeholder="GCash account name">
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>GCash Mobile Number</label>
                                                    <input type="text"
                                                        name="gcash_number"
                                                        class="form-control form-control-sm"
                                                        placeholder="Mobile number">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea name="remarks"
                                            rows="3"
                                            class="form-control form-control-sm"
                                            placeholder="Optional remarks"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3" id="paymentHistorySection" style="display:none;">
                                <div class="card-header bg-light">
                                    <strong><i class="fa fa-history"></i> Previous Payments</strong>
                                </div>
                                <div class="card-body p-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Bank</th>
                                                    <th>Check #</th>
                                                </tr>
                                            </thead>
                                            <tbody id="paymentHistoryBody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fa fa-save"></i> Save Collection
                            </button>
                            <a href="{{ route('collection.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 1200px; width: 95%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-search"></i> Select Invoice</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="invoiceTable" width="100%" class="table table-bordered table-striped table-hover mb-0">
                        <thead class="thead-light">
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
</div>
@endsection

@push('js')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#payment_date_display').datepicker({
                dateFormat: 'MM dd, yy',
                onSelect: function(dateText) {
                    $('#payment_date').val(formatToYMD(dateText));
                }
            });
             $('#check_date_display').datepicker({
                dateFormat: 'MM dd, yy',
                onSelect: function(dateText) {
                    $('#check_date').val(formatToYMD(dateText));
                }
            });
            function togglePaymentFields(mode) {
                $('#pdcBankName').hide();
                $('#pdcFields').hide();
                $('#pdcCheck').hide();
                $('#pdcCheckAmount').hide();
                $('#gcashFields').hide();
                if (!mode) {
                    return;
                }
                mode = mode.toLowerCase();
                if (mode === 'pdc/check') {
                    $('#pdcBankName').show();
                    $('#pdcFields').show();
                    $('#pdcCheck').show();
                    $('#pdcCheckAmount').show();
                } else if (mode === 'gcash') {
                    $('#gcashFields').show();
                }
            }
            function formatCurrency(value) {
        const number = Number(value);
        if (Number.isNaN(number)) return '';
        return '₱' + number.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatDate(value) {
        if (!value) return '';
        let date = new Date(value);
        if (isNaN(date.getTime())) return '';
        return date.toLocaleDateString(undefined, {
            month: 'short',
            day: '2-digit',
            year: 'numeric'
        });
    }

    function parseNumericAmount(value) {
        const cleaned = String(value ?? '').replace(/[^0-9.]/g, '');
        if (!cleaned) {
            return '';
        }

        const parts = cleaned.split('.');
        if (parts.length > 2) {
            return parts.shift() + '.' + parts.join('');
        }

        if (parts.length <= 1) {
            return parts[0] || '';
        }

        return parts[0] + '.' + parts[1].slice(0, 2);
    }

    function updateAmountDisplay() {
        const raw = parseNumericAmount($('#amountPaidHidden').val());
        $('#amountPaidHidden').val(raw);
        $('#amountPaidDisplay').val(raw ? formatCurrency(raw) : '');
    }

    function updateCheckAmountDisplay() {
        const raw = parseNumericAmount($('#checkAmountHidden').val());
        $('#checkAmountHidden').val(raw);
        $('#checkAmountDisplay').val(raw ? formatCurrency(raw) : '');
    }

    function bindCurrencyField(displaySelector, hiddenSelector) {
        $(displaySelector).on('input', function() {
            let raw = $(this).val().replace(/[^0-9.]/g, '');

            if (raw === '.') {
                raw = '0.';
            }

            const parts = raw.split('.');
            if (parts.length > 2) {
                raw = parts.shift() + '.' + parts.join('');
            }

            $(hiddenSelector).val(raw);
            $(this).val(raw);
        });

        $(displaySelector).on('focus', function() {
            const raw = $(this).val().replace(/[^0-9.]/g, '');
            $(this).val(raw);
        });

        $(displaySelector).on('blur', function() {
            const raw = parseNumericAmount($(this).val());
            $(hiddenSelector).val(raw);

            if (!raw) {
                $(this).val('');
                return;
            }

            $(this).val(formatCurrency(raw));
        });
    }

    bindCurrencyField('#amountPaidDisplay', '#amountPaidHidden');
    bindCurrencyField('#checkAmountDisplay', '#checkAmountHidden');
        let invoiceTable = $('#invoiceTable').DataTable({
            processing: true,
            serverSide: false,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[0, 'desc']],
            responsive: true,
            ajax: {
                url: "{{ route('invoices.search') }}",
                dataSrc: ""
            },
            columns: [
                { data: 'invoice_number' },
                { data: 'customer.name', defaultContent: '' },
                {
                    data: 'grand_total',
                    render: function(data) {
                        return '₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 });
                    }
                },
                {
                    data: 'balance',
                    render: function(data) {
                        return '₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 });
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        return `
                            <button type="button" class="btn btn-primary btn-sm selectInvoice"
                                data-id="${data.id}"
                                data-number="${data.invoice_number}"
                                data-date="${data.invoice_date}"
                                data-total="${data.grand_total}"
                                data-balance="${data.balance}"
                                data-customer="${data.customer?.id ?? ''}"
                                data-name="${data.customer?.name ?? ''}"
                                data-email="${data.customer?.email ?? ''}"
                                data-phone="${data.customer?.mobile ?? ''}"
                                data-address="${data.customer?.address ?? ''}"
                                data-payment="${data.payment_mode?.name ?? ''}">
                                <i class="fa fa-check"></i> Select
                            </button>`;
                    }
                }
            ]
        });

        $('#invoiceModal').on('shown.bs.modal', function() {
            invoiceTable.ajax.reload();
        });

        $(document).on('click', '.selectInvoice', function() {
            let invoiceId = $(this).data('id');
            let paymentMode = $(this).data('payment');
            let balance = parseFloat($(this).data('balance')) || 0;

            $('#invoiceId').val(invoiceId);
            $('#customerId').val($(this).data('customer'));
            $('#invoiceSearch').val($(this).data('number'));

            $('#invoiceDetails').show();
            $('#paymentHistorySection').show();
            $('#amountPaidDisplay').prop('disabled', false);

            $('#detailInvoiceNumber').text($(this).data('number'));
            $('#detailInvoiceDate').text(formatDate($(this).data('date')));
            console.log('Invoice Date:', $(this).data('date'));
            $('#detailGrandTotal').text(formatCurrency($(this).data('total')));
            $('#detailBalance').text(formatCurrency(balance));
            $('#detailCustomerName').text($(this).data('name'));
            $('#detailCustomerEmail').text($(this).data('email') || 'N/A');
            $('#detailCustomerPhone').text($(this).data('phone') || 'N/A');
            $('#detailCustomerAddress').text($(this).data('address') || 'N/A');
            $('#ModeofPayment').text(paymentMode);

            togglePaymentFields(paymentMode);

            $.ajax({
                url: `/invoice/${invoiceId}/collections`,
                type: 'GET',
                success: function(data) {
                    let tbody = $('#paymentHistoryBody');
                    tbody.empty();

                    if (!data || data.length === 0) {
                        tbody.append(`
                            <tr>
                                <td colspan="4" class="text-center">No previous payments.</td>
                            </tr>
                        `);
                        return;
                    }

                    $.each(data, function(index, row) {
                        tbody.append(`
                            <tr>
                                <td>${formatDate(row.payment_date)}</td>
                                <td>${formatCurrency(row.amount_paid)}</td>
                                <td>${row.bank_name ?? ''}</td>
                                <td>${row.check_number ?? ''}</td>
                            </tr>
                        `);
                    });
                }
            });

            $('#invoiceModal').modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        });

        function generateCollectionNumber() {
            let date = new Date();
            let year = date.getFullYear();
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let day = String(date.getDate()).padStart(2, '0');
            let random = Math.floor(1000 + Math.random() * 9000);
            return `COL-${year}${month}${day}-${random}`;
        }

        $('#collectionNumber').val(generateCollectionNumber());

        $('form').submit(function(e) {
            let balance = parseFloat($('#detailBalance').text().replace(/[^0-9.-]+/g, '')) || 0;
            let amount = parseFloat($('#amountPaidHidden').val()) || 0;

            if (!$('#invoiceId').val()) {
                e.preventDefault();
                Swal.fire('Invoice Required', 'Please select invoice first.', 'error');
                return false;
            }

            if (amount <= 0) {
                e.preventDefault();
                Swal.fire('Invalid Amount', 'Payment amount must be greater than zero.', 'error');
                return false;
            }

            if (amount > balance) {
                e.preventDefault();
                Swal.fire('Invalid Payment', `Amount paid cannot exceed balance ₱${balance.toFixed(2)}`, 'error');
                return false;
            }
        });
    });
</script>
@endpush
