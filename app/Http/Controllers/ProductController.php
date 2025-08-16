<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\ProductSupplier;
use App\Supplier;
use App\SupplierItem;
use App\Tax;
use App\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $additional = ProductSupplier::with([
            'product.category',
            'product.unit',
            'product.tax',
            'supplier'
        ])
        ->whereHas('product')
        ->whereHas('supplier')
        ->get();

        return view('product.index', compact('additional'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers =Supplier::all();
        $categories = Category::all();
        $taxes = Tax::all();
        $units = Unit::all();

        return view('product.create', compact('categories','taxes','units','suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'product_code' => 'required|unique:products,product_code',
            'name' => 'required|string|min:3|max:255|unique:products',
            'serial_number' => 'required',
            'model' => 'required',
            'category_id' => 'required',
            'sales_price' => 'required',
            'unit_id' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tax_id' => 'nullable|integer',
            'quantity' => 'required',
            'remaining_stock' => 'nullable|numeric|min:0'        
        ]);

        $product = new Product();
        $product->product_code = $request->product_code;
        $product->supplier_product_code = $request->supplier_product_code;
        $product->name = $request->product_name;
        $product->serial_number = $request->serial_number;
        $product->model = $request->model;
        $product->category_id = $request->category_id;
        $product->sales_price = $request->sales_price;
        $product->unit_id = $request->unit_id;
        $product->quantity = $request->quantity;
        $product->remaining_stock = $request->remaining_stock;
        $product->tax_id = $request->tax_id;
        $product->threshold = 0;

        // Determine stock status
        if ($product->quantity <= 0) {
            $product->status = 'Out of Stock';
        } elseif ($product->quantity <= $product->threshold) {
            $product->status = 'Low Stock';
        } else {
            $product->status = 'In Stock';
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();        
            $image->move(public_path('images/product/'), $imageName);
            $product->image = $imageName;
        }

        $product->save();

        foreach($request->supplier_id as $key => $supplier_id){
            $supplier = new ProductSupplier();
            $supplier->product_id = $product->id;
            $supplier->supplier_id = $request->supplier_id[$key];
            $supplier->price = $request->supplier_price[$key];
            $supplier->save();
        }
        return redirect()->route('product.index')->with('message', 'New product has been added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::with(['category','supplierItems.supplier','unit','tax'])->findOrFail($id);

        return view('product.partials.view', compact('product'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::with('productSuppliers')->findOrFail($id);
        $categories = Category::all();
        $units = Unit::all();
        $suppliers = Supplier::all();
        $taxes = Tax::all();

        return view('product.edit', compact('product', 'categories', 'units', 'suppliers', 'taxes'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'sales_price' => 'required|numeric',
            'supplier_id' => 'required|array',
            'supplier_price' => 'required|array',
        ]);

        $product = Product::findOrFail($id);

        // Update basic product fields
        $product->update([
            'serial_number' => $request->serial_number,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'model' => $request->model,
            'quantity' => $request->quantity,
            'remaining_stock' => $request->quantity, // or however you manage stock
            'sales_price' => $request->sales_price,
            'unit_id' => $request->unit_id,
            'tax_id' => $request->tax_id,
            'threshold' => $request->quantity <= 10 ? 1 : floor($request->quantity * 0.2),
            'status' => $request->quantity == 0 ? 'Out of Stock' : ($request->quantity <= ($request->quantity <= 10 ? 1 : floor($request->quantity * 0.2)) ? 'Low Stock' : 'In Stock'),
        ]);

        // Handle image update if uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/product'), $filename);
            $product->image = $filename;
            $product->save();
        }

        // Delete old supplier records
        ProductSupplier::where('product_id', $product->id)->delete();

        // Recreate supplier records
        foreach ($request->supplier_id as $index => $supplierId) {
            ProductSupplier::create([
                'product_id' => $product->id,
                'supplier_id' => $supplierId,
                'price' => $request->supplier_price[$index],
            ]);
        }

        return redirect()->route('product.index')->with('message', 'Product updated successfully!');
    }

    public function getProductInfo($id)
    {
        $product = Product::with(['tax', 'unit'])->findOrFail($id);

        $status = 'In Stock';
        if ($product->remaining_stock <= $product->threshold) {
            $status = 'Low Stock';
        }
        if ($product->remaining_stock == 0) {
            $status = 'Out of Stock';
        }

        return response()->json([
            'code' => $product->product_code,
            'price' => $product->sales_price,
            'unit' => $product->unit->name ?? '',
            'tax' => $product->tax->name ?? 0,  // Make sure 'value' is the tax percentage (e.g., 12 for 12%)
            'stock'  => $product->remaining_stock ?? 0,
            'status' => $status,
        ]);
    }

    // Suggest items from supplier_items
    public function suggest(Request $request)
    {
        $query = $request->get('query');

        $items = SupplierItem::where('item_description', 'LIKE', "%{$query}%")
            ->orWhere('item_code', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'item_code', 'item_description', 'item_price', 'supplier_id']);

        return response()->json($items);
    }

    // Get suppliers based on chosen item_code
    public function suppliers(Request $request)
    {
        $itemCode = $request->get('item_code');
        $suppliers = SupplierItem::where('item_code', $itemCode)
            ->join('suppliers', 'supplier_items.supplier_id', '=', 'suppliers.id')
            ->get([
                'suppliers.id',
                'suppliers.name',
                'supplier_items.item_price'
            ]);

        return response()->json($suppliers);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }

        // Manually delete related product suppliers
        ProductSupplier::where('product_id', $id)->delete();

        // Then delete the product
        $product->delete();

        return redirect()->back()->with('message', 'Product and its suppliers deleted successfully.');
    }
}
