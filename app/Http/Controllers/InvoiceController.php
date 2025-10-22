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
use App\Collection;
use App\Tax;
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
        $taxes = Tax::all();

        // get all active payment modes
        $paymentModes = ModeOfPayment::where('is_active', 1)->get();

        return view('invoice.create', compact('customers','products','paymentModes','units','taxes'));
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
            $request->validate([
                'customer_id'      => 'required|exists:customers,id',
                'payment_mode_id'  => 'required|exists:mode_of_payment,id',
                'invoice_date'     => 'required|date',
                'due_date'         => 'required|date',
                'product_id'       => 'required|array|min:1',
                'qty'              => 'required|array|min:1',
                'price'            => 'required|array|min:1',
            ]);

            \Log::info('Invoice request data', $request->all());

            $invoice = Invoice::create([
                'invoice_number'    => $request->invoice_number,
                'customer_id'       => $request->customer_id,
                'invoice_date'      => $request->invoice_date,
                'due_date'          => $request->due_date,
                'payment_mode_id'   => $request->payment_mode_id,
                'discount_type'     => $request->discount_type,
                'discount_value'    => $request->discount_value ?? 0,
                'subtotal'          => 0,
                'shipping_fee'      => $request->shipping_fee ?? 0,
                'other_charges'     => $request->other_charges ?? 0,
                'grand_total'       => 0,
                'remarks'           => $request->remarks,
                'invoice_status'    => 'pending',
                'payment_status'    => 'pending',
                'discount_approved' => $request->discount_approved ?? 0,
            ]);

            // Normalize inputs
            $products  = (array) $request->product_id;
            $qtys      = (array) $request->qty;
            $prices    = (array) $request->price;
            // $request->dis should be like: dis[0] => [1,2], dis[1] => [3]
            $discounts = $request->dis ?? [];

            $subtotal = 0;

            foreach ($products as $index => $productId) {
                $qty   = (float) ($qtys[$index] ?? 0);
                $price = (float) ($prices[$index] ?? 0);
                $lineTotal = $price * $qty;

                // Initialize per-product discounts array (so it won't carry from previous iteration)
                $invoiceSaleDiscounts = [];

                // Get discount IDs for this product (could be missing)
                $disIds = [];

                // handle different shapes defensively:
                // If dis is an array keyed by product index and each value is an array of IDs -> use that
                if (isset($discounts[$index]) && is_array($discounts[$index])) {
                    $disIds = $discounts[$index];
                } else {
                    // fallback: if discounts is flat (bad shape), try to map by position
                    // e.g. dis => [ [1], 2 ] etc. we'll try to coerce
                    if (is_array($discounts) && array_key_exists($index, $discounts)) {
                        $maybe = $discounts[$index];
                        if (is_array($maybe)) $disIds = $maybe;
                        elseif ($maybe) $disIds = [$maybe];
                    } elseif (is_array($discounts) && isset($discounts[0]) && !is_array($discounts[0])) {
                        // last resort: if user submitted single-level dis[] for first product only,
                        // you may want to combine them — but safest is to ignore. So skip.
                        $disIds = [];
                    }
                }

                // For each discount id, lookup tax row, get numeric rate and type, apply sequentially
                foreach ($disIds as $disId) {
                    if (!$disId) continue; // skip empty selects
                    $tax = DB::table('taxes')->where('id', $disId)->first();
                    if (!$tax) continue;

                    // Figure out numeric discount value (defensive)
                    // Try common column names: 'discount_value', 'value', otherwise parse numbers from 'name'
                    $rate = null;
                    if (isset($tax->discount_value)) $rate = floatval($tax->discount_value);
                    elseif (isset($tax->value)) $rate = floatval($tax->value);
                    else {
                        // parse digits from name (e.g. "10 %" or "Promo 5%")
                        $num = preg_replace('/[^0-9.]/', '', $tax->name);
                        $rate = $num !== '' ? floatval($num) : 0;
                    }

                    // Determine discount type (prefer explicit column `type` or fallback to percent)
                    $t = strtolower($tax->type ?? 'percent');
                    if ($t === 'percent' || $t === 'percentage') {
                        // apply percent on current running lineTotal
                        $lineTotal -= ($lineTotal * ($rate / 100));
                    } else {
                        // fixed amount
                        $lineTotal -= $rate;
                    }

                    // push to array to save after invoice line created
                    $invoiceSaleDiscounts[] = [
                        'discount_name'  => $tax->name,
                        'discount_type'  => ($t === 'percent' || $t === 'percentage') ? 'percent' : 'fixed',
                        'discount_value' => $rate,
                    ];
                }

                // create invoice sales line
                $line = InvoiceSales::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $productId,
                    'qty'        => $qty,
                    'price'      => $price,
                    'dis'        => 0,
                    'amount'     => round($lineTotal, 2),
                ]);

                // save discounts for this invoice sale
                foreach ($invoiceSaleDiscounts as $d) {
                    DB::table('invoice_sales_discounts')->insert([
                        'invoice_sale_id' => $line->id,
                        'discount_name'   => $d['discount_name'],
                        'discount_type'   => $d['discount_type'],
                        'discount_value'  => $d['discount_value'],
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }

                $subtotal += round($lineTotal, 2);

                // update product stock
                $product = Product::find($productId);
                if ($product) {
                    $product->remaining_stock -= $qty;
                    $product->threshold = $product->remaining_stock <= 10 ? 1 : floor($product->remaining_stock * 0.2);
                    if ($product->remaining_stock <= 0) $product->status = 'Out of Stock';
                    elseif ($product->remaining_stock <= $product->threshold) $product->status = 'Low Stock';
                    else $product->status = 'In Stock';
                    $product->save();
                }
            }

            // overall discount
            $overallDiscount = 0;
            if ($request->discount_type === 'overall' && $request->discount_value > 0) {
                $overallDiscount = $subtotal * ($request->discount_value / 100);
            }

            $afterDiscount = $subtotal - $overallDiscount;
            $shipping = $request->shipping_fee ?? 0;
            $other = $request->other_charges ?? 0;
            $grandTotal = $afterDiscount + $shipping + $other;

            $invoice->update([
                'subtotal'    => $subtotal,
                'grand_total' => $grandTotal,
            ]);

            DB::commit();

            return redirect()->route('invoice.index')->with('message', 'Invoice created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()])->withInput();
        }
    }

    public function validateAdminPassword(Request $request)
    {
        $request->validate(['password' => 'required']);

        // Use the correct column name: user_role
        $admin = User::where('user_role', 'super_admin')->first();

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
        $invoice = Invoice::with('customer')->findOrFail($id);
        return view('invoice.modal', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invoice = Invoice::with(['items.discounts'])->findOrFail($id);
        $customers = Customer::all();
        $products = Product::all();
        $units = Unit::all();
        $paymentModes = ModeofPayment::all();
        $taxes = Tax::all(); // If you’re using discount options from $taxes in the form

        return view('invoice.edit', compact('invoice', 'customers', 'products', 'units', 'paymentModes', 'taxes'));
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
        DB::transaction(function() use ($request, $id) {
            $invoice = Invoice::findOrFail($id);

            // Update main invoice details
            $invoice->update([
                'customer_id'    => $request->customer_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date'   => $request->invoice_date,
                'due_date'       => $request->due_date,
                'payment_mode_id'=> $request->payment_mode_id,
                'discount_type'  => $request->discount_type,
                'discount_value' => $request->discount_value ?? 0,
                'shipping_fee'   => $request->shipping_fee ?? 0,
                'other_charges'  => $request->other_charges ?? 0,
                'subtotal'       => $request->subtotal ?? 0,
                'grand_total'    => $request->grand_total ?? 0,
                'remarks'        => $request->remarks,
            ]);

            // ✅ Delete old items and related discounts
            $oldItems = $invoice->items;
            foreach ($oldItems as $item) {
                DB::table('invoice_sales_discounts')->where('invoice_sale_id', $item->id)->delete();
            }
            $invoice->items()->delete();

            // ✅ Re-insert updated items and discounts
            foreach ($request->product_id as $key => $productId) {
                $invoiceSale = InvoiceSales::create([
                    'invoice_id'   => $invoice->id,
                    'product_id'   => $productId,
                    'product_code' => $request->product_code[$key] ?? '',
                    'unit_id'      => $request->unit[$key] ?? null,
                    'qty'          => $request->qty[$key] ?? 0,
                    'price'        => $request->price[$key] ?? 0,
                    'dis'          => $request->dis[$key] ?? 0,
                    'amount'       => $request->amount[$key] ?? 0,
                ]);

                // ✅ If discount exists, store it in invoice_sales_discounts
                if (!empty($request->dis[$key]) && $request->dis[$key] > 0) {
                    DB::table('invoice_sales_discounts')->insert([
                        'invoice_sale_id' => $invoiceSale->id,
                        'discount_name'   => 'Custom Discount',
                        'discount_type'   => 'percent',
                        'discount_value'  => $request->dis[$key],
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }

            // ✅ Optional: record overall discount for tracking
            if ($request->discount_type === 'overall' && $request->discount_value > 0) {
                DB::table('invoice_sales_discounts')->insert([
                    'invoice_sale_id' => null,
                    'discount_name'   => 'Overall Discount',
                    'discount_type'   => 'percent',
                    'discount_value'  => $request->discount_value,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        });

        return redirect()->route('invoice.index')->with('message', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return redirect()->route('invoice.index')->with('message','Invoice deleted successfully.');
    }

    public function details($id)
    {
        $invoice = Invoice::with('customer')->findOrFail($id);

        // Total paid so far
        $paid = Collection::where('invoice_id', $invoice->id)->sum('amount_paid');
        $balance = $invoice->grand_total - $paid;

        return response()->json([
            'invoice_number' => $invoice->invoice_number,
            'grand_total'    => $invoice->grand_total,
            'balance'        => $balance,
            'customer'       => [
                'id'      => $invoice->customer->id,
                'name'    => $invoice->customer->name,
                'email'   => $invoice->customer->email,
                'phone'   => $invoice->customer->phone,
                'address' => $invoice->customer->address,
            ]
        ]);
    }

    //Update invoice status
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'invoice_status' => 'required|in:pending,approved,canceled',
        ]);

        $invoice = Invoice::findOrFail($id);
        $invoice->invoice_status = $request->invoice_status; 
        $invoice->save();

        return response()->json([
            'success' => true,
            'invoice' => $invoice
        ]);
    }

    public function print($id)
    {
        $invoice = Invoice::with(['sales.product', 'sales.unit', 'customer'])
            ->findOrFail($id);

        return view('invoice.print', compact('invoice'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        $invoices = Invoice::with(['customer', 'paymentMode'])
            ->withSum('collections as paid_total', 'amount_paid') // total payments
            ->where('invoice_status', 'approved') // only approved invoices
            ->get()
            ->map(function ($invoice) {
                $paid = $invoice->paid_total ?? 0;
                $invoice->balance = $invoice->grand_total - $paid;

                // Add payment mode
                $invoice->payment_mode_name = $invoice->paymentMode->name ?? 'N/A';

                // // Compute dynamic payment status
                // if ($invoice->balance <= 0) {
                //     $invoice->payment_status = 'paid';
                // } elseif ($paid > 0) {
                //     $invoice->payment_status = 'partial';
                // } else {
                //     $invoice->payment_status = 'pending';
                // }

                // if ($invoice->due_date && now()->gt($invoice->due_date) && $invoice->balance > 0) {
                //     $invoice->payment_status = 'overdue';
                // }

                return $invoice;
            })
            // Exclude fully paid invoices
            ->reject(function ($invoice) {
                return $invoice->payment_status === 'paid';
            })
            ->values(); // Reindex collection

        
        if ($query) {
            $invoices = $invoices->filter(function ($invoice) use ($query) {
                return str_contains(strtolower($invoice->invoice_number), strtolower($query)) ||
                    str_contains(strtolower($invoice->customer->name), strtolower($query)) ||
                    str_contains(strtolower($invoice->customer->email), strtolower($query)) ||
                    str_contains(strtolower($invoice->customer->mobile), strtolower($query));
            })->values();
        }

        return response()->json($invoices);
    }

    public function approve(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

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
        $invoice->invoice_status = 'approved';
        $invoice->save();

        return response()->json(['success' => 'Invoice approved successfully!']);
    }
}
