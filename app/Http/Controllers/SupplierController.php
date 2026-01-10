<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Tax;
use App\Models\Product;
use App\Models\ProductSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $suppliers = Supplier::all();
        return view('supplier.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $units = Unit::all();
        $discounts = Tax::all();
        return view('supplier.create', compact('categories', 'units','discounts'));
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
            'supplier_code' => 'required|unique:suppliers,supplier_code',
            'name' => 'required',
            'mobile' => 'nullable|digits:11',
            'address' => 'required|min:3',
            'details' => 'nullable|min:3',
            'previous_balance' => 'nullable|numeric',
            'item_image.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required',
        ]);

        // Check for duplicates within the form itself
        if ($request->has('item_description')) {
            $descriptions = array_map('strtolower', array_filter($request->item_description));
            if (count($descriptions) !== count(array_unique($descriptions))) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Duplicate product descriptions found in the form. Please remove or rename them.');
            }
        }

        // Create supplier
        $supplier = Supplier::create([
            'supplier_code' => $request->supplier_code,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'details' => $request->details,
            'tax' => $request->tax,
            'email' => $request->email,
            'previous_balance' => $request->previous_balance ?? 0,
            'status' => $request->status,
        ]);

        // Loop through items
        $item_codes = $request->item_code ?? [];
        $item_descriptions = $request->item_description ?? [];

        foreach ($item_codes as $index => $code) {
            $code = trim($code);
            $desc = $item_descriptions[$index] ?? null;
            if (!$code || !$desc) continue; // skip empty rows

            // Check for duplicates in DB
            $exists = SupplierItem::where('supplier_id', $supplier->id)
                ->where('item_description', $desc)
                ->exists();

            if ($exists) {
                $supplier->delete(); // rollback
                return redirect()->back()
                    ->withInput()
                    ->with('error', "The product '{$desc}' already exists for this supplier.");
            }

            // Handle image
            $imagePath = null;
            if ($request->hasFile('item_image') && isset($request->file('item_image')[$index])) {
                $image = $request->file('item_image')[$index];
                $supplierFolder = $supplier->supplier_code ?? 'items';
                $imagePath = $image->store("items/{$supplierFolder}", 'public');
            }

            // Save SupplierItem
            $item = SupplierItem::create([
                'supplier_id' => $supplier->id,
                'item_code' => $code,
                'category_id' => $request->category_id[$index] ?? null,
                'item_description' => $desc,
                'item_price' => $request->unit_cost[$index] ?? 0,
                'net_price' => $request->net_cost[$index] ?? 0,
                'unit_id' => $request->unit_id[$index] ?? null,
                'item_qty' => $request->item_qty[$index] ?? 0,
                'discount_less_add' => $request->discount_type[$index] ?? null,
                'discount_1' => $request->discount1[$index] ?? null,
                'discount_2' => $request->discount2[$index] ?? null,
                'discount_3' => $request->dis3[$index] ?? null,
                'item_image' => $imagePath,
                'volume_less' => $request->volume_less[$index] ?? null,
                'regular_less' => $request->regular_less[$index] ?? null,
            ]);

            // Generate product code
            $lastCode = Product::lockForUpdate()
                ->orderBy('id', 'desc')
                ->value('product_code');

            preg_match('/AVT(\d+)/', $lastCode ?? '', $matches);
            $nextNumber = isset($matches[1]) ? ((int)$matches[1] + 1) : 1;
            $productCode = 'AVT' . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

            // Create Product linked to SupplierItem
            $product = $item->products()->create([
                'product_code' => $productCode,
                'supplier_product_code' => $code,
                'product_name' => $desc,
                'unit_id' => $request->unit_id[$index] ?? null,
                'category_id' => $request->category_id[$index] ?? null,
                'quantity' => 0,
                'sales_price' => $request->unit_cost[$index] ?? 0,
                'discount_less_add' => $request->discount_type[$index] ?? null,
                'discount_1' => $request->discount_1[$index] ?? null,
                'discount_2' => $request->discount_2[$index] ?? null,
                'discount_3' => $request->discount_3[$index] ?? null,
            ]);

            // Add supplier info to product_supplier
            ProductSupplier::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'supplier_id' => $supplier->id,
                ],
                [
                    'price' => $request->unit_cost[$index] ?? 0,
                    'net_price' => $request->net_cost[$index] ?? 0,
                ]
            );
        }

        return redirect()->back()->with('message', 'Supplier and items have been added successfully!');
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
        $supplier = Supplier::with('items')->findOrFail($id);
        $categories = Category::all(); 
        $units = Unit::all();        
        $discounts = Tax::all();
        return view('supplier.edit', compact('supplier', 'categories', 'units','discounts'));
    }

    /**
     * Show product item per Supplier
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showProducts($id)
    {
        $supplier = Supplier::with('items')->findOrFail($id);
        $categories = Category::all();
        $units = Unit::all();
        $discounts_items = Tax::all();
        return view('supplier.supplier-products', compact('supplier', 'categories', 'units','discounts_items'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'mobile' => 'nullable|string',
            'email' => 'nullable|email',
            'item_code.*' => 'nullable|string',
            'item_description.*' => 'nullable|string',
            'item_price.*' => 'nullable|numeric',
            'item_amount.*' => 'nullable|numeric',
            'item_image.*' => 'nullable|image|max:2048',
        ]);

        $supplier = Supplier::findOrFail($id);

        \Log::info('Supplier update started', [
            'supplier_id' => $supplier->id,
            'old_data' => $supplier->only([
                'supplier_code',
                'name',
                'mobile',
                'email',
                'address',
                'tax',
                'details',
                'status'
            ]),
            'updated_by' => auth()->user()->id ?? null,
        ]);

        $supplier->update([
            // 'supplier_code' => $request->supplier_code,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'address' => $request->address,
            'tax' => $request->tax,
            'details' => $request->details,
            'status' => $request->status
        ]);

        \Log::info('Supplier updated successfully', [
            'supplier_id' => $supplier->id,
            'new_data' => $supplier->fresh()->only([
                'supplier_code',
                'name',
                'mobile',
                'email',
                'address',
                'tax',
                'details',
                'status'
            ]),
        ]);

        return redirect()
            ->route('supplier.supplier-products', $supplier->id)
            ->with('message', 'Supplier updated successfully.');
    }

    public function getInfo($id)
    {
        $supplier = Supplier::findOrFail($id);
        return response()->json([
            'supplier_code' => $supplier->supplier_code,
            'name' => $supplier->name,
            'address' => $supplier->address,
            'phone' => $supplier->mobile,
            'email' => $supplier->email,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $isUsedInSupplierItems = $supplier->items()->exists();
        $isUsedInProductSupplier = $supplier->productSupplier()->exists();
        $isUsedInPurchases = $supplier->purchases()->exists();

        if ($isUsedInProductSupplier || $isUsedInPurchases) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete this supplier because it is used by either supplier items, products or purchases.'
            ]);
        }
        $supplier->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Supplier deleted successfully.'
        ]);    
    }

}
