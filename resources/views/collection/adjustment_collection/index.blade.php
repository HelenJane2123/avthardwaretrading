@extends('layouts.master')

@section('title', 'Adjustment Collection | AVT Hardware')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-exchange"></i> Adjustment Collection Records</h1>
                <p class="text-muted mb-0">Review, update, or delete collection adjustment entries.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Collection Adjustment</li>
                <li class="breadcrumb-item active"><a href="#">Adjustment Records</a></li>
            </ul>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a class="btn btn-primary" href="{{ route('adjustment_collection.create') }}">
                <i class="fa fa-plus"></i> New Adjustment Entry
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
                    <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Adjustment Collection Table</h3>
                    <div class="tile-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="adjustmentTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Adjustment #</th>
                                        <th>Invoice #</th>
                                        <th>Debit/Credit</th>
                                        <th>Collection Date Adjustment</th>
                                        <th>Account Name</th>
                                        <th>Adjusted Amount</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adjustments as $adjustment)
                                        <tr id="row-{{ $adjustment->id }}">
                                            <td><span class="badge badge-info">{{ $adjustment->adjustment_no }}</span></td>
                                            <td>{{ $adjustment->invoice_no }}</td>
                                            <td>
                                                <span class="badge 
                                                    {{ $adjustment->entry_type === 'Debit' ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $adjustment->entry_type }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($adjustment->collection_date_adjustment)->format('M d, Y') }}</td>
                                            <td>{{ $adjustment->account_name }}</td>
                                            <td>{{ $adjustment->amount }}</td>
                                            <td>{{ $adjustment->remarks ?? '-' }}</td>
                                            <td>
                                                <a href="{{ route('adjustment_collection.edit', $adjustment->id) }}" class="btn btn-info btn-sm">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <button class="btn btn-danger btn-sm" type="button" 
                                                        onclick="deleteTag('{{ $adjustment->id }}','{{ $adjustment->adjustment_no }}')">
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
        </div>
    </main>
@endsection

@push('js')
    <script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script type="text/javascript">
        $('#adjustmentTable').DataTable();

        function deleteTag(adjustmentId, adjustmentNumber) {
            Swal.fire({
                title: 'Delete "' + adjustmentNumber + '"?',
                text: "This action cannot be undone!",
                type: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(function(result) {
                if (result.value) { 
                    fetch('{{ route("adjustment_collection.destroy", ":id") }}'.replace(':id', adjustmentId), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire({
                            title: data.status === 'success' ? 'Deleted!' : 'Error!',
                            text: data.message,
                            type: data.status === 'success' ? 'success' : 'error'
                        }).then(function() {
                            if (data.status === 'success') {
                                // Remove row dynamically
                                var row = document.getElementById('row-' + salesmanId);
                                if (row) row.remove();
                            }
                        });
                    })
                    .catch(function() {
                        Swal.fire('Error', 'Something went wrong!', 'error');
                    });
                }
            });
        }
    </script>
@endpush