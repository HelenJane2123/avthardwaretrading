@extends('layouts.master')

@section('titel', 'Invoice | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Invoices Table</h1>
                <p class="text-muted mb-0">View, update, or delete existing invoices.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Invoice</li>
                <li class="breadcrumb-item active"><a href="#">Invoice Table</a></li>
            </ul>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a class="btn btn-sm btn-primary" href="{{route('invoice.create')}}"><i class="fa fa-plus"></i> Create New Invoice</a>
            <a class="btn btn-sm btn-success shadow-sm" href="{{ route('export.invoices') }}">
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </a>
        </div>
        @if(session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle"></i> {{ session()->get('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-check-circle"></i> {{ session()->get('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Invoice Records</h3>
                    <div class="tile-body">
                        <div class="d-flex justify-content-end align-items-center mb-3 flex-wrap">
                            <div class="mr-2">
                                <label for="filterLocation" class="mr-1">Filter by Location:</label>
                                <select id="filterLocation" class="form-control form-control-sm d-inline-block w-auto">
                                    <option value="">All Locations</option>
                                    <!-- Options will be populated by JS -->
                                </select>

                                <button id="bulkApprove" class="btn btn-success btn-sm">
                                    <i class="fa fa-check"></i> Approve Selected
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="invoiceTable">
                                <thead class="thead-dark medium">
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th>Location</th>
                                        <th>Invoice Date</th>
                                        <th>Due Date</th>
                                        <th>Subtotal</th>
                                        <th>Grand Total</th>
                                        <th>Outstanding</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Created By</th>
                                        <th>Date Created</th>
                                        <th>Updated By</th>
                                        <th>Date Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="medium">
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td>
                                                @if($invoice->invoice_status != 'approved' && $invoice->invoice_status != 'canceled')
                                                    <input type="checkbox" class="invoice-checkbox" value="{{ $invoice->id }}">
                                                @endif
                                            </td>
                                            <td><span class="badge badge-info px-1 py-1">{{ $invoice->invoice_number }}</span></td>
                                            <td class="text-truncate" style="max-width:120px;">{{ $invoice->customer->name }}</td>
                                            <td class="text-truncate" style="max-width:120px;">{{ $invoice->customer->location }}</td>
                                            <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</td>
                                            <td>{{ number_format($invoice->subtotal, 2) }}</td>
                                            <td>{{ number_format($invoice->grand_total, 2) }}</td>
                                            <td>{{ number_format($invoice->outstanding_balance, 2) }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($invoice->invoice_status == 'approved') bg-success
                                                    @elseif($invoice->invoice_status == 'pending') bg-warning
                                                    @elseif($invoice->invoice_status == 'canceled') bg-danger
                                                    @endif px-1 py-1">
                                                    {{ ucfirst($invoice->invoice_status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @if($invoice->payment_status == 'paid') bg-success
                                                    @elseif($invoice->payment_status == 'pending') bg-warning
                                                    @elseif($invoice->payment_status == 'overdue') bg-danger
                                                    @elseif($invoice->payment_status == 'partial') bg-info
                                                    @endif px-1 py-1">
                                                    {{ ucfirst($invoice->payment_status) }}
                                                </span>
                                            </td>
                                            <td>{{ $invoice->created_by && $invoice->user ? $invoice->user->f_name . ' ' . $invoice->user->l_name : 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($invoice->created_at)->format('M d, Y') }}</td>
                                            <td>{{ $invoice->updated_by && $invoice->updater ? $invoice->updater->f_name . ' ' . $invoice->updater->l_name : 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($invoice->updated_at)->format('M d, Y') }}</td>
                                            <td class="text-nowrap">
                                                <button class="btn btn-primary btn-sm p-1 view-invoice" data-id="{{ $invoice->id }}">
                                                    <i class="fa fa-eye fa-xs"></i>
                                                </button>

                                                @if($invoice->invoice_status == 'approved')
                                                    <a class="btn btn-secondary btn-sm p-1" href="{{ route('invoice.print', $invoice->id) }}" target="_blank">
                                                        <i class="fa fa-print fa-xs"></i>
                                                    </a>
                                                @elseif($invoice->invoice_status == 'canceled')
                                                    <span class="badge bg-danger text-light px-1 py-1">Canceled</span>
                                                @else
                                                    <a class="btn btn-info btn-sm p-1" href="{{ route('invoice.edit', $invoice->id) }}">
                                                        <i class="fa fa-edit fa-xs"></i>
                                                    </a>
                                                    @if(auth()->user()->user_role === 'super_admin')
                                                        <button class="btn btn-success btn-sm p-1" onclick="approveInvoice({{ $invoice->id }})">
                                                            <i class="fa fa-check fa-xs"></i>
                                                        </button>
                                                    @endif
                                                @endif

                                                <!-- <button class="btn btn-danger btn-sm p-1" onclick="deleteTag({{ $invoice->id }})">
                                                    <i class="fa fa-trash fa-xs"></i>
                                                </button> -->
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-modal="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invoiceModalLabel">Invoice Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="invoiceDetails">
                    <!-- AJAX loads details here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script type="text/javascript">
        var invoiceTable = $('#invoiceTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 10,
            "responsive": true
        });
        // Populate Location dropdown from table data
        var locationColumnIndex = 3; // Location column
        var locations = invoiceTable.column(locationColumnIndex).data().unique().sort();

        locations.each(function(d) {
            $('#filterLocation').append('<option value="' + d + '">' + d + '</option>');
        });

        // Filter table based on selection
        $('#filterLocation').on('change', function() {
            var val = $(this).val();
            invoiceTable.column(locationColumnIndex).search(val ? '^' + val + '$' : '', true, false).draw();
        });
        $('#filterLocation').on('change', function() {
            var locationId = $(this).val();
            table.column(0).search(locationId).draw();
        });
        $('#selectAll').on('click', function() {
            $('.invoice-checkbox').prop('checked', this.checked);
        });
        //bulk approval
        $('#bulkApprove').on('click', function () {
            var selectedIds = $('.invoice-checkbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                swal('No invoices selected', 'Please select invoices to approve.', 'warning');
                return;
            }

            swal({
                title: 'Confirm Bulk Approval',
                text: 'Only Admin can approve invoices.',
                input: 'password',
                inputPlaceholder: 'Enter Admin password',
                showCancelButton: true,
                confirmButtonText: 'Approve',
                confirmButtonColor: '#28a745',
                cancelButtonText: 'Cancel',
                preConfirm: function (password) {
                    return new Promise(function (resolve, reject) {
                        if (!password || password.trim() === '') {
                            reject('Please enter your password');
                            return;
                        }

                        const token = $('meta[name="csrf-token"]').attr('content');

                        $.ajax({
                            url: "{{ route('invoice.bulkApprove') }}",
                            type: 'POST', // POST + _method: PUT
                            data: {
                                _method: 'PUT',
                                _token: token,
                                ids: selectedIds,
                                password: password.trim()
                            },
                            success: function (response) {
                                if (response.error) {
                                    reject(response.error);
                                } else {
                                    resolve(response);
                                }
                            },
                            error: function (xhr) {
                                console.log(xhr);
                                reject('An error occurred during approval.');
                            }
                        });
                    });
                }
            }).then(function (result) {
                if (result && result.value && result.value.success) {
                    swal({
                        type: 'success',
                        title: 'Invoices have been approved!',
                        text: result.value.success,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 1500);
                }
            }).catch(function (error) {
                swal.showInputError ? swal.showInputError(error) : swal('Error', error, 'error');
            });
        });
        function deleteTag(id) {
            swal({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    event.preventDefault();
                    document.getElementById('delete-form-'+id).submit();
                }
            })
        }

        // Show modal with invoice details safely
        $(document).on('click', '.view-invoice', function () {
            const id = $(this).data('id');
            const $modal = $('#invoiceModal');
            const $details = $('#invoiceDetails');

            $details.html('<p class="text-muted">Loading details...</p>');
            $modal.modal('show');

            $.get(`/invoice/${id}`, function (data) {
                $details.html(data); // inject the modal HTML
            });
        });

        // Optional: clean up when closed
        $('#invoiceModal').on('hidden.bs.modal', function () {
            $('#invoiceDetails').empty();
        });

        function approveInvoice(id) {
            swal({
                title: 'Confirm Approval',
                text: 'Only Admin can approve invoices.',
                input: 'password',
                inputPlaceholder: 'Enter Admin password',
                showCancelButton: true,
                confirmButtonText: 'Approve',
                confirmButtonColor: '#28a745',
                cancelButtonText: 'Cancel',
                preConfirm: function (password) {
                    return new Promise(function (resolve, reject) {
                        if (!password) {
                            reject('Please enter your password');
                            return;
                        }

                        $.ajax({
                            url: `/invoice/${id}/approve`,
                            type: 'PUT',
                            data: {
                                _token: '{{ csrf_token() }}',
                                password: password
                            },
                            success: function (response) {
                                console.log("test response",response);
                                if (response.error) {
                                    reject(response.error);
                                } else {
                                    resolve(response);
                                }
                            },
                            error: function () {
                                reject('An error occurred during approval.');
                            }
                        });
                    });
                }
            }).then(function (result) {
                if (result && result.value && result.value.success) {
                    swal({
                        type: 'success',
                        title: 'Invoice has been approved!',
                        text: result.value.success,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 1500);
                }
            }).catch(function (error) {
                swal.showInputError ? swal.showInputError(error) : swal('Error', error, 'error');
            });
        }
    </script>
@endpush
