<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSupplier;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\ProductAdjustments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
        $product_code = $this->generateProductCode();
        return view('product.create', compact('product_code','categories','taxes','units','suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Get first supplier (since supplier_id is an array)
        $supplierId = $request->supplier_id[0] ?? null;

        $request->validate([
            'product_code' => 'required|unique:products,product_code',

            'supplier_product_code' => 'required|string|max:255',

            'product_name' => 'required|string|min:3|max:255',
            'category_id' => 'required',
            'sales_price' => 'required|numeric',
            'unit_id' => 'required',
            'quantity' => 'required|integer|min:0',
            'remaining_stock' => 'nullable|numeric|min:0',

            'supplier_id' => 'required|array',
            'supplier_id.*' => 'exists:suppliers,id',

            'supplier_price' => 'nullable|array',
            'supplier_price.*' => 'nullable|numeric|min:0',

            'net_price' => 'nullable|array',
            'net_price.*' => 'nullable|numeric|min:0',

            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            'discount_type' => 'nullable',
            'discount_1' => 'nullable|integer',
            'discount_2' => 'nullable|integer',
            'discount_3' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            // -----------------------------
            // CREATE PRODUCT
            // -----------------------------
            $product = new Product();
            $product->product_code = $this->generateProductCode();
            $product->supplier_product_code = $request->supplier_product_code;
            $product->product_name = $request->product_name;
            $product->description = $request->description;
            $product->serial_number = $request->serial_number;
            $product->model = $request->model;
            $product->category_id = $request->category_id;
            $product->sales_price = $request->sales_price;
            $product->unit_id = $request->unit_id;
            $product->quantity = $request->quantity;
            $product->remaining_stock = $request->remaining_stock ?? $request->quantity;
            $product->discount_type = $request->discount_type;
            $product->discount_1 = $request->discount_1;
            $product->discount_2 = $request->discount_2;
            $product->discount_3 = $request->discount_3;
            $product->threshold = 0;
            $product->volume_less = $request->volume_less;
            $product->regular_less = $request->regular_less;

            // Stock status
            if ($product->remaining_stock <= 0) {
                $product->status = 'Out of Stock';
            } elseif ($product->remaining_stock <= $product->threshold) {
                $product->status = 'Low Stock';
            } else {
                $product->status = 'In Stock';
            }

            // -----------------------------
            // IMAGE UPLOAD
            // -----------------------------
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/product/'), $imageName);
                $product->image = $imageName;
            }

            $product->save();

            // -----------------------------
            // ATTACH SUPPLIERS
            // -----------------------------
            $syncData = [];

            foreach ($request->supplier_id as $key => $supplierId) {
                $syncData[$supplierId] = [
                    'price'     => $request->supplier_price[$key] ?? 0,
                    'net_price' => $request->net_price[$key] ?? 0,
                ];
            }

            $product->suppliers()->sync($syncData);

            DB::commit();

            return redirect()
                ->route('product.index')
                ->with('message', 'New product has been added successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Product store failed: ' . $e->getMessage());

            return back()
                ->withErrors(['error' => 'Failed to add product. Please try again.'])
                ->withInput();
        }
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

        foreach ($product->productSuppliers as $ps) {
            $ps->supplierItem = SupplierItem::where('supplier_id', $ps->supplier_id)
                ->where('item_code', $product->supplier_product_code)
                ->first();
        }

        $categories = Category::all();
        $units = Unit::all();
        $suppliers = Supplier::all();
        $taxes = Tax::all();

        return view('product.edit', compact(
            'product',
            'categories',
            'units',
            'suppliers',
            'taxes'
        ));
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
        //dd('Update function hit!', $id, $request->all());
        // $request->validate([
        //     'product_name' => 'required',
        //     'supplier_product_code' => 'required',
        //     'category_id' => 'required',
        //     'sales_price' => 'required|numeric',
        //     'supplier_id' => 'required|array',
        //     'supplier_id.*' => 'required|integer|exists:suppliers,id',
        //     'supplier_price' => 'required|array',
        //     'supplier_price.*' => 'required|numeric|min:0',
        //     'net_price' => 'nullable|array',
        //     'net_price.*' => 'nullable|numeric|min:0',
        //     'adjustment' => 'nullable|array',
        //     'adjustment.*' => 'nullable|integer|min:0',
        //     'adjustment_status' => 'nullable|array',
        //     'adjustment_status.*' => 'nullable|in:Return,Others',
        //     'adjustment_remarks' => 'nullable|array',
        //     'adjustment_remarks.*' => 'nullable|string|max:500',
        //     'add_quantity' => 'nullable|integer|min:0',
        // ]);
        try {
            $product = Product::findOrFail($id);

            // compute new values
            $addedQty = (int) $request->add_quantity;
            $newTotalQuantity  = $product->quantity;
            $newRemainingStock = $product->remaining_stock + $addedQty;

            $threshold = $newTotalQuantity <= 10 ? 1 : floor($newTotalQuantity * 0.2);

            $status = 'In Stock';
            if ($newRemainingStock <= 0) {
                $status = 'Out of Stock';
            } elseif ($newRemainingStock <= $threshold) {
                $status = 'Low Stock';
            }

            // update product
            $product->update([
                'serial_number' => $request->serial_number,
                'supplier_product_code' => $request->supplier_product_code,
                'product_name' => $request->product_name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'model' => $request->model,
                'quantity' => $newTotalQuantity,
                'remaining_stock' => max(0, $newRemainingStock),
                'sales_price' => $request->sales_price,
                'unit_id' => $request->unit_id,
                'discount_type' => $request->discount_type,
                'discount_1' => $request->discount_1,
                'discount_2' => $request->discount_2,
                'discount_3' => $request->discount_3,
                'threshold' => $threshold,
                'status' => $status,
                'volume_less' => $request->volume_less,
                'regular_less' => $request->regular_less,
            ]);

            // image upload
            $imageFilename = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageFilename = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/product'), $imageFilename);
                $product->update(['image' => $imageFilename]);
            }

            // update suppliers
            // ProductSupplier::where('product_id', $product->id)->delete();
            // $suppliersUpdated = [];
            // foreach ($request->supplier_id as $index => $supplierId) {
            //     if (!empty($supplierId) && isset($request->supplier_price[$index])) {
            //         $supplier = ProductSupplier::create([
            //             'product_id' => $product->id,
            //             'supplier_id' => $supplierId,
            //             'price' => $request->supplier_price[$index],
            //             'net_price' => $request->net_price[$index] ?? 0,
            //         ]);
            //         $suppliersUpdated[] = [
            //             'supplier_id' => $supplier->supplier_id,
            //             'price' => $supplier->price,
            //             'net_price' => $supplier->net_price,
            //         ];
            //     }
            // }

            // log success
            \Log::info('Product updated successfully.', [
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'updated_by_user_id' => auth()->id() ?? 'guest',
                'quantity_added' => $addedQty,
                'new_total_quantity' => $newTotalQuantity,
                'remaining_stock' => $newRemainingStock,
                'status' => $status,
                'image_uploaded' => $imageFilename,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return redirect()->route('product.index')->with('message', 'Product has been successfully updated.');

        } catch (\Exception $e) {
            // log error
            \Log::error('Product update failed.', [
                'product_id' => $id,
                'updated_by_user_id' => auth()->id() ?? 'guest',
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to update product. Please try again.');
        }
    }


    public function storeAdjustment(Request $request, $id)
    {
        $request->validate([
            'adjustment' => 'required|numeric',
            'adjustment_status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $product = Product::findOrFail($id);

        // Compute new remaining stock
        $newRemainingStock = $product->remaining_stock + $request->adjustment;

        // Save adjustment history
        $adj = $product->adjustments()->create([
            'adjustment' => $request->adjustment,
            'adjustment_status' => $request->adjustment_status,
            'remarks' => $request->remarks,
            'new_initial_qty' => $newRemainingStock, // historical snapshot
        ]);

        // Update product inventory
        $product->update([
            'remaining_stock' => $newRemainingStock,
        ]);

        return response()->json([
            'adjustment' => [
                'adjustment' => $adj->adjustment,
                'adjustment_status' => $adj->adjustment_status,
                'remarks' => $adj->remarks,
                'new_initial_qty' => $newRemainingStock,
                'created_at' => $adj->created_at->format('M d, Y H:i'),
            ],
        ]);
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

    public function list()
    {
        $products = SupplierItem::select('id', 'item_code', 'item_description')
                    // ->take(10) // only get 10 records
                    ->get();

        return response()->json($products);
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
                'supplier_items.item_price',
                'supplier_items.net_price',
                'supplier_items.discount_less_add',
                'supplier_items.discount_1',
                'supplier_items.discount_2',
                'supplier_items.discount_3'
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

        // Check if product exists in any invoice sales
        if ($product->sales()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete this product because it is used by invoices.'
            ]);
        }

        // Delete the product
        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully.'
        ]);
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

    private function generateProductCode() {
        $prefix = "AVT";

        $lastProduct = Product::orderBy('id', 'desc')->first();

        if (!$lastProduct) {
            $nextNumber = 1;
        } else {
            $lastNumber = (int) str_replace($prefix, '', $lastProduct->product_code);
            $nextNumber = $lastNumber + 1;
        }

        $paddedNumber = str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        return $prefix . $paddedNumber;
    }
}
