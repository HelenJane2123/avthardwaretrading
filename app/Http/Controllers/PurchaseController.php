<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Product;
use App\Purchase;
use App\Sale;
use App\Supplier;
use App\SupplierItem;
use App\Invoice;
use App\PurchaseDetail;
use App\ModeofPayment;
use App\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $purchases = Purchase::with('supplier')->get();
        return view('purchase.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $products = SupplierItem::all();
        $units = Unit::all();

        // get all active payment modes
        $paymentModes = ModeOfPayment::where('is_active', 1)->get();

         // get all active payment modes
        $paymentModes = ModeOfPayment::where('is_active', 1)->get();
        return view('purchase.create', compact('suppliers','products','paymentModes','units'));
    }

    public function getSupplierItems($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $items = SupplierItem::where('supplier_id', $id)
                ->select('id', 'item_code', 'item_description', 'item_price', 'item_amount')
                ->get();

           return response()->json([
                'supplier' => $supplier,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showDetails($id)
    {
        $purchase = Purchase::with(['supplier', 'items.supplierItem'])->findOrFail($id);
        return view('purchase.partial.details', compact('purchase'));
    }

    public function print($id)
    {
        $purchase = Purchase::with(['supplier', 'items.supplierItem'])->findOrFail($id);
        return view('purchase.partial.print', compact('purchase'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // ✅ Validation rules
        // $request->validate([
        //     'supplier_id'         => 'required|exists:suppliers,id',
        //     'date'                => 'required|date',
        //     'po_number'           => 'required|string|max:50|unique:purchases,po_number',
        //     'salesman'            => 'nullable|string|max:100',
        //     'payment_id'          => 'nullable|exists:mode_of_payment,id',
        //     'discount_type'       => 'nullable|string|in:percent,fixed',
        //     'overall_discount'    => 'nullable|numeric|min:0',
        //     'subtotal_value'      => 'required|numeric|min:0',
        //     'discount_value'      => 'nullable|numeric|min:0',
        //     'shipping_value'      => 'nullable|numeric|min:0',
        //     'other_value'         => 'nullable|numeric|min:0',
        //     'grand_total_value'   => 'required|numeric|min:0',
        //     'remarks'             => 'nullable|string',

        //     // ✅ Validate product line arrays
        //     'product_id'          => 'required|array|min:1',
        //     'product_id.*'        => 'required|exists:supplier_items,id',
        //     'product_code'        => 'required|array|min:1',
        //     'product_code.*'      => 'required|string|max:50',
        //     'qty'                 => 'required|array|min:1',
        //     'qty.*'               => 'required|integer|min:1',
        //     'price'               => 'required|array|min:1',
        //     'price.*'             => 'required|numeric|min:0',
        //     'dis'                 => 'nullable|array',
        //     'dis.*'               => 'nullable|numeric|min:0',
        //     'unit'                => 'required|array|min:1',
        //     'unit.*'              => 'required|string|max:50',
        //     'amount'              => 'required|array|min:1',
        //     'amount.*'            => 'required|numeric|min:0',
        // ]);

        // dd("Validation passed!", $request->all());

        DB::transaction(function () use ($request) {
            $purchase = Purchase::create([
                'supplier_id'        => $request->supplier_id,
                'date'               => $request->date,
                'po_number'          => $request->po_number,
                'salesman'           => $request->salesman,
                'payment_id'         => $request->payment_id,
                'discount_type'      => $request->discount_type,
                'overall_discount'   => $request->overall_discount,
                'subtotal'           => $request->subtotal,
                'discount_value'     => $request->discount_value,
                'shipping_value'     => $request->shipping_value,
                'other_value'        => $request->other_value,
                'grand_total'        => $request->grand_total_value,
                'remarks'            => $request->remarks,
            ]);

            foreach ($request->product_id as $index => $supplierItemId) {
                $purchase->items()->create([
                    'supplier_item_id' => $supplierItemId,
                    'product_code'     => $request->product_code[$index],
                    'qty'              => $request->qty[$index],
                    'unit_price'       => $request->price[$index],
                    'discount'         => $request->dis[$index] ?? 0,
                    'unit'             => $request->unit[$index],
                    'total'            => $request->amount[$index],
                ]);
            }
        });

        return redirect()->route('purchase.create')->with('success', 'Purchase saved successfully.');
    }

    public function findPrice(Request $request){
        $data = DB::table('products')->select('sales_price')->where('id', $request->id)->first();
        return response()->json($data);
    }

    public function getLatestPo()
    {
        $latest = Purchase::orderBy('id', 'desc')->first();

        return response()->json([
            'po_number' => $latest?->po_number
        ]);
    }

    public function findPricePurchase(Request $request) {
        $data = DB::table('product_suppliers')
                ->select('price')
                ->where('product_id', $request->id)
                ->where('supplier_id', $request->supplier_id) // Assuming you pass supplier_id from the frontend
                ->first();
    
        return response()->json($data);
    }    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase_items = PurchaseItem::where('purchase_id', $id)->get();
        return view('purhcase.show', compact('purchase','purchase_items'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customers = Customer::all();
        $products = Product::orderBy('id', 'DESC')->get();
        $invoice = Invoice::findOrFail($id);
        $sales = Sale::where('invoice_id', $id)->get();
        return view('invoice.edit', compact('customers','products','invoice','sales'));
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
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'product_id.*' => 'required|exists:products,id',
            'qty.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0',
            'dis.*' => 'required|numeric|min:0|max:100',
            'amount.*' => 'required|numeric|min:0',
            'discount_type' => 'required|in:per_item,overall',
            'subtotal' => 'required|numeric|min:0',
            'overall_discount' => 'nullable|numeric|min:0',
            'discount_value' => 'required|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
        ]);

        $purchase = Purchase::findOrFail($id);
        $purchase->supplier_id = $request->supplier_id;
        $purchase->date = $request->date;

        // Update new fields
        $purchase->discount_type = $request->discount_type;
        $purchase->overall_discount = $request->overall_discount ?? 0;
        $purchase->subtotal = $request->subtotal;
        $purchase->discount_value = $request->discount_value;
        $purchase->shipping = $request->shipping ?? 0;
        $purchase->other_charges = $request->other_charges ?? 0;
        $purchase->grand_total = $request->grand_total;

        $purchase->save();

        // Optionally delete and reinsert purchase details
        $purchase->purchaseDetails()->delete();

        foreach ($request->product_id as $key => $productId) {
            $purchase->purchaseDetails()->create([
                'supplier_id' => $request->supplier_id,
                'product_id' => $productId,
                'qty' => $request->qty[$key],
                'price' => $request->price[$key],
                'discount' => $request->dis[$key],
                'amount' => $request->amount[$key],
            ]);
        }

        return redirect()->route('purchase.index')->with('success', 'Purchase updated successfully');
    }


    public function getLatestPoNumber()
    {
        $latest = Purchase::orderBy('id', 'desc')->first();

        if ($latest && $latest->po_number) {
            return response()->json(['po_number' => $latest->po_number]);
        }

        return response()->json(['po_number' => null]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);

        // Delete related purchase details
        $purchase->purchaseDetails()->delete();

        // Then delete the purchase itself
        $purchase->delete();

        return redirect()->route('purchase.index')->with('success', 'Purchase deleted successfully');
    }

    public function generatePurchase($id)
    {
        $purchase = Purchase::with(['supplier', 'details.product'])->findOrFail($id);

        $pdf = Pdf::loadView('purchase.pdf', compact('purchase'));

        return $pdf->download('PO-'.$purchase->po_number.'.pdf');
    }

    
}
