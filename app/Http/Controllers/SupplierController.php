<?php

namespace App\Http\Controllers;

use App\Supplier;
use App\SupplierItem;
use App\Unit;
use App\Category;
use Illuminate\Http\Request;
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
        return view('supplier.create', compact('categories', 'units'));
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
            'name' => 'required',
            'mobile' => 'required|min:3|digits:11',
            'address' => 'required|min:3',
            'details' => 'required|min:3|',
            'previous_balance' => 'nullable|numeric',
            'item_image.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $supplier = Supplier::create([
            'supplier_code' => $request->supplier_code,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'details' => $request->details,
            'tax' => $request->tax,
            'email' => $request->email,
            'previous_balance' => $request->previous_balance ?? 0,
        ]);

        if ($request->has('item_code')) {
            foreach ($request->item_code as $index => $code) {
                if ($code !== null && $code !== '') {
                    $imagePath = null;
                    // Check if file exists for this index
                    if ($request->hasFile('item_image') && isset($request->file('item_image')[$index])) {
                        $image = $request->file('item_image')[$index];
                        $supplierFolder = $supplier->supplier_code ?? 'items';
                        $imagePath = $image->store("items/{$supplierFolder}", 'public'); // Stored in storage/app/public/items/SUP-XXX/
                    }
                    SupplierItem::create([
                        'supplier_id' => $supplier->id,
                        'item_code' => $code,
                        'category_id' => $request->item_category[$index] ?? null,
                        'item_description' => $request->item_description[$index] ?? null,
                        'item_price' => $request->item_price[$index] ?? 0,
                        'item_amount' => $request->item_amount[$index] ?? 0,
                        'unit_id' => $request->unit_id[$index] ?? null,
                        'item_qty' => $request->item_qty[$index] ?? 0, 
                        'item_image' => $imagePath,
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
         $supplier = Supplier::with('items')->findOrFail($id);
        $categories = Category::all(); 
        $units = Unit::all();        

        return view('supplier.edit', compact('supplier', 'categories', 'units'));
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
        return view('supplier.supplier-products', compact('supplier'));
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

        // ✅ Fetch the supplier
        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'supplier_code' => $request->supplier_code,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'address' => $request->address,
            'tax' => $request->tax,
            'details' => $request->details,
        ]);

        $supplierCode = $supplier->supplier_code;
        $itemIds = $request->item_ids ?? [];
        $descriptions = $request->item_description ?? [];
        $prices = $request->item_price ?? [];
        $amounts = $request->item_amount ?? [];
        $codes = $request->item_code ?? [];
        $item_category = $request->category_id ?? [];
        $unit_id = $request->unit_id ?? [];
        $item_qty = $request->item_qty ?? [];


        foreach ($codes as $index => $code) {
            $itemId = $itemIds[$index] ?? null;

            $data = [
                'supplier_id' => $supplier->id,
                'item_code' => $code,
                'category_id' => $item_category[$index] ?? null,
                'item_description' => $descriptions[$index] ?? '',
                'item_price' => $prices[$index] ?? 0,
                'item_amount' => $amounts[$index] ?? 0,
                'unit_id' => $unit_id[$index] ?? null,
                'item_qty' => $item_qty[$index] ?? 0, 
            ];

            if ($request->hasFile("item_image.$index")) {
                $file = $request->file("item_image.$index");
                $randomName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $folder = "items/{$code}";
                $file->storeAs($folder, $randomName, 'public');
                $data['item_image'] = "$folder/$randomName";
            }

            if ($itemId) {
               SupplierItem::where('id', $itemId)->update($data);
            } else {
                SupplierItem::create($data);
            }
        }

        return redirect()->route('supplier.index')->with('message', 'Supplier updated successfully.');
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
        $supplier->delete();
        return redirect()->back()->with('success', 'Supplier and all items deleted successfully.');
    }

}
