<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\Unit;
use App\Models\Category;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupplierItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'item_description' => 'required|string|max:255',
            'unit_id' => 'nullable|exists:units,id',
            'item_price' => 'nullable|numeric',
            'net_price' => 'nullable|numeric',
            'discount_less_add' => 'nullable|in:less,add',
            'discount_1' => 'nullable|numeric',
            'discount_2' => 'nullable|numeric',
            'discount_3' => 'nullable|numeric',
            'volume_less' => 'nullable|string|max:50',
            'regular_less' => 'nullable|string|max:50',
            'item_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $item = SupplierItem::findOrFail($id);

        // Log old data before update
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

        // Update item
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

        // Log new data after update
        \Log::info('SupplierItem updated successfully', [
            'item_id' => $item->id,
            'new_data' => $item->fresh()->toArray(),
            'updated_by' => auth()->user()->id ?? null,
        ]);

        return back()->with('message', 'Item updated successfully.');
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
            'supplier_id' => 'required|exists:suppliers,id',
            'item_code' => 'required|unique:supplier_items,item_code',
            'item_description' => 'required',
            'item_price' => 'required|numeric',
            'net_price' => 'required|numeric',
        ]);

        $item = new SupplierItem();
        $item->supplier_id = $request->supplier_id;
        $item->item_code = $request->item_code;
        $item->category_id = $request->category_id;
        $item->unit_id = $request->unit_id;
        $item->item_description = $request->item_description;
        $item->item_price = $request->item_price;
        $item->net_price = $request->net_price;
        $item->discount_less_add = $request->discount_less_add;
        $item->discount_1 = $request->discount_1;
        $item->discount_2 = $request->discount_2;
        $item->discount_3 = $request->discount_3;
        $item->volume_less = $request->volume_less;
        $item->regular_less = $request->regular_less;

        if($request->hasFile('item_image')){
            $path = $request->file('item_image')->store('items', 'public');
            $item->item_image = $path;
        }

        $item->save();

        return redirect()->back()->with('success', 'Item added successfully.');
    }
}
