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
    {{-- Success Message --}}
    @if(session()->has('message'))
        <div class="alert alert-success">{{ session()->get('message') }}</div>
    @endif
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Purchase Records</h3>
                <div class="tile-body">
                    <table class="table table-striped table-hover table-bordered" id="sampleTable">
                        <thead class="table-dark">
                            <tr>
                                <th>PO Number</th>
                                <th>Supplier</th>
                                <th>Salesman</th>
                                <th>Date Purchased</th>
                                <th>Discount Type</th>
                                <th>Total Purchased</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $purchase)
                                <tr>
                                    <td><span class="badge badge-info">{{ $purchase->po_number }}</span></td>
                                    <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                                    <td>{{ $purchase->salesman ?? '-' }}</td>
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
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            {{-- View Details --}}
                                            <button class="btn btn-info btn-sm view-btn"
                                                    data-id="{{ $purchase->id }}"
                                                    title="View Details">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            {{-- Print --}}
                                            <a href="{{ route('purchase.print', $purchase->id) }}" 
                                               target="_blank" 
                                               class="btn btn-secondary btn-sm" 
                                               title="Print PO">
                                                <i class="fa fa-print"></i>
                                            </a>
                                            {{-- Edit --}}
                                            <a class="btn btn-primary btn-sm" 
                                               href="{{ route('purchase.edit', $purchase->id) }}" 
                                               title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
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

    // Load Purchase Details into Modal
    $(document).on("click", ".view-btn", function () {
        let id = $(this).data("id");
        $.get("purchase/" + id + "/details", function (data) {
            $("#purchase-details").html(data);
            $("#viewPurchaseModal").modal("show");
        });
    });
</script>
@endpush
