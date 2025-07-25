<?php

namespace App\Http\Controllers;

use App\Supplier;
use App\SupplierItem;
use App\Unit;
use Illuminate\Http\Request;

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
        return view('supplier.create');
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
            'supplier_code' => 'required|unique:suppliers',
            'name' => 'required|min:3|unique:suppliers|regex:/^[a-zA-Z ]+$/',
            'address' => 'required|min:3',
            'mobile' => 'required|min:3|digits:11',
            'details' => 'required|min:3|',
            'previous_balance' => 'min:3',

        ]);

       $supplier = Supplier::create([
            'supplier_code' => $request->supplier_code,
            'name' => $request->name,
            'address' => $request->address,
            'mobile' => $request->mobile,
            'details' => $request->details,
            'previous_balance' => $request->previous_balance ?? 0,
            'tax' => $request->tax,
        ]);
        if ($request->has('item_code')) {
            foreach ($request->item_code as $index => $code) {
                if ($code !== null && $code !== '') {
                    SupplierItem::create([
                        'supplier_id' => $supplier->id,
                        'item_code' => $code,
                        'item_description' => $request->item_description[$index] ?? null,
                        'item_price' => $request->item_price[$index] ?? 0,
                        'item_amount' => $request->item_amount[$index] ?? 0,
                    ]);
                }
            }
        }
        return redirect()->back()->with('message', 'New supplier has been added successfully!');
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
        $supplier = Supplier::findOrFail($id);
        return view('supplier.edit', compact('supplier'));
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
            'name' => 'required|min:3|regex:/^[a-zA-Z ]+$/',
            'address' => 'required|min:3',
            'mobile' => 'required|digits:11',
            'details' => 'required|min:3',
            'previous_balance' => 'nullable|numeric',
            'tax' => 'nullable|string',
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'name' => $request->name,
            'address' => $request->address,
            'mobile' => $request->mobile,
            'details' => $request->details,
            'previous_balance' => $request->previous_balance ?? 0,
            'tax' => $request->tax,
        ]);

        // Sync supplier items
        $existingItemIds = $supplier->supplierItems()->pluck('id')->toArray();
        $submittedItemIds = $request->item_id ?? [];

        // Delete items that were removed in the form
        $itemsToDelete = array_diff($existingItemIds, $submittedItemIds);
        SupplierItem::destroy($itemsToDelete);

        // Update or create supplier items
        if ($request->has('item_code')) {
            foreach ($request->item_code as $index => $code) {
                $itemId = $request->item_id[$index] ?? null;

                $itemData = [
                    'supplier_id' => $supplier->id,
                    'item_code' => $code,
                    'item_description' => $request->item_description[$index] ?? null,
                    'item_price' => $request->item_price[$index] ?? 0,
                    'item_amount' => $request->item_amount[$index] ?? 0,
                ];

                if ($itemId && in_array($itemId, $existingItemIds)) {
                    // Update existing item
                    SupplierItem::where('id', $itemId)->update($itemData);
                } else {
                    // Create new item
                    SupplierItem::create($itemData);
                }
            }
        }

        return redirect()->route('supplier.index')->with('message', 'Supplier and items updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        $supplier->delete();
        return redirect()->back();

    }
}
