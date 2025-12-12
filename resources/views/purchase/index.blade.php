@extends('layouts.master')

@section('title', 'Purchase | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-th-list"></i> Purchases</h1>
            <p class="text-muted mb-0">View, update, or delete existing purchase orders.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('purchase.create') }}">
            <i class="fa fa-plus"></i> Add New Purchase
        </a>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a class="btn btn-primary" href="{{route('purchase.create')}}"><i class="fa fa-plus"></i> Create New Purchase</a>
        <a class="btn btn-success shadow-sm" href="{{ route('export.purchase') }}">
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
            <div class="tile shadow-sm">
                <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Purchase Records</h3>
                <div class="tile-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered" id="sampleTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>PO Number</th>
                                    <th>Supplier</th>
                                    <th>Salesman</th>
                                    <th>Date Purchased</th>
                                    <th>Discount Type</th>
                                    <th>Total Purchased</th>
                                    <th>Payment Status</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchases as $purchase)
                                    <tr>
                                        @php
                                            $totalPaid= $purchase->payments->sum('amount_paid');
                                            $outstanding = $purchase->grand_total - $totalPaid;

                                            if ($totalPaid == 0) {
                                                $status = 'none'; // No payment yet
                                            } elseif ($totalPaid < $purchase->grand_total) {
                                                $status = 'partial';
                                            } else {
                                                $status = 'paid';
                                            }
                                        @endphp
                                        <td><span class="badge badge-info">{{ $purchase->po_number }}</span></td>
                                        <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                                        <td>{{ $purchase->salesman->salesman_name ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y') }}</td>
                                        <td>
                                            @if ($purchase->discount_type === 'per_item')
                                                <span class="badge bg-success">Per Item</span>
                                            @elseif ($purchase->discount_type === 'overall')
                                                <span class="badge bg-warning text-dark">Overall</span>
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>₱ {{ number_format($purchase->grand_total, 2) }}</td>
                                        <td>
                                            @if($status === 'paid')
                                                {{-- Fully paid, show info instead of Make Payment button --}}
                                                <span class="badge bg-success ms-2">Paid</span>
                                                <span class="badge bg-info ms-1">₱ {{ number_format($totalPaid, 2) }}</span>
                                                <span class="badge bg-warning text-dark ms-1">Outstanding: ₱ {{ number_format($outstanding, 2) }}</span>
                                            @elseif($status === 'partial')
                                                <span class="badge bg-warning ms-2">Partial Payment</span>
                                                <span class="badge bg-info ms-1">₱ {{ number_format($totalPaid, 2) }}</span>
                                                <span class="badge bg-warning text-dark ms-1">Outstanding: ₱ {{ number_format($outstanding, 2) }}</span>
                                            @else
                                                <span class="badge bg-info ms-2">No Payment yet</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($purchase->is_approved === 1)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                {{-- View Details --}}
                                                <button class="btn btn-info btn-sm view-btn"
                                                        data-id="{{ $purchase->id }}"
                                                        title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                {{-- Print --}}
                                                @if ($purchase->is_approved === 1)
                                                    <a href="{{ route('purchase.print', $purchase->id) }}" 
                                                        target="_blank" 
                                                        class="btn btn-secondary btn-sm" 
                                                        title="Print PO">
                                                            <i class="fa fa-print"></i>
                                                    </a>
                                                    <button class="btn btn-success btn-sm" onclick="completePurchaseOrder({{ $purchase->id }})">
                                                        <i class="fa fa-check"></i> Complete Order
                                                    </button>
                                                @endif
                                                {{-- Edit --}}
                                                @if ($purchase->is_approved !== 1)
                                                    <a class="btn btn-primary btn-sm" 
                                                        href="{{ route('purchase.edit', $purchase->id) }}" 
                                                        title="Edit">
                                                            <i class="fa fa-edit"></i>
                                                    </a>
                                                    @if(auth()->user()->user_role === 'super_admin')
                                                        <button class="btn btn-success btn-sm" onclick="approvePurchase({{ $purchase->id }})">
                                                            <i class="fa fa-check"></i> Approve
                                                        </button>
                                                    @endif
                                                @else
                                                    @if($status !== 'paid')
                                                        {{-- Not fully paid, show Make Payment button --}}
                                                        <button class="btn btn-warning btn-sm payment-btn ms-1" data-id="{{ $purchase->id }}">
                                                            <i class="fa fa-credit-card"></i> Make Payment
                                                        </button>
                                                    @endif
                                                @endif

                                                {{-- Delete --}}
                                                <button class="btn btn-danger btn-sm" 
                                                        onclick="deleteTag({{ $purchase->id }})"
                                                        title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>

                                            {{-- Hidden Delete Form --}}
                                            <form id="delete-form-{{ $purchase->id }}" 
                                                action="{{ route('purchase.destroy', $purchase->id) }}" 
                                                method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
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

    {{-- Purchase Details Modal --}}
    <div class="modal fade" id="viewPurchaseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-info-circle"></i> Purchase Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="purchase-details">
                    {{-- Filled dynamically --}}
                </div>
            </div>
        </div>
    </div>
    {{-- Make Payment Modal --}}
    <div class="modal fade" id="makePaymentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa fa-credit-card"></i> Make Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="make-payment-form" method="POST">
                    @csrf
                    <input type="hidden" name="purchase_id" id="payment-purchase-id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="po_number" class="form-label">PO Number</label>
                            <input type="text" class="form-control" id="po_number" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="mode_of_payment" class="form-label">Mode of Payment</label>
                            <input type="text" class="form-control" id="mode_of_payment" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="outstanding_balance" class="form-label">Outstanding Balance</label>
                            <input type="text" class="form-control" id="outstanding_balance" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-control" id="payment_status" name="payment_status" required>
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="amount_paid" class="form-label">Amount Paid</label>
                            <input type="number" step="0.01" class="form-control" id="amount_paid" name="amount_paid" required>
                        </div>

                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date</label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div id="check-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="check_number" class="form-label">Check Number</label>
                                <input type="text" class="form-control" id="check_number" name="check_number"
                                    placeholder="Enter check number">
                            </div>
                        </div>

                        <div id="gcash-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="gcash_number" class="form-label">GCash Number</label>
                                <input type="text" class="form-control" id="gcash_number" name="gcash_number"
                                    placeholder="Enter GCash number">
                            </div>
                            <div class="mb-3">
                                <label for="gcash_name" class="form-label">GCash Name</label>
                                <input type="text" class="form-control" id="gcash_name" name="gcash_name"
                                    placeholder="Enter GCash account name">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

@push('js')
<script src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
<script src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<script>
    $('#sampleTable').DataTable();

    function deleteTag(id) {
        swal({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
        }).then((result) => {
            if (result.value) {
                document.getElementById('delete-form-'+id).submit();
            } else {
                swal('Cancelled', 'Your record is safe :)', 'error');
            }
        })
    }

    function completePurchaseOrder(id) {
        swal({
            title: "Complete Purchase Order?",
            text: "This will add all received items into your inventory.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            confirmButtonText: "Yes, Complete",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: `/purchase/${id}/complete`,
                    type: "PUT",
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        swal({
                            type: "success",
                            title: "Purchase Completed!",
                            text: response.success,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function(xhr) {
                        swal("Error", xhr.responseJSON.error || "Something went wrong.", "error");
                    }
                });
            }
        });
    }

    // Load Purchase Details into Modal
    $(document).on("click", ".view-btn", function () {
        let id = $(this).data("id");
        $.get("purchase/" + id + "/details", function (data) {
            $("#purchase-details").html(data);
            $("#viewPurchaseModal").modal("show");
        });
    });
    
    // Open Make Payment Modal
   $(document).on("click", ".payment-btn", function () {
        let purchaseId = $(this).data("id");

        // Reset form fields
        $("#make-payment-form")[0].reset();
        $("#payment-purchase-id").val(purchaseId);

        // Hide conditional fields
        $("#check-fields").hide();
        $("#gcash-fields").hide();

        // Set the form action dynamically
        $("#make-payment-form").attr("action", "/purchase/" + purchaseId + "/payment-store");

        // Fetch purchase details via AJAX
        $.get('/purchase/' + purchaseId + '/payment-info', function (data) {
            $("#po_number").val(data.po_number);
            $("#outstanding_balance").val(data.outstanding_balance);
            $("#payment_status").val(data.payment_status);

            // Set mode of payment dropdown
            $("#mode_of_payment").val(data.mode_of_payment_name +' ('+data.mode_of_payment_term+' Days)').trigger("change");

            // Hide all optional fields first
            $("#check-fields").hide();
            $("#gcash-fields").hide();

            // Show and populate based on payment mode name
            if (data.mode_of_payment_name && data.mode_of_payment_name.toLowerCase().includes("pdc/check")) {
                $("#check_number").val(data.check_number || '');
                $("#check-fields").show();
            } 
            else if (data.mode_of_payment_name && data.mode_of_payment_name.toLowerCase().includes("gcash")) {
                $("#gcash_number").val(data.gcash_number || '');
                $("#gcash_name").val(data.gcash_name || '');
                $("#gcash-fields").show();
            }

            $("#makePaymentModal").modal("show");
        });
    });
    function approvePurchase(id) {
        swal({
            title: 'Confirm Approval',
            text: 'Only Admin can approve purchases.',
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
                        url: `/purchase/${id}/approve`,
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
                    title: 'Purchase has been approved!',
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
