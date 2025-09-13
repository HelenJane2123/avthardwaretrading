<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Product;
use App\Sale;          // singular Sale model
use App\Supplier;
use App\Invoice;
use App\Unit;
use App\ModeOfPayment;
use App\Customer;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
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
        $invoices = Invoice::all();
        return view('invoice.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        $units = Unit::all();

        // get all active payment modes
        $paymentModes = ModeOfPayment::where('is_active', 1)->get();

        return view('invoice.create', compact('customers','products','paymentModes','units'));
    }

    public function getCustomerInformation($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['customer' => null]);
        }

        return response()->json(['customer' => $customer]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // 1. Create invoice header (totals will be recalculated below)
            $invoice = Invoice::create([
                'invoice_number' => $request->invoice_number,
                'customer_id'    => $request->customer_id,
                'invoice_date'   => $request->date,
                'payment_id'     => $request->payment_id,
                'discount_type'  => $request->discount_type, // per_item | overall
                'discount_value' => $request->discount_value ?? 0,
                'subtotal'       => 0,
                'shipping'       => $request->shipping ?? 0,
                'other_charges'  => $request->other_charges ?? 0,
                'grand_total'    => 0,
                'remarks'        => $request->remarks,
                'status'         => 'Pending',
                'discount_approved' => $request->discount_approved;
            ]);

            $products   = $request->product_id;
            $qtys       = $request->qty;
            $prices     = $request->price;
            $discounts  = $request->dis;
            $discount_approved = $request->discount_approved;

            $subtotal = 0;

            // 2. Insert invoice line items
            foreach ($products as $index => $productId) {
                $qty   = $qtys[$index];
                $price = $prices[$index];
                $dis   = $discounts[$index] ?? 0;

                $lineTotal = $price * $qty;

                // Apply per-item discount ONLY if type = per_item
                if ($request->discount_type === 'per_item' && $dis > 0) {
                    $lineTotal -= ($lineTotal * $dis / 100);
                }

                InvoiceSales::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $productId,
                    'qty'        => $qty,
                    'price'      => $price,
                    'discount'   => $request->discount_type === 'per_item' ? $dis : 0,
                    'amount'     => $lineTotal,
                ]);

                $subtotal += $lineTotal;

                // Update product stock, threshold, and status
                $product = Product::find($productId);
                if ($product) {
                    $product->remaining_stock -= $qty;

                    $product->threshold = $product->remaining_stock <= 10
                        ? 1
                        : floor($product->remaining_stock * 0.2);

                    if ($product->remaining_stock <= 0) {
                        $product->status = 'Out of Stock';
                    } elseif ($product->remaining_stock <= $product->threshold) {
                        $product->status = 'Low Stock';
                    } else {
                        $product->status = 'In Stock';
                    }

                    $product->save();
                }
            }

            // 3. Apply overall discount (if type = overall)
            $overallDiscount = 0;
            if ($request->discount_type === 'overall' && $request->discount_value > 0) {
                $overallDiscount = $subtotal * ($request->discount_value / 100);
            }

            $afterDiscount = $subtotal - $overallDiscount;

            // 4. Add shipping + other charges
            $shipping   = $request->shipping ?? 0;
            $other      = $request->other_charges ?? 0;
            $grandTotal = $afterDiscount + $shipping + $other;

            // 5. Update invoice totals
            $invoice->update([
                'subtotal'    => $subtotal,
                'grand_total' => $grandTotal,
            ]);

            DB::commit();
            return redirect()->route('invoice.index')->with('message', 'Invoice created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create invoice: '.$e->getMessage()]);
        }
    }

    public function validateAdminPassword(Request $request)
    {
        $request->validate(['password' => 'required']);

        $admin = User::where('role', 'admin')->first();

        if ($admin && \Hash::check($request->password, $admin->password)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 401);
    }

    public function findPrice(Request $request){
        $data = DB::table('products')->select('sales_price')->where('id', $request->id)->first();
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

        'customer_id' => 'required',
        'product_id' => 'required',
        'qty' => 'required',
        'price' => 'required',
        'dis' => 'required',
        'amount' => 'required',
    ]);

        $invoice = Invoice::findOrFail($id);
        $invoice->customer_id = $request->customer_id;
        $invoice->total = 1000;
        $invoice->save();

        Sale::where('invoice_id', $id)->delete();

        foreach ( $request->product_id as $key => $product_id){
            $sale = new Sale();
            $sale->qty = $request->qty[$key];
            $sale->price = $request->price[$key];
            $sale->dis = $request->dis[$key];
            $sale->amount = $request->amount[$key];
            $sale->product_id = $request->product_id[$key];
            $sale->invoice_id = $invoice->id;
            $sale->save();


        }

         return redirect('invoice/'.$invoice->id)->with('message','invoice created Successfully');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        Sales::where('invoice_id', $id)->delete();
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        return redirect()->back();

    }
}
