@extends('layouts.master')

@section('titel', 'Supplier Products | ')

@section('content')
@include('partials.header')
@include('partials.sidebar')

<main class="app-content">

    <div class="app-title d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fa fa-building text-primary"></i> Supplier: <strong>{{ $supplier->name }}</strong></h1>
            <p class="text-muted mb-0">Here is the list of all items supplied by <strong>{{ $supplier->name }}</strong>.</p>
        </div>
        <div>
            <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('supplier.supplier-products.export', $supplier->id) }}" class="btn btn-success">
                <i class="fa fa-file-excel-o"></i> Export to Excel
            </a>
        </div>
    </div>

    <div class="tile mb-4">
        <div class="tile-body">
            <h5 class="text-dark mb-3">Supplier Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Code:</strong> {{ $supplier->supplier_code }}</p>
                    <p><strong>Address:</strong> {{ $supplier->address }}</p>
                    <p><strong>Contact:</strong> {{ $supplier->mobile }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Email:</strong> {{ $supplier->email }}</p>
                    <p><strong>Tax:</strong> {{ $supplier->tax }}</p>
                    <p><strong>Details:</strong> {{ $supplier->details }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="tile">
        <div class="tile-body">
            <h5 class="text-dark mb-3">Product List</h5>
            <div class="table-responsive">
               <table class="table table-hover table-bordered" id="productsTable">
                    <thead class="thead-light">
                        <tr class="bg-light">
                            <th>Image</th>
                            <th>Item Code</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Volume Less</th>
                            <th>Regular Less</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier->items as $item)
                            <tr>
                                <td>
                                    @if($item->item_image)
                                        <img src="{{ asset('storage/' . $item->item_image) }}" width="60" class="img-thumbnail">
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $item->item_code }}</td>
                                <td>{{ $item->category->name ?? 'N/A' }}</td>
                                <td>{{ $item->item_description }}</td>
                                <td>{{ $item->unit->name ?? 'N/A' }}</td>
                                <td>{{ $item->item_qty }}</td>
                                <td>₱{{ number_format($item->item_price, 2) }}</td>
                                <td>{{ $item->volume_less }}</td>
                                <td>{{ $item->regular_less }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No products available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($supplier->items->isNotEmpty())
                    <!-- <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="5" class="text-end">TOTAL</td>
                            <td>
                                {{ $supplier->items->sum('item_qty') }}
                            </td>
                            <td>
                                ₱{{ number_format($supplier->items->sum('item_price'), 2) }}
                            </td>
                            <td>
                                ₱{{ number_format($supplier->items->sum('item_amount'), 2) }}
                            </td>
                        </tr>
                    </tfoot> -->
                    @endif
                </table>
            </div>
        </div>
    </div>

</main>
@endsection
@push('js')
    <script type="text/javascript" src="{{asset('/')}}js/plugins/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{asset('/')}}js/plugins/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">$('#productsTable').DataTable();</script>
    <script src="https://unpkg.com/sweetalert2@7.19.1/dist/sweetalert2.all.js"></script>
@endpush
