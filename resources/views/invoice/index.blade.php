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
            <a class="btn btn-primary" href="{{route('invoice.create')}}"><i class="fa fa-plus"></i> Create New Invoice</a>
            <a class="btn btn-success shadow-sm" href="{{ route('export.invoices') }}">
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </a>
        </div>

         @if(session()->has('message'))
            <div class="alert alert-success mt-2">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Invoice Records</h3>
                    <div class="tile-body">
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Invoice Date</th>
                                    <th>Due Date</th>
                                    <th>Subtotal</th>
                                    <th>Discount Type</th>
                                    <th>Grand Total</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    <tr>
                                        <td><span class="badge badge-info">{{ $invoice->invoice_number }}</span></td>
                                        <td>{{ $invoice->customer->name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</td>
                                        <td>{{ number_format($invoice->subtotal, 2) }}</td>
                                        <td>
                                           @if($invoice->discount_type == 'per_item')
                                                <span class="badge badge-success">Per Item</span>
                                            @elseif($invoice->discount_value > 0)
                                                <span class="badge badge-primary">
                                                    Overall - {{ $invoice->discount_value }} {{ $invoice->discount_type == 'overall' ? '%' : '₱' }}
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                   No Discount
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($invoice->grand_total, 2) }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($invoice->invoice_status == 'approved') bg-success
                                                @elseif($invoice->invoice_status == 'pending') bg-warning
                                                @elseif($invoice->invoice_status == 'canceled') bg-danger
                                                @endif">
                                                {{ ucfirst($invoice->invoice_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($invoice->payment_status == 'paid') bg-success
                                                @elseif($invoice->payment_status == 'pending') bg-warning
                                                @elseif($invoice->payment_status == 'overdue') bg-danger
                                                @elseif($invoice->payment_status == 'partial') bg-info
                                                @endif">
                                                {{ ucfirst($invoice->payment_status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm view-invoice" data-id="{{ $invoice->id }}"><i class="fa fa-eye"></i></button>
                                            <a class="btn btn-info btn-sm" href="{{ route('invoice.edit', $invoice->id) }}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            @if($invoice->invoice_status == 'approved')
                                                <a class="btn btn-secondary btn-sm" href="{{ route('invoice.print', $invoice->id) }}" target="_blank">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                            @endif
                                            <button class="btn btn-danger btn-sm" onclick="deleteTag({{ $invoice->id }})">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $invoice->id }}" action="{{ route('invoice.destroy',$invoice->id) }}" method="POST" style="display: none;">
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
    </main>

    <!-- Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="invoiceModalLabel">Invoice Details</h5>
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

        // Show modal with invoice details
        $(document).on('click', '.view-invoice', function () {
            let id = $(this).data('id');
            $.get("{{ url('invoice') }}/" + id, function (data) {
                $('#invoiceDetails').html(data);
                $('#invoiceModal').modal('show');
            });
        });
    </script>
@endpush
