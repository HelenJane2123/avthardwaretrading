@extends('layouts.master')

@section('titel', 'Mode of Payment | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Manage Mode of Payment</h1>
                <p class="text-muted mb-0">View, update, or delete existing payment methods to keep your transactions organized.</p>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Mode of Payment</li>
                <li class="breadcrumb-item active"><a href="#">Manage Mode of Payment</a></li>
            </ul>
        </div>
        <div class="">
            <a class="btn btn-sm btn-primary" href="{{route('modeofpayment.create')}}"><i class="fa fa-plus"></i> Mode of Payment</a>
        </div>
        @if(session()->has('message'))
            <div class="alert alert-success mt-2">
                {{ session()->get('message') }}
            </div>
        @endif
        @if (session()->has('error'))
             <div class="alert alert-danger mt-2">
                {{ session()->get('error') }}
            </div>
        @endif
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                        <h3 class="tile-title mb-3"><i class="fa fa-table"></i> Mode of Payment Records</h3>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered" id="modeofpaymentTable">
                                <thead class="thead-dark medium">
                                    <tr>
                                        <th> ID </th>
                                        <th> Name </th>
                                        <th> Description </th>
                                        <th> Term </th>
                                        <th>Date Created</th>
                                        <th>Date Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="medium">
                                    @foreach( $modeofpayments as $modeofpayment)
                                        <tr id="row-{{ $modeofpayment->id }}">
                                            <td>{{ $modeofpayment->id }} </td>
                                            <td>{{ $modeofpayment->name }} </td>
                                            <td>{{ $modeofpayment->description }} </td>
                                            <td>{{ $modeofpayment->term }} </td>
                                            <td>{{ $modeofpayment->created_at }} </td>
                                            <td>{{ $modeofpayment->updated_at }} </td>
                                            <td>
                                                <a class="btn btn-primary btn-sm" href="{{route('modeofpayment.edit', $modeofpayment->id)}}"><i class="fa fa-edit" ></i></a>
                                                <button class="btn btn-danger btn-sm" type="button" 
                                                        onclick="deleteTag('{{ $modeofpayment->id }}', '{{ $modeofpayment->name }}','{{ $modeofpayment->term }}')">
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
    <script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">$('#modeofpaymentTable').DataTable();</script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
    <script type="text/javascript">
        function deleteTag(modeofpaymentId, name, term) {
            Swal.fire({
                title: 'Delete "' + name + ' (' + term + ')?',
                text: "This action cannot be undone!",
                type: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then(function(result) {
                if (result.value) { 
                    fetch('{{ route("modeofpayment.destroy", ":id") }}'.replace(':id', modeofpaymentId), {
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
                                var row = document.getElementById('row-' + productId);
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
