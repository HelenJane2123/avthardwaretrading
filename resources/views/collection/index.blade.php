@extends('layouts.master')

@section('titel', 'Collection | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')
    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Collection Table</h1>
                <p class="text-muted mb-0">View, update, or delete existing collection.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Collection</li>
                <li class="breadcrumb-item active"><a href="#">Collection Table</a></li>
            </ul>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a class="btn btn-primary" href="{{route('collection.create')}}"><i class="fa fa-plus"></i> Create New Collection</a>
            <a class="btn btn-success shadow-sm" href="{{ route('export.collections') }}">
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
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                    <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Collection Records</h3>
                    <div class="tile-body">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                                <tr>
                                    <th>Collection #</th>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Payment Date</th>
                                    <th>Invoice Amount</th>
                                    <th>Amount Paid</th>
                                    <th>Outstanding Balance</th>
                                    <th>Payment Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($collections as $collection)
                                    <tr>
                                        <td><span class="badge badge-info">{{ $collection->collection_number }}</span></td>
                                        <td>{{ $collection->invoice->invoice_number }}</td>
                                        <td>{{ $collection->invoice->customer->name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($collection->payment_date)->format('M d, Y') }}</td>
                                        <td>{{ number_format($collection->invoice->grand_total, 2) }}</td>
                                        <td>{{ number_format($collection->amount_paid, 2) }}</td>
                                        <td>{{ number_format($collection->invoice->outstanding_balance, 2) }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($collection->invoice->payment_status == 'paid') bg-success
                                                @elseif($collection->invoice->payment_status == 'partial') bg-warning
                                                @elseif($collection->invoice->payment_status == 'pending') bg-info
                                                @endif">
                                                {{ ucfirst($collection->invoice->payment_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm view-collection" data-id="{{ $collection->invoice->id }}">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <a class="btn btn-info btn-sm" href="{{ route('collection.edit', $collection->id) }}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            @if($collection->invoice->invoice_status == 'approved')
                                                <a class="btn btn-secondary btn-sm" href="{{ route('collection.receipt', $collection->id) }}" target="_blank">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                            @endif

                                            <!-- Delete Button -->
                                            <form action="{{ route('collection.destroy', $collection->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm btn-delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
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
    </main>

    <!-- Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="invoiceModalLabel">Collection Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Modal Body -->
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
    <script type="text/javascript">$('#sampleTable').DataTable();</script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.view-collection').on('click', function() {
                var invoiceId = $(this).data('id');
                
                $.ajax({
                    url: '/collection/' + invoiceId + '/details', // Route to get details
                    type: 'GET',
                    success: function(data) {
                        $('#invoiceDetails').html(data);
                        $('#invoiceModal').modal('show');
                    },
                    error: function(xhr) {
                        alert('Unable to fetch details.');
                    }
                });
            });

             // Delete confirmation
            $('.btn-delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone.",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
