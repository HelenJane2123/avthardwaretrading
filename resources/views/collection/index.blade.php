@extends('layouts.master')

@section('title', 'Collections | ')

@section('content')

@include('partials.header')
@include('partials.sidebar')

<main class="app-content">
    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-money"></i> Collections</h1>
            <p class="text-muted mb-0">Manage customer payments and collections.</p>
        </div>
        <ul class="app-breadcrumb breadcrumb">
            <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
            <li class="app-breadcrumb-item active">Collections</li>
        </ul>
    </div>

    <div class="mb-3">
        <a href="{{ route('collection.create') }}" class="btn btn-sm btn-primary">
            <i class="fa fa-plus"></i> Add Collection
        </a>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success">{{ session()->get('message') }}</div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="tile shadow-sm">
                <h3 class="tile-title"><i class="fa fa-money"></i> Collection List</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="collectionTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Collection #</th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Payment Date</th>
                                <th>Payment Method</th>
                                <th class="text-right">Amount Paid</th>
                                <th class="text-right">Balance</th>
                                <th>Status</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($collections as $collection)
                                <tr>
                                    <td>{{ $collection->collection_number }}</td>
                                    <td>{{ $collection->invoice->invoice_number ?? '-' }}</td>
                                    <td>{{ $collection->invoice->customer->name ?? '-' }}</td>
                                    <td>{{ date('M d, Y', strtotime($collection->payment_date)) }}</td>
                                    <td>{{ $collection->invoice->paymentMode->name ?? '-' }}</td>
                                    <td class="text-right">₱{{ number_format($collection->amount_paid, 2) }}</td>
                                    <td class="text-right">₱{{ number_format($collection->invoice->outstanding_balance ?? 0, 2) }}</td>
                                    <td>
                                        @if($collection->invoice->outstanding_balance <= 0)
                                            <span class="badge badge-success">Paid</span>
                                        @else
                                            <span class="badge badge-warning">Outstanding</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('collection.details', $collection->id) }}" class="btn btn-sm btn-primary" title="View History">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('collection.receipt', $collection->id) }}" class="btn btn-sm btn-secondary" target="_blank" title="Print Receipt">
                                            <i class="fa fa-print"></i>
                                        </a>
                                        <a href="{{ route('collection.edit', $collection->id) }}" class="btn btn-sm btn-info" title="Edit Collection">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger deleteCollection" data-id="{{ $collection->id }}" title="Delete Collection">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('js')
<script src="{{ asset('/') }}js/plugins/jquery.dataTables.min.js"></script>
<script src="{{ asset('/') }}js/plugins/dataTables.bootstrap.min.js"></script>
<script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
<script>
$(document).ready(function() {
    $('#collectionTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        responsive: true
    });

    $('.deleteCollection').click(function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Delete Collection?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/collection/delete/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire('Deleted!', response.message, 'success').then(() => location.reload());
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
