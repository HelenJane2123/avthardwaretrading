<div class="row">
    <!-- Left Side: Product Image -->
    <div class="col-md-4 text-center">
        @if($product->image)
            <img src="{{ asset('images/product/' . $product->image) }}" 
                 alt="{{ $product->product_name }}" 
                 class="img-fluid rounded shadow-sm mb-3" 
                 style="max-height: 250px;">
        @else
            <img src="{{ asset('images/no-image.png') }}" 
                 alt="No image available" 
                 class="img-fluid rounded shadow-sm mb-3" 
                 style="max-height: 250px;">
        @endif
    </div>

    <!-- Right Side: Product Details -->
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Product Code:</strong> {{ $product->product_code }}</p>
                <p><strong>Supplier Item Code:</strong> {{ $product->supplier_product_code }}</p>
                <p><strong>Product:</strong> {{ $product->product_name }}</p>
                <p><strong>Description:</strong> {{ $product->description }}</p>
                <p><strong>Model:</strong> {{ $product->model ?? 'N/A' }}</p>
                <p><strong>Serial:</strong> {{ $product->serial_number ?? 'N/A'}}</p>
                <p><strong>Discount:</strong> {{ $product->tax->name ?? 'No Discount' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Category:</strong> {{ $product->category->name }}</p>
                <p><strong>Unit:</strong> {{ $product->unit->name }}</p>
                <p><strong>Quantity:</strong> {{ $product->quantity }}</p>
                <p><strong>Quantity on Hand:</strong> {{ $product->remaining_stock }}</p>
                <p><strong>Sales Price:</strong> {{ $product->sales_price }}</p>
                <p><strong>Threshold:</strong> {{ $product->threshold }}</p>
                <p><strong>Status:</strong> {{ $product->status }}</p>
                <p><strong>Volume Less:</strong> {{ $product->volume_less ?? 'N/A' }}</p>
                <p><strong>Regular Less:</strong> {{ $product->regular_less ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>

<hr>

<!-- Suppliers Section -->
<h5>Suppliers</h5>
<ul>
    @foreach($product->suppliers as $supplier)
        <li>{{ $supplier->name }} - Supplier Price: {{ $supplier->pivot->price }}</li>
    @endforeach
</ul>
