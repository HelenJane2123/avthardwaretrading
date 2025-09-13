<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Product;
use App\Sale;          // singular Sale model
use App\Supplier;
use App\Invoice;
use App\InvoiceSales;
use App\Unit;
use App\ModeOfPayment;
use App\Customer;
use App\Category;
use App\User;
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
        // Load categories with their products (only necessary columns)
        $categories = Category::with(['products' => function($q){
            $q->select('id','product_name','product_code','sales_price','remaining_stock','category_id');
        }])->get();

        // Also load a flat products list (fallback or for other places in the form)
        $products = Product::select('id','product_name','product_code','sales_price','remaining_stock','category_id')->get();

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
            // ✅ Step 1: Validate request
            $request->validate([
                'customer_id'      => 'required|exists:customers,id',
                'payment_mode_id'  => 'required|exists:mode_of_payment,id',
                'invoice_date'     => 'required|date',
                'product_id'       => 'required|array|min:1',
                'qty'              => 'required|array|min:1',
                'price'            => 'required|array|min:1',
            ]);

            // ✅ Debug: check incoming request
            \Log::info('Invoice request data', $request->all());

            // ✅ Step 2: Create invoice header
            $invoice = Invoice::create([
                'invoice_number'    => $request->invoice_number,
                'customer_id'       => $request->customer_id,
                'invoice_date'      => $request->invoice_date, // FIXED (was $request->date)
                'payment_mode_id'   => $request->payment_mode_id,
                'discount_type'     => $request->discount_type,
                'discount_value'    => $request->discount_value ?? 0,
                'subtotal'          => 0,
                'shipping_fee'      => $request->shipping_fee ?? 0,
                'other_charges'     => $request->other_charges ?? 0,
                'grand_total'       => 0,
                'remarks'           => $request->remarks,
                'status'            => 'pending',
                'discount_approved' => $request->discount_approved ?? 0
            ]);

            \Log::info('Invoice created', $invoice->toArray());

            $products   = $request->product_id;
            $qtys       = $request->qty;
            $prices     = $request->price;
            $discounts  = $request->dis ?? [];

            $subtotal = 0;

            // ✅ Step 3: Insert invoice line items
            foreach ($products as $index => $productId) {
                $qty   = $qtys[$index] ?? 0;
                $price = $prices[$index] ?? 0;
                $dis   = $discounts[$index] ?? 0;

                $lineTotal = $price * $qty;

                // Apply per-item discount
                if ($request->discount_type === 'per_item' && $dis > 0) {
                    $lineTotal -= ($lineTotal * $dis / 100);
                }

                $line = InvoiceSales::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $productId,
                    'qty'        => $qty,
                    'price'      => $price,
                    'dis'        => $request->discount_type === 'per_item' ? $dis : 0,
                    'amount'     => $lineTotal,
                ]);

                \Log::info('Invoice line inserted', $line->toArray());

                $subtotal += $lineTotal;

                // ✅ Step 4: Update product stock
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

                    \Log::info('Product stock updated', $product->toArray());
                }
            }

            // ✅ Step 5: Apply overall discount
            $overallDiscount = 0;
            if ($request->discount_type === 'overall' && $request->discount_value > 0) {
                $overallDiscount = $subtotal * ($request->discount_value / 100);
            }

            $afterDiscount = $subtotal - $overallDiscount;

            // ✅ Step 6: Add shipping + other charges
            $shipping   = $request->shipping_fee ?? 0;
            $other      = $request->other_charges ?? 0;
            $grandTotal = $afterDiscount + $shipping + $other;

            // ✅ Step 7: Update totals
            $invoice->update([
                'subtotal'    => $subtotal,
                'grand_total' => $grandTotal,
            ]);

            \Log::info('Invoice totals updated', [
                'subtotal'    => $subtotal,
                'grand_total' => $grandTotal,
            ]);

            DB::commit();

            return redirect()->route('invoice.index')
                ->with('message', 'Invoice created successfully!');
        } catch (\Exception $e) {
            DB::rollback();

            // ✅ Log the error with stack trace
            \Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'error' => 'Failed to create invoice: ' . $e->getMessage()
            ])->withInput();
        }
    }



    public function validateAdminPassword(Request $request)
    {
        $request->validate(['password' => 'required']);

        $admin = User::where('role', 'super_admin')->first();

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

    public function getInvoiceDetails($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);

        return response()->json([
            'invoice_number' => $invoice->invoice_number,
            'grand_total'    => $invoice->grand_total,
            'customer_name'  => $invoice->customer->name,
            'customer_email' => $invoice->customer->email ?? '',
            'customer_phone' => $invoice->customer->phone ?? '',
        ]);
    }
}
