<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\ProductSupplier;
use App\Supplier;
use App\SupplierItem;
use App\Tax;
use App\Unit;
use App\ProductAdjustments;
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
            'supplier_product_code' => 'required|unique:products,supplier_product_code',
            'product_name' => 'required|string|min:3|max:255',
            // 'serial_number' => 'required',
            // 'model' => 'required',
            'category_id' => 'required',
            'sales_price' => 'required|numeric',
            'unit_id' => 'required',
            'quantity' => 'required|integer|min:0',
            'remaining_stock' => 'nullable|numeric|min:0',
            'supplier_id' => 'required|array',  // must be array
            'supplier_id.*' => 'exists:suppliers,id',
            'supplier_price' => 'required|array',
            'supplier_price.*' => 'numeric|min:0',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'tax_id' => 'nullable|integer',
            // 'adjustment' => 'nullable|integer|min:0', // new adjustment input
            // 'adjustment_status' => 'nullable|in:Return,Others',
            // 'remarks' => 'nullable|string|max:500',
        ]);

        $product = new Product();
        $product->product_code = $request->product_code;
        $product->supplier_product_code = $request->supplier_product_code;
        $product->product_name = $request->product_name;
        $product->serial_number = $request->serial_number;
        $product->model = $request->model;
        $product->category_id = $request->category_id;
        $product->sales_price = $request->sales_price;
        $product->unit_id = $request->unit_id;
        $product->quantity = $request->quantity;
        $product->remaining_stock = $request->remaining_stock ?? $request->quantity;
        $product->tax_id = $request->tax_id;
        $product->threshold = 0;

        // Set stock status
        if ($product->quantity <= 0) {
            $product->status = 'Out of Stock';
        } elseif ($product->quantity <= $product->threshold) {
            $product->status = 'Low Stock';
        } else {
            $product->status = 'In Stock';
        }

        // Handle image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/product/'), $imageName);
            $product->image = $imageName;
        }

        $product->save();

        // Attach suppliers
        $syncData = [];
        foreach ($request->supplier_id as $key => $supplierId) {
            $syncData[$supplierId] = ['price' => $request->supplier_price[$key]];
        }
        $product->suppliers()->sync($syncData);

        //Insert into adjustments if adjustment exists
        if ($request->has('adjustment')) {
            foreach ($request->adjustment as $index => $adjustment) {
                // Skip if adjustment is empty or zero
                if (empty($adjustment) || $adjustment <= 0) continue;

                $status = $request->adjustment_status[$index] ?? 'Others';
                $remarks = $request->adjustment_remarks[$index] ?? '';
                $newQty = $adjustment + $product->remaining_stock;

                \DB::table('product_adjustments')->insert([
                    'product_id' => $product->id,
                    'adjustment' => $adjustment,
                    'adjustment_status' => $status,
                    'remarks' => $remarks,
                    'new_initial_qty' => $newQty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update product remaining stock
                $product->remaining_stock += $adjustment;
                $product->save();
            }
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
        $product = Product::with(['category','suppliers','unit','tax'])->findOrFail($id);

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
        $product = Product::with([
            'productSuppliers.supplier',
            'category',
            'unit',
            'tax',
            'adjustments'
        ])->findOrFail($id);

        $categories = Category::all();
        $units = Unit::all();
        $suppliers = Supplier::all();
        $taxes = Tax::all();

        return view('product.edit', compact('product', 'categories', 'units', 'suppliers', 'taxes'));
    }

    public function getProductDetails(Request $request)
    {
        $product = Product::with(['productSuppliers.supplier', 'unit', 'category', 'tax'])
            ->where('product_code', $request->product_code)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'product_name' => 'required',
            'supplier_product_code' => 'required',
            'category_id' => 'required',
            'sales_price' => 'required|numeric',
            'supplier_id' => 'required|array',
            'supplier_id.*' => 'required|integer|exists:suppliers,id',
            'supplier_price' => 'required|array',
            'supplier_price.*' => 'required|numeric|min:0',
            'adjustment' => 'nullable|array',
            'adjustment.*' => 'nullable|integer|min:0',
            'adjustment_status' => 'nullable|array',
            'adjustment_status.*' => 'nullable|in:Return,Others',
            'adjustment_remarks' => 'nullable|array',
            'adjustment_remarks.*' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($id);

        // Update basic product fields
        $threshold = $request->quantity <= 10 ? 1 : floor($request->quantity * 0.2);
        $status = $request->quantity == 0 
                    ? 'Out of Stock' 
                    : ($request->quantity <= $threshold ? 'Low Stock' : 'In Stock');

        $product->update([
            'serial_number' => $request->serial_number,
            'supplier_product_code' => $request->supplier_product_code,
            'product_name' => $request->product_name,
            'category_id' => $request->category_id,
            'model' => $request->model,
            'quantity' => $request->quantity,
            'remaining_stock' => $request->quantity, // will adjust later based on adjustments
            'sales_price' => $request->sales_price,
            'unit_id' => $request->unit_id,
            'tax_id' => $request->tax_id,
            'threshold' => $threshold,
            'status' => $status,
        ]);

        // Handle image update if uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/product'), $filename);
            $product->image = $filename;
            $product->save();
        }

        // Delete old supplier records and recreate
        ProductSupplier::where('product_id', $product->id)->delete();
        foreach ($request->supplier_id as $index => $supplierId) {
            if (!empty($supplierId) && !empty($request->supplier_price[$index])) {
                ProductSupplier::create([
                    'product_id' => $product->id,
                    'supplier_id' => $supplierId,
                    'price' => $request->supplier_price[$index],
                ]);
            }
        }

        // Delete old adjustments
        \DB::table('product_adjustments')->where('product_id', $product->id)->delete();

        // Insert new adjustments and update remaining stock
        $totalAdjustment = 0;
        if ($request->filled('adjustment')) {
            foreach ($request->adjustment as $index => $adjValue) {
                $adjValue = (int) $adjValue;
                if ($adjValue !== 0) {
                    \DB::table('product_adjustments')->insert([
                        'product_id' => $product->id,
                        'adjustment' => $adjValue,
                        'adjustment_status' => $request->adjustment_status[$index] ?? 'Others',
                        'remarks' => $request->adjustment_remarks[$index] ?? null,
                        'new_initial_qty' => $request->new_initial_qty[$index] ?? ($product->remaining_stock + $adjValue),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $totalAdjustment += $adjValue;
                }
            }
        }

        // Update remaining stock with total adjustments
        $product->remaining_stock += $totalAdjustment;
        $product->save();

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
        $product = Product::findOrFail($id);
        $isUsedInvoiceSales = $product->product()->exists();

        if ($isUsedInvoiceSales) {
            return redirect()->back()->with('error', 'Cannot delete this product because it is used by invoices.');
        }

        // Delete the product
        $product->delete();

        return redirect()->back()->with('message', 'Product and its suppliers deleted successfully.');
    }

    public function search(Request $request)
    {
        $term = $request->get('term');
        $products = Product::where('product_name', 'like', "%{$term}%")
                    ->orWhere('product_code', 'like', "%{$term}%")
                    ->limit(10)
                    ->get();

        return response()->json($products);
    }
}
