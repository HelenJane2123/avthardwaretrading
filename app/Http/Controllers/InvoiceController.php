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
        try {
            \Log::info('Invoice request data', $request->all());

            // Validate required fields
            $request->validate([
                'customer_id' => 'required',
                'invoice_date' => 'required|date',
                'invoice_number' => 'required|unique:invoices,invoice_number',
                'product_id' => 'required|array',
            ]);

            // Create the main invoice record
            $invoice = new Invoice();
            $invoice->customer_id = $request->customer_id;
            $invoice->invoice_number = $request->invoice_number;
            $invoice->invoice_date = $request->invoice_date;
            $invoice->due_date = $request->due_date;
            $invoice->payment_mode_id = $request->payment_mode_id;
            $invoice->discount_type = $request->discount_type;
            $invoice->shipping_fee = $request->shipping_fee ?? 0;
            $invoice->other_charges = $request->other_charges ?? 0;
            $invoice->subtotal = $request->subtotal ?? 0;
            $invoice->grand_total = $request->grand_total ?? 0;
            $invoice->discount_approved = $request->discount_approved ?? 0;
            $invoice->remarks = $request->remarks;
            $invoice->save();

            // Product-related fields
            $productIds = $request->product_id;
            $quantities = $request->qty;
            $prices = $request->price;
            $amounts = $request->amount;
            $discounts = $request->dis ?? []; // multiple discounts per item
            $discountType = $request->discount_type;

            // Loop through each product in the invoice
            foreach ($productIds as $index => $productId) {
                $product = new InvoiceSales();
                $product->invoice_id = $invoice->id;
                $product->product_id = $productId;
                $product->qty = $quantities[$index] ?? 0;
                $product->price = $prices[$index] ?? 0;
                $product->amount = $amounts[$index] ?? 0;
                $product->save();

                // 🔹 Apply per-item discounts (if applicable)
                if (in_array($discountType, ['per_item', 'overall'])) {
                    $itemDiscounts = isset($discounts[$index]) ? (array)$discounts[$index] : [];

                    foreach ($itemDiscounts as $discountId) {
                        InvoiceSalesDiscount::create([
                            'invoice_sales_id' => $product->id,
                            'discount_id' => $discountId,
                        ]);
                    }
                }
            }

            // 🔹 Apply overall discounts (if applicable)
            if (in_array($discountType, ['overall', 'per_item'])) {
                if ($request->has('overall_discounts')) {
                    foreach ($request->overall_discounts as $discountId) {
                        InvoiceOverallDiscount::create([
                            'invoice_id' => $invoice->id,
                            'discount_id' => $discountId,
                        ]);
                    }
                }
            }

            \Log::info('Invoice successfully created', ['invoice_id' => $invoice->id]);

            return redirect()->route('invoices.index')->with('success', 'Invoice created successfully!');
        } catch (\Throwable $e) {
            \Log::error('Invoice creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred while creating the invoice.');
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
