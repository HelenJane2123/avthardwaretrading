<div class="row">
    <div class="col-md-6">
        <p><strong>Product Code:</strong> {{ $product->product_code }}</p>
        <p><strong>Supplier Item Code:</strong> {{ $product->supplier_product_code }}</p>
        <p><strong>Product:</strong> {{ $product->name }}</p>
        <p><strong>Model:</strong> {{ $product->model }}</p>
        <p><strong>Serial:</strong> {{ $product->serial_number }}</p>
    </div>
    <div class="col-md-6">
        <p><strong>Category:</strong> {{ $product->category->name }}</p>
        <p><strong>Unit:</strong> {{ $product->unit->name }}</p>
        <p><strong>Quantity:</strong> {{ $product->quantity }}</p>
        <p><strong>Threshold:</strong> {{ $product->threshold }}</p>
        <p><strong>Status:</strong> {{ $product->status }}</p>
    </div>
</div>
<hr>
<h5>Suppliers</h5>
<ul>
    @foreach($product->supplierItems as $item)
        <li>{{ $item->supplier->name }} - {{ $item->price }}</li>
    @endforeach
</ul>
