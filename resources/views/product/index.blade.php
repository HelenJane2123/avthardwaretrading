

@extends('layouts.master')

@section('titel', 'Product | ')
@section('content')
    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1><i class="fa fa-th-list"></i> Product Table</h1>
            </div>
            <ul class="app-breadcrumb breadcrumb side">
                <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
                <li class="breadcrumb-item">Product</li>
                <li class="breadcrumb-item active"><a href="#">Product Table</a></li>
            </ul>
        </div>
        <div class="">
            <a class="btn btn-primary" href="{{route('product.create')}}"><i class="fa fa-plus"></i> Add Product</a>
        </div>

        <div class="row mt-2">
            <div class="col-md-12">
                <div class="tile">
                    <div class="tile-body">
                        <div class="d-flex justify-content-end mb-3">
                            <a href="{{ route('export.products') }}" class="btn btn-success mb-2">
                                <i class="fa fa-download"></i> Export to Excel
                            </a>
                        </div>  
                        <table class="table table-hover table-bordered" id="sampleTable">
                            <thead>
                            <tr>
                                <th>Category </th>
                                <th>Product </th>
                                <th>Model </th>
                                <th>Serial</th>
                                <th>Discount</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Sales Price</th>
                                <th>Supplier</th>
                                <th>Purchase Price</th>
                                <th>Threshold</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                             <tbody>

                             @foreach($additional as $add)
                                 <tr>
                                     <td>{{$add->product->category->name}}</td>
                                     <td>{{$add->product->name}}</td>
                                     <td>{{$add->product->model}}</td>
                                     <td>{{$add->product->serial_number}}</td>
                                     <td>{{ $add->product?->tax?->name ?? 'No Discount' }}</td>
                                     <td>{{$add->product->quantity}}</td>
                                     <td>{{$add->product->unit->name}}</td>
                                     <td>{{$add->product->sales_price}}</td>
                                     <td>
                                        <a href="{{ route('supplier.supplier-products', $add->supplier->id) }}">
                                            {{ $add->supplier->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('supplier.supplier-products', $add->supplier->id) }}">
                                            {{ $add->price }}
                                        </a>
                                    </td>
                                    <td>{{$add->product->threshold}}</td>
                                    <td>
                                        @if ($add->product->status === 'In Stock')
                                            <span class="badge badge-success">{{ $add->product->status }}</span>
                                        @elseif ($add->product->status === 'Low Stock')
                                            <span class="badge badge-warning">{{ $add->product->status }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ $add->product->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-primary btn-sm" href="{{ route('product.edit', $add->product->id) }}"><i class="fa fa-edit" ></i></a>
                                        <button class="btn btn-danger btn-sm waves-effect" type="submit" onclick="deleteTag({{ $add->product->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $add->product->id }}" action="{{ route('product.destroy',$add->product->id) }}" method="POST" style="display: none;">
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
    </script>
@endpush
