<div class="product-details" style="font-size: 17px;">
    <div class="row mb-4">
        <!-- Left Side: Product Image -->
        <div class="col-md-4 text-center mb-3">
            @if($product->image)
                <img src="{{ asset('images/product/' . $product->image) }}" 
                     alt="{{ $product->product_name }}" 
                     class="img-fluid rounded shadow-sm" 
                     style="max-height: 250px;">
            @else
                <div class="d-flex justify-content-center align-items-center bg-light rounded shadow-sm" 
                     style="height: 250px;">
                    <i class="fa fa-image fa-4x text-muted"></i>
                </div>
            @endif
        </div>

        <!-- Right Side: Product Details -->
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <p><strong>Product Code:</strong> {{ $product->product_code }}</p>
                    <p><strong>Supplier Item Code:</strong> {{ $product->supplier_product_code }}</p>
                    <p><strong>Product Name:</strong> {{ $product->product_name }}</p>
                    <p><strong>Description:</strong> {{ $product->description ?? 'N/A' }}</p>
                    <p><strong>Model:</strong> {{ $product->model ?? 'N/A' }}</p>
                    <p><strong>Serial Number:</strong> {{ $product->serial_number ?? 'N/A' }}</p>
                    <p><strong>Discount / Tax:</strong> {{ $product->tax->name ?? 'No Discount' }}</p>
                    <p><strong>Status:</strong>
                       @if ($product->status === 'In Stock' && $product->remaining_stock > 0)
                            <span class="badge badge-success">{{ $product->status }}</span>
                        @elseif ($product->status === 'Low Stock')
                            <span class="badge badge-warning">{{ $product->status }}</span>
                        @elseif ($product->remaining_stock <= 0 || $product->remaining_stock === null)
                            <span class="badge badge-danger">{{ $product->status }}</span>
                        @else
                            <span class="badge badge-secondary">{{ $product->status }}</span>
                        @endif
                    </p>
                </div>

                <div class="col-md-6 mb-3">
                    <p><strong>Category:</strong> {{ $product->category->name ?? 'N/A' }}</p>
                    <p><strong>Unit:</strong> {{ $product->unit->name ?? 'N/A' }}</p>
                    <p><strong>Quantity:</strong> {{ $product->quantity }}</p>
                    <p><strong>Quantity on Hand:</strong> {{ $product->remaining_stock }}</p>
                    <p><strong>Sales Price:</strong> ₱{{ number_format($product->sales_price, 2) }}</p>
                    <p><strong>Threshold:</strong> {{ $product->threshold }}</p>
                    <p><strong>Volume Less:</strong> {{ $product->volume_less ?? 'N/A' }}</p>
                    <p><strong>Regular Less:</strong> {{ $product->regular_less ?? 'N/A' }}</p>
                    <p><strong>Last Updated:</strong> 
                        {{ $product->updated_at ? $product->updated_at->format('M d, Y') : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <!-- Suppliers Section -->
    <h5>Suppliers</h5>
    @if($product->suppliers->isNotEmpty())
        <ul class="list-group list-group-flush mb-3" style="font-size: 17px;">
            @foreach($product->suppliers as $supplier)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $supplier->name }} 
                    <span class="badge bg-success">₱{{ number_format($supplier->pivot->price, 2) }}</span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-muted">No suppliers available for this product.</p>
    @endif
</div>
