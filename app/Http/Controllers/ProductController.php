<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\ProductSupplier;
use App\Supplier;
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
        $product->name = $request->name;
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $productId = $id; // The specific product ID you want to retrieve associated ProductSupplier record for
        $additional = ProductSupplier::where('product_id', $productId)->first();

        $product = Product::with('suppliers.supplier')->findOrFail($id);
        $suppliers =Supplier::all();
        $categories = Category::all();
        $taxes = Tax::all();
        $units = Unit::all();
        return view('product.edit', compact('additional','suppliers','categories','taxes','units','product'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|min:3|unique:products,name,' . $id . '|regex:/^[a-zA-Z ]+$/',
            'serial_number' => 'required',
            'model' => 'required|min:3',
            'category_id' => 'required',
            'sales_price' => 'required|numeric|min:0',
            'unit_id' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tax_id' => 'required',
            'quantity' => 'required|integer|min:0',
            'supplier_id.*' => 'required|exists:suppliers,id',
            'supplier_price.*' => 'required|numeric|min:0',
        ]);

        $product = Product::find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        // Update product fields
        $product->name = $request->name;
        $product->serial_number = $request->serial_number;
        $product->model = $request->model;
        $product->category_id = $request->category_id;
        $product->sales_price = $request->sales_price;
        $product->unit_id = $request->unit_id;
        $product->tax_id = $request->tax_id;

        // Update quantity (initial_qty) and compute threshold
        $product->quantity = $request->quantity;
        //$product->qty = $request->initial_qty;

        // Compute threshold as 20% of qty or set to at least 1
        $threshold = ($product->quantity <= 10) ? 1 : floor($product->quantity * 0.2);
        $product->threshold = $threshold;

        // Determine product status based on qty and threshold
        if ($product->quantity == 0) {
            $product->status = 'Out of Stock';
        } elseif ($product->quantity <= $threshold) {
            $product->status = 'Low Stock';
        } else {
            $product->status = 'In Stock';
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $existingImagePath = public_path("images/product/{$product->image}");
            if (file_exists($existingImagePath) && is_file($existingImagePath)) {
                unlink($existingImagePath);
            }

            $imageName = time() . '_' . uniqid() . '.' . $request->image->getClientOriginalExtension();
            $request->image->move(public_path('images/product/'), $imageName);
            $product->image = $imageName;
        }

        $product->save();

       // Remove old suppliers
        ProductSupplier::where('product_id', $product->id)->delete();

        // Re-add suppliers from the form
        foreach ($request->supplier_id as $key => $supplier_id) {
            ProductSupplier::create([
                'product_id' => $product->id,
                'supplier_id' => $supplier_id,
                'price' => $request->supplier_price[$key],
            ]);
        }

        return redirect()->route('product.index')->with('message', 'Product has been updated successfully');
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
