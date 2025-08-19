@extends('layouts.master')

@section('title', 'Purchase | ')
@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title">
        <div>
            <h1><i class="fa fa-th-list"></i> Purchase Table</h1>
        </div>
    </div>

    <div class="">
        <a class="btn btn-primary" href="{{ route('purchase.create') }}"><i class="fa fa-plus"></i> Add New Purchase</a>
    </div>

    <div class="row mt-2">
        <div class="col-md-12">
            <div class="tile">
                <div class="tile-body">
                    <table class="table table-hover table-bordered" id="sampleTable">
                        <thead>
                            <tr>
                                <th>Purchase Number</th>
                                <th>Supplier</th>
                                <th>Salesman</th>
                                <th>Date Purchased</th>
                                <th>Discount Type</th>
                                <th>Total Purchased</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->po_number }}</td>
                                    <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                                    <td>{{ $purchase->salesman }}</td>
                                    <td>{{ $purchase->date }}</td>
                                    <td>
                                        @if ($purchase->discount_type === 'per_item')
                                            <span class="badge badge-success">Per Item Discount</span>
                                        @elseif ($purchase->discount_type === 'overall')
                                            <span class="badge badge-warning">Overall Discount</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($purchase->grand_total, 2) }}</td>
                                    <td>
                                        <!-- Details Modal -->
                                        <button class="btn btn-info btn-sm view-btn"
                                                data-id="{{ $purchase->id }}">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <!-- Print Page -->
                                        <a href="{{ route('purchase.print', $purchase->id) }}" target="_blank" class="btn btn-secondary btn-sm">
                                            <i class="fa fa-print"></i>
                                        </a>
                                        <!-- Edit -->
                                        <a class="btn btn-primary btn-sm" href="{{ route('purchase.edit', $purchase->id) }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <!-- Delete -->
                                        <button class="btn btn-danger btn-sm" onclick="deleteTag({{ $purchase->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
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

    <!-- Modal -->
    <div class="modal fade" id="viewPurchaseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body" id="purchase-details"></div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('js')
<script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
<script type="text/javascript">$('#sampleTable').DataTable();</script>
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<script>
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
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: false,
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                event.preventDefault();
                document.getElementById('delete-form-'+id).submit();
            } else if (
                // Read more about handling dismissals
                result.dismiss === swal.DismissReason.cancel
            ) {
                swal(
                    'Cancelled',
                    'Your data is safe :)',
                    'error'
                )
            }
        })
    }
    $(document).on("click", ".view-btn", function () {
        let id = $(this).data("id");
        $.get("purchase/" + id + "/details", function (data) {
            $("#purchase-details").html(data);
            $("#viewPurchaseModal").modal("show");
        });
    });
</script>
@endpush
