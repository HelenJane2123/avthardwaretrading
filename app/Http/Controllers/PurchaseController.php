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
        $purchases = Purchase::with('supplier')->orderBy('created_at', 'desc')->get();
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
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation rules
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'salesman' => 'nullable|string|max:255',
            'payment_id' => 'required|exists:mode_of_payments,id',
            'product_id.*' => 'required|exists:products,id',
            'po_number' => 'required|string',
            'qty.*' => 'required|numeric|min:1',
            'price.*' => 'required|numeric|min:0',
            'dis.*' => 'required|numeric|min:0|max:100',
            'amount.*' => 'required|numeric|min:0',
            'unit.*' => 'required|string',
            'discount_type' => 'required|in:per_item,overall',
            'subtotal' => 'required|numeric|min:0',
            'overall_discount' => 'nullable|numeric|min:0',
            'discount_value' => 'required|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Create Purchase
        $purchase = new Purchase();
        $purchase->supplier_id = $request->supplier_id;
        $purchase->po_number = $request->po_number;
        $purchase->salesman = $request->salesman;
        $purchase->payment_id = $request->payment_id;
        $purchase->date = $request->date;

        $purchase->discount_type = $request->discount_type;
        $purchase->discount_value = $request->discount_value;
        $purchase->overall_discount = $request->overall_discount ?? 0;
        $purchase->subtotal = $request->subtotal;
        $purchase->shipping = $request->shipping ?? 0;
        $purchase->other_charges = $request->other_charges ?? 0;
        $purchase->grand_total = $request->grand_total;
        $purchase->remarks = $request->remarks;

        $purchase->save();

        // Store purchase details
        foreach ($request->product_id as $key => $productId) {
            $purchase->purchaseDetails()->create([
                'purchase_id' => $purchase->id, // ✅ Use the saved purchase ID
                'product_id' => $productId,
                'product_code' => $request->product_code[$key] ?? null, // ✅ Make it per-item
                'qty' => $request->qty[$key],
                'price' => $request->price[$key],
                'discount' => $request->dis[$key],
                'unit' => $request->unit[$key],
                'total' => $request->amount[$key],
            ]);
        }

        return redirect()->route('purchase.index')->with('success', 'Purchase added successfully');
    }

    public function findPrice(Request $request){
        $data = DB::table('products')->select('sales_price')->where('id', $request->id)->first();
        return response()->json($data);
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
        $invoice = Invoice::findOrFail($id);
        $sales = Sale::where('invoice_id', $id)->get();
        return view('invoice.show', compact('invoice','sales'));

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
