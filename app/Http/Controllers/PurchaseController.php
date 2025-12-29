<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\Invoice;
use App\Models\PurchaseDetail;
use App\Models\ModeofPayment;
use App\Models\Unit;
use App\Models\Salesman;
use App\Models\Tax;
use App\Models\User;
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
        $paymentModes = ModeofPayment::all();
        $purchases = Purchase::with('supplier','salesman')->get();
        return view('purchase.index', compact('purchases','paymentModes'));
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
        $taxes = Tax::all();

        // get all active payment modes
        $paymentModes = ModeOfPayment::where('is_active', 1)->get();

         // get all active payment modes
        $paymentModes = ModeOfPayment::where('is_active', 1)->get();

        //get salesman
        $salesman = Salesman::where('status',1)->get();
        return view('purchase.create', compact('suppliers','products','paymentModes','units','salesman','taxes'));
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
        $purchase = Purchase::with([
            'supplier',
            'items.supplierItem',
            'paymentMode',
            'payments' // include the payment history
        ])->findOrFail($id);

        // Compute total paid from all payments
        $totalPaid = $purchase->payments->sum('amount_paid');
        $outstanding = $purchase->grand_total - $totalPaid;

        // Determine payment status
        if ($totalPaid <= 0) {
            $paymentStatus = 'Unpaid';
        } elseif ($totalPaid < $purchase->grand_total) {
            $paymentStatus = 'Partial Payment';
        } else {
            $paymentStatus = 'Fully Paid';
        }

        return view('purchase.partial.print', compact('purchase', 'totalPaid', 'outstanding', 'paymentStatus'));
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
                'salesman_id'        => $request->salesman_id,
                'payment_id'         => $request->payment_id,
                'discount_type'      => $request->discount_type,
                'discount_value'     => $request->discount_value ?? 0,
                'overall_discount'   => $request->overall_discount ?? 0,
                'subtotal'           => $request->subtotal ?? 0,
                'shipping'           => $request->shipping ?? 0,
                'other_charges'      => $request->other_charges ?? 0,
                'remarks'            => $request->remarks,
                'grand_total'        => $request->grand_total ?? 0,
                'is_approved'        => 0
            ]);

            foreach ($request->product_id as $index => $supplierItemId) {
                $purchase->items()->create([
                    'supplier_item_id'     => $supplierItemId,
                    'product_code'         => $request->product_code[$index],
                    'qty'                  => $request->qty[$index],
                    'unit_price'           => $request->price[$index],
                    'discount_less_add'    => $request->discount_less_add[$index],
                    'discount_1'           => $request->dis1[$index] ?? 0,
                    'discount_2'           => $request->dis2[$index] ?? 0,
                    'discount_3'           => $request->dis3[$index] ?? 0,
                    'unit'                 => $request->unit[$index],
                    'total'                => $request->amount[$index],
                ]);
            }
        });

        return redirect()->route('purchase.create')->with('message', 'Purchase saved successfully.');
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
        $purchase = Purchase::with('items.supplierItem.product', 'items.unit')
                            ->findOrFail($id);

        $suppliers = Supplier::all();
        $paymentModes = ModeofPayment::all();
        $units = Unit::all();
        $taxes = Tax::all();
        $taxesArray = $taxes->map(function($t) {
            return ['id' => $t->id, 'name' => $t->name];
        })->toArray();

        // Load supplier items linked to this purchase's supplier
        $supplierItems = SupplierItem::where('supplier_id', $purchase->supplier_id)
                                    ->get();

        $purchaseItemsArray = $purchase->items->map(function($i){
            return [
                'product_id' => $i->supplier_item_id,
                'product_code' => $i->supplierItem->item_code,
                'product_name' => $i->supplierItem->item_description,
                'unit_id' => $i->unit_id,
                'qty' => $i->qty,
                'price' => $i->price,
                'discount_less_add' => $i->discount_less_add,
                'dis1' => $i->discount_1,
                'dis2' => $i->discount_2,
                'dis3' => $i->discount_3,
                'unit_price' => $i->unit_price,
                'amount' => $i->total
            ];
        })->toArray();        
        
        //get salesman
        $salesman = Salesman::where('status',1)->get();
        return view('purchase.edit', compact(
            'purchase',
            'suppliers',
            'paymentModes',
            'units',
            'supplierItems',
            'salesman',
            'taxesArray',
            'taxes',
            'purchaseItemsArray'
        ));
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
        // ✅ Validation (mirrors store)
        // $request->validate([
        //     'supplier_id'         => 'required|exists:suppliers,id',
        //     'date'                => 'required|date',
        //     'po_number'           => 'required|string|max:50|unique:purchases,po_number,' . $id,
        //     'salesman'            => 'nullable|string|max:100',
        //     'payment_id'          => 'nullable|exists:mode_of_payment,id',
        //     'discount_type'       => 'nullable|string|in:percent,fixed',
        //     'overall_discount'    => 'nullable|numeric|min:0',
        //     'subtotal'            => 'required|numeric|min:0',
        //     'discount_value'      => 'nullable|numeric|min:0',
        //     'shipping_value'      => 'nullable|numeric|min:0',
        //     'other_value'         => 'nullable|numeric|min:0',
        //     'grand_total_value'   => 'required|numeric|min:0',
        //     'remarks'             => 'nullable|string',

        //     // ✅ Product line validation
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
        try {
            \Log::info('Purchase request data', $request->all());
            DB::transaction(function () use ($request, $id) {
                $purchase = Purchase::findOrFail($id);

                // ✅ Update purchase main record
                $purchase->update([
                    'supplier_id'        => $request->supplier_id,
                    'po_number'          => $request->po_number,
                    'salesman_id'        => $request->salesman_id,
                    'payment_id'         => $request->payment_id,
                    'date'               => $request->date,
                    'discount_type'      => $request->discount_type,
                    'discount_value'     => $request->discount_value ?? 0,
                    'overall_discount'   => $request->overall_discount ?? 0,
                    'subtotal'           => $request->subtotal ?? 0,
                    'shipping'           => $request->shipping ?? 0,
                    'other_charges'      => $request->other_charges ?? 0,
                    'remarks'            => $request->remarks,
                    'grand_total'        => $request->grand_total ?? 0,
                ]);

                // ✅ Reset and reinsert items
                $purchase->items()->delete();

                foreach ($request->product_id as $index => $supplierItemId) {
                    $purchase->items()->create([
                        'supplier_item_id' => $supplierItemId,
                        'product_code'     => $request->product_code[$index],
                        'qty'              => $request->qty[$index],
                        'unit_price'       => $request->price[$index],
                        'discount_less_add'    => $request->discount_less_add[$index],
                        'discount_1'         => $request->dis1[$index] ?? 0,
                        'discount_2'         => $request->dis2[$index] ?? 0,
                        'discount_3'         => $request->dis3[$index] ?? 0,
                        'unit'             => $request->unit[$index],
                        'total'            => $request->amount[$index],
                    ]);
                }
            });

            \Log::info('Purchase successfully updated', ['purchase_id' => $id]);
            return redirect()->route('purchase.index')->with('message', 'Purchase updated successfully.');
        } catch (\Throwable $e) {
            \Log::error('Purchase creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred while creating the invoice.');
        }
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

        return redirect()->route('purchase.index')->with('message', 'Purchase deleted successfully');
    }

    public function generatePurchase($id)
    {
        $purchase = Purchase::with(['supplier', 'details.product'])->findOrFail($id);

        $pdf = Pdf::loadView('purchase.pdf', compact('purchase'));

        return $pdf->download('PO-'.$purchase->po_number.'.pdf');
    }

    public function approve(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        // Find the super admin record
        $user = User::where('user_role', 'super_admin')->first();

        // Check if super admin record exists
        if (!$user) {
            return response()->json(['error' => 'Super Admin account not found.'], 404);
        }

        // Validate password input
        $request->validate([
            'password' => 'required|string',
        ]);

        // Check password match
        if (!\Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 403);
        }

        \Log::info('Approval attempt:', [
            'user_found' => $user ? true : false,
            'role' => $user->user_role ?? 'none',
            'entered_password' => $request->password,
        ]);

        // Approve the invoice
        $purchase->is_approved = 1;
        $purchase->save();

        return response()->json(['success' => 'Purchase approved successfully!']);
    }

    public function completePurchaseOrder($id)
    {
        $purchase = Purchase::with('items.product')->findOrFail($id);

        // Prevent double receiving
        if ($purchase->is_completed == 1) {
            return response()->json(['error' => 'This purchase order is already completed.'], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($purchase->items as $item) {
                $product = $item->product;

                if (!$product) continue;

                // Add purchased quantity to inventory
                $product->remaining_stock += $item->qty;
                $product->save();

                // Update product threshold status
                $this->updateProductStatus($product);
            }

            // Mark PO as completed
            $purchase->is_completed = 1;
            $purchase->save();

            DB::commit();

            return response()->json(['success' => 'Purchase order received and inventory updated!']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error completing purchase order: '.$e->getMessage());
            return response()->json(['error' => 'Failed to complete purchase order.'], 500);
        }
    }

    protected function updateProductStatus(Product $product)
    {
        $threshold = $product->threshold ?? 0;

        if ($product->remaining_stock <= 0) {
            $product->status = 'Out of Stock';
        } elseif ($product->remaining_stock <= $threshold) {
            $product->status = 'Low Stock';
        } else {
            $product->status = 'In Stock';
        }

        $product->save();
    }
}
