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
use Illuminate\Support\Facades\DB;

class SupplierItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id'        => 'nullable|exists:categories,id',
            'item_description'   => 'required|string|max:255',
            'unit_id'            => 'nullable|exists:units,id',
            'item_price'         => 'nullable|numeric',
            'net_price'          => 'nullable|numeric',
            'discount_less_add'  => 'nullable|in:less,add',
            'discount_1'         => 'nullable|numeric',
            'discount_2'         => 'nullable|numeric',
            'discount_3'         => 'nullable|numeric',
            'volume_less'        => 'nullable|string|max:50',
            'regular_less'       => 'nullable|string|max:50',
            'item_image'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $item = SupplierItem::findOrFail($id);

        \Log::info('Updating SupplierItem', [
            'item_id' => $item->id,
            'old_data' => $item->toArray(),
            'updated_by' => auth()->user()->id ?? null,
        ]);

        // Handle image upload
        if ($request->hasFile('item_image')) {
            $file = $request->file('item_image');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $folder = "items/{$item->item_code}";
            $file->storeAs($folder, $filename, 'public');
            $request->merge(['item_image' => "$folder/$filename"]);
        }

        DB::transaction(function () use ($request, $item) {

            //Update supplier_items
            $item->update($request->only([
                'category_id',
                'item_description',
                'unit_id',
                'item_price',
                'net_price',
                'discount_less_add',
                'discount_1',
                'discount_2',
                'discount_3',
                'volume_less',
                'regular_less',
                'item_image',
            ]));

            // Update linked product inventory items
            $item->products()->update([
                'product_name' => $request->item_description,
                'unit_id'      => $request->unit_id,
                'category_id'  => $request->category_id,
            ]);
        });

        \Log::info('SupplierItem and linked products updated successfully', [
            'item_id' => $item->id,
            'new_data' => $item->fresh()->toArray(),
            'updated_by' => auth()->user()->id ?? null,
        ]);

        return back()->with('message', 'Supplier Item and linked product inventory updated successfully.');
    }

    public function getLastItemCode($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $lastItem = SupplierItem::where('supplier_id', $supplierId)
                    ->orderBy('id', 'desc')
                    ->first();

        if ($lastItem && $lastItem->item_code) {
            // Split by dash
            $parts = explode('-', $lastItem->item_code);
            $prefix = implode('-', array_slice($parts, 0, -1)); // everything except last part
            $number = (int) end($parts); // get the last numeric part
            $newNumber = str_pad($number + 1, 3, '0', STR_PAD_LEFT); // pad with zeros, e.g., 003

            $newCode = $prefix . '-' . $newNumber;
        } else {
            // Default code if no items exist
            $newCode = $supplier->supplier_code . '-001';
        }

        return response()->json(['new_code' => $newCode]);
    }

    // Store new supplier item
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'      => 'required|exists:suppliers,id',
            'item_code'        => 'required|unique:supplier_items,item_code',
            'item_description' => 'required',
            'item_price'       => 'required|numeric',
            'net_price'        => 'required|numeric',
        ]);

        // Save supplier item first
        $item = SupplierItem::create([
            'supplier_id'        => $request->supplier_id,
            'item_code'          => $request->item_code,
            'category_id'        => $request->category_id,
            'unit_id'            => $request->unit_id,
            'item_description'   => $request->item_description,
            'item_price'         => $request->item_price,
            'net_price'          => $request->net_price,
            'discount_less_add'  => $request->discount_less_add,
            'discount_1'         => $request->discount_1,
            'discount_2'         => $request->discount_2,
            'discount_3'         => $request->discount_3,
            'volume_less'        => $request->volume_less,
            'regular_less'       => $request->regular_less,
        ]);

        // Upload image (after item exists)
        if ($request->hasFile('item_image')) {
            $path = $request->file('item_image')->store('items', 'public');
            $item->update(['item_image' => $path]);
        }

        $lastCode = Product::lockForUpdate()
            ->orderBy('id', 'desc')
            ->value('product_code');

        preg_match('/AVT(\d+)/', $lastCode ?? '', $matches);
        $nextNumber = isset($matches[1]) ? ((int)$matches[1] + 1) : 1;

        $productCode = 'AVT' . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        // Automatically create inventory product
        $product = $item->products()->create([
            'product_code'        => $productCode,
            'supplier_product_code'=> $request->item_code,
            'product_name'        => $request->item_description,
            'unit_id'             => $request->unit_id,
            'category_id'         => $request->category_id,
            'quantity'            => 0, 
            'sales_price'         => $request->item_price,
            'discount_less_add'   => $request->discount_less_add,
            'discount_1'          => $request->discount_1,
            'discount_2'          => $request->discount_2,
            'discount_3'          => $request->discount_3,
        ]);

        // Add supplier info to product_supplier
        ProductSupplier::updateOrCreate(
            [
                'product_id'  => $product->id,
                'supplier_id' => $item->supplier_id,
            ],
            [
                'price'     => $item->item_price,
                'net_price' => $item->net_price,
            ]
        );

        return redirect()->back()->with('message', 'Item and inventory product added successfully.');
    }

    public function destroy($id)
    {
        // Find the supplier item
        $item = SupplierItem::findOrFail($id);

        // Get the item_code
        $itemCode = $item->item_code;

        // Delete related products that match supplier_product_code
        $relatedProducts = Product::where('supplier_product_code', $itemCode)->get();
        foreach ($relatedProducts as $product) {
            $product->delete();
        }

        // Delete the supplier item
        $item->delete();

        return redirect()->back()->with('message', 'Supplier item and linked products successfully deleted.');
    }
}
