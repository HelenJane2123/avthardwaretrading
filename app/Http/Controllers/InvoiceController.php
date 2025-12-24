<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Product;
use App\Sale;
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
use App\InvoiceSalesDiscount;
use App\Salesman;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


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
        $user = User::where('user_status','active')->get();
        $locations = Customer::select('location')
                ->distinct()
                ->where('status', 1)
                ->whereNotNull('location')
                ->get();
        return view('invoice.index', compact('invoices', 'user', 'locations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::with(['products' => function($q) {
            $q->select('id','product_name','product_code','sales_price','remaining_stock','category_id','supplier_product_code')
            ->with(['supplierItems:id,item_code,item_price']); 
        }])->get();

        $today = date('Y-m-d');
        // Get the last invoice number for today
        $lastInvoice = Invoice::whereDate('invoice_date', $today)
                        ->orderBy('id', 'desc')
                        ->first();

        if ($lastInvoice) {
            // Extract series from last invoice number (e.g. DR-20251224-001)
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastSeries = intval(end($parts));
            $newSeries = str_pad($lastSeries + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSeries = '001'; // first invoice today
        }

        $invoiceNumber = 'DR-' . date('Ymd') . '-' . $newSeries;

        // Load products with base price
        $products = Product::select('id',
                'product_name',
                'product_code',
                'sales_price',
                'remaining_stock',
                'category_id',
                'supplier_product_code',
                'unit_id',
                'discount_type',
                'discount_1',
                'discount_2',
                'discount_3')
             ->with([
                    'supplierItems:id,item_code,item_price,supplier_id',
                    'unit:id,name'
                ])
            ->get();
            
        $customers = Customer::where('status', 1)->get();
        $units = Unit::where('status',1)->get();
        $taxes = Tax::where('status',1)->get();
        $paymentModes = ModeOfPayment::where('is_active', 1)->get();
        $salesman = Salesman::where('status',1)->get();
        $suppliers = Supplier::where('status',1)->get();

        return view('invoice.create', compact(
            'customers','products','paymentModes','units','taxes','salesman','suppliers','invoiceNumber'
        ));
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
            \Log::info('Invoice request data', $request->all());

            $request->validate([
                'customer_id'  => 'required',
                'invoice_date' => 'required|date',
                'product_id'   => 'required|array',
                'salesman'     => 'required'
            ]);

            $invoiceDate = Carbon::parse($request->invoice_date)->format('Ymd');

            // LOCKED query (safe now)
            $lastInvoice = Invoice::where('invoice_number', 'like', "DR-{$invoiceDate}-%")
                ->orderBy('invoice_number', 'desc')
                ->lockForUpdate()
                ->first();

            if ($lastInvoice) {
                $lastSeries = (int) substr($lastInvoice->invoice_number, -4);
                $nextSeries = str_pad($lastSeries + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $nextSeries = '0001';
            }

            $invoiceNumber = "DR-{$invoiceDate}-{$nextSeries}";

            // Save invoice
            $invoice = new Invoice();
            $invoice->customer_id       = $request->customer_id;
            $invoice->invoice_number    = $invoiceNumber;
            $invoice->invoice_date      = $request->invoice_date;
            $invoice->due_date          = $request->due_date;
            $invoice->payment_mode_id   = $request->payment_mode_id;
            $invoice->discount_type     = $request->discount_type;
            $invoice->shipping_fee      = $request->shipping_fee ?? 0;
            $invoice->other_charges     = $request->other_charges ?? 0;
            $invoice->subtotal          = $request->subtotal ?? 0;
            $invoice->grand_total       = $request->grand_total ?? 0;
            $invoice->discount_approved = $request->discount_approved ?? 0;
            $invoice->salesman          = $request->salesman;
            $invoice->remarks           = $request->remarks;
            $invoice->created_by        = auth()->id();
            $invoice->save();

            // Save products
            foreach ($request->product_id as $index => $productId) {
                InvoiceSales::create([
                    'invoice_id'         => $invoice->id,
                    'product_id'         => $productId,
                    'qty'                => $request->qty[$index] ?? 0,
                    'price'              => $request->price[$index] ?? 0,
                    'discount_less_add'  => $request->discount_less_add[$index] ?? 'less',
                    'discount_1'         => $request->dis1[$index] ?? 0,
                    'discount_2'         => $request->dis2[$index] ?? 0,
                    'discount_3'         => $request->dis3[$index] ?? 0,
                    'amount'             => $request->amount[$index] ?? 0,
                ]);
            }

            DB::commit();

            \Log::info('Invoice successfully created', ['invoice_id' => $invoice->id]);

            return redirect()->route('invoice.index')
                ->with('message', "Invoice {$invoiceNumber} created successfully!");

        } catch (\Throwable $e) {
            DB::rollBack();

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
        // Load invoice with items, their products, units, and supplier items
        $invoice = Invoice::with([
            'items.product.unit',
            'items.product.supplierItems'
        ])->findOrFail($id);

        // Load all customers, products, units, payment modes, taxes, and active salesmen
        $customers = Customer::all();
        $products = Product::with(['supplierItems', 'unit'])->get();
        $units = Unit::where('status', 1)->get();
        $paymentModes = ModeofPayment::all();
        $taxes = Tax::where('status', 1)->get();
        $salesman = Salesman::where('status', 1)->get();
        $suppliers = Supplier::where('status', 1)->get();

        // Map invoice items to include supplier_item_price
        $invoiceItems = $invoice->items->map(function($item) {
            $product = $item->product;
            $supplierItem = $product->supplierItems->where('item_code', $item->supplier_product_code)->first();
            $supplierPrice = $product->supplier_item_price
                ?? optional($product->supplierItems->first())->item_price
                ?? 0;
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'qty' => $item->qty,
                'price' => $item->price,
                'amount' => $item->amount,
                'discount_1' => $item->discount_1,
                'discount_2' => $item->discount_2,
                'discount_3' => $item->discount_3,
                'discount_less_add' => $item->discount_less_add,
                'unit_id' => $item->unit_id,
                'product' => [
                    'product_code' => $item->product->product_code,
                    'product_name' => $item->product->product_name,
                    'supplier_item_price' => $supplierPrice ?? 0
                ]
            ];
        });

        // Return the edit view with all necessary data
        return view('invoice.edit', compact(
            'invoice', 
            'customers', 
            'products', 
            'units', 
            'paymentModes', 
            'taxes', 
            'salesman',
            'invoiceItems',
            'suppliers'
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
        DB::transaction(function () use ($request, $id) {
            $invoice = Invoice::findOrFail($id);

            // Log invoice data before updating
            \Log::info("Updating Invoice ID: {$invoice->id}", [
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
                'items' => $invoice->items->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'qty' => $item->qty,
                        'price' => $item->price,
                        'amount' => $item->amount,
                    ];
                }),
            ]);

            // Restore previous stock for all invoice items
            // foreach ($invoice->items as $oldItem) {
            //     $product = Product::find($oldItem->product_id);
            //     if ($product) {
            //         $product->quantity += $oldItem->qty;
            //         $this->updateProductStatus($product);
            //     }
            // }

            // Remove old invoice items (and optional discounts)
            $oldItems = InvoiceSales::where('invoice_id', $invoice->id)->pluck('id');
            // InvoiceSalesDiscount::whereIn('invoice_sale_id', $oldItems)->delete(); // optional
            InvoiceSales::where('invoice_id', $invoice->id)->delete();

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
                'salesman'       => $request->salesman,
                'remarks'        => $request->remarks,
                'updated_by'     => auth()->id()
            ]);

            // Insert new invoice items
            $newProductIds = $request->product_id ?? [];
            foreach ($newProductIds as $key => $productId) {
                if (!$productId) continue; // skip empty rows

                $qty = $request->qty[$key] ?? 0;
                $price = $request->price[$key] ?? 0;
                $amount = $request->amount[$key] ?? 0;
                $discount_less_add = $request->discount_less_add[$key] ?? 'less';
                $discount_1 = $request->dis1[$key] ?? 0;
                $discount_2 = $request->dis2[$key] ?? 0;
                $discount_3 = $request->dis3[$key] ?? 0;

                $invoiceSale = InvoiceSales::create([
                    'invoice_id'        => $invoice->id,
                    'product_id'        => $productId,
                    'product_code'      => $request->product_code[$key] ?? '',
                    'unit_id'           => $request->unit[$key] ?? null,
                    'qty'               => $qty,
                    'price'             => $price,
                    'discount_less_add' => $discount_less_add,
                    'discount_1'        => $discount_1,
                    'discount_2'        => $discount_2,
                    'discount_3'        => $discount_3,
                    'amount'            => $amount,
                ]);

                // Adjust product stock
                // $product = Product::find($productId);
                // if ($product) {
                //     $product->quantity = max(0, $product->remaining_stock - $qty);
                //     $this->updateProductStatus($product);
                // }
            }

            // Optional: Log after update
            \Log::info("Invoice ID {$invoice->id} updated successfully.");
        });

        return redirect()->route('invoice.index')
            ->with('message', 'Invoice updated successfully.');
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
        $isUsedInCollection = $invoice->collections()->exists();

        if ($isUsedInCollection) {
            return redirect()->back()->with('error', 'Cannot delete this invoice because it is used by collection.');
        }
        
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
        $invoice = Invoice::with('items.product')->findOrFail($id);

        $user = User::where('user_role', 'super_admin')->first();
        if (!$user) {
            return response()->json(['error' => 'Super Admin account not found.'], 404);
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!\Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 403);
        }

        if ($invoice->invoice_status === 'approved') {
            return response()->json(['error' => 'Invoice is already approved.'], 400);
        }

        DB::beginTransaction();
        try {

            foreach ($invoice->items as $item) {
                $product = $item->product;
                if (!$product) continue;

                // Deduct stock
                $newStock = $product->remaining_stock - $item->qty;

                if ($newStock < 0) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "Insufficient stock for {$product->product_name}."
                    ], 400);
                }

                $product->remaining_stock = $newStock;
                $product->save();

                // Update product status based on threshold
                $this->updateProductStatus($product);
            }

            // Approve invoice
            $invoice->invoice_status = 'approved';
            $invoice->discount_approved = 1;
            $invoice->save();

            DB::commit();

            return response()->json(['success' => 'Invoice approved and stock updated successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating stock: '.$e->getMessage());
            return response()->json(['error' => 'Approval failed.'], 500);
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'password' => 'required'
        ]);

        $user = auth()->user();

        if(!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid password.'], 422);
        }

        Invoice::whereIn('id', $request->ids)
            ->where('invoice_status', 'pending')
            ->update(['invoice_status' => 'approved', 'approved_by' => $user->id, 'approved_at' => now()]);

        return response()->json(['success' => 'Invoices approved successfully.']);
    }


     /**
     * Helper function to update product stock status
     */
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
