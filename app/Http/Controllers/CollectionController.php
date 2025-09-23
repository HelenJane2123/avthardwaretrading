<?php

namespace App\Http\Controllers;

use App\Collection;
use App\Invoice;
use App\ModeofPayment;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::with([
            'invoice.customer',      // load customer details through invoice
            'invoice.paymentMode'    // load payment method through invoice
        ])->latest()->get();

        return view('collection.index', compact('collections'));
    }

    public function create()
    {
        $invoices = Invoice::with('customer')->get();
        $paymentModes = ModeofPayment::all();
        return view('collection.create', compact('invoices','paymentModes'));
    }

    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'invoice_id'  => 'required|exists:invoices,id',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date'=> 'required|date',
            'remarks'     => 'nullable|string|max:255',
        ]);

        // Load invoice with payment mode
        $invoice = Invoice::with('paymentMode')->findOrFail($request->invoice_id);

        // Calculate total payments so far
        $existingPaid = Collection::where('invoice_id', $invoice->id)->sum('amount_paid');
        $newTotalPaid = $existingPaid + $request->amount_paid;

        // Compute outstanding balance
        $balance = $invoice->grand_total - $newTotalPaid;
        if ($balance < 0) {
            $balance = 0; // Ensure balance is not negative
        }

        // Determine payment status
        if ($balance == 0) {
            $paymentStatus = 'paid';
        } elseif ($newTotalPaid > 0 && $balance > 0) {
            $paymentStatus = 'partial';
        } else {
            $paymentStatus = 'pending';
        }

        // Overdue check
        if ($balance > 0 && now()->greaterThan($invoice->due_date)) {
            $paymentStatus = 'overdue';
        }

        // Create collection record
        Collection::create([
            'collection_number' => $request->collection_number,
            'invoice_id'   => $invoice->id,
            'customer_id'  => $invoice->customer_id,
            'payment_date' => $request->payment_date,
            'amount_paid'  => $request->amount_paid,
            'remarks'      => $request->remarks,
        ]);

        // Determine invoice status
        $invoiceStatus = $invoice->invoice_status; // keep current status by default
        if ($invoice->paymentMode && strtolower($invoice->paymentMode->name) !== 'cash' && $invoiceStatus === 'pending') {
            $invoiceStatus = 'approved';
        }

        // Update invoice with new balances and status
        $invoice->update([
            'outstanding_balance' => $balance,
            'payment_status'      => $paymentStatus,
            'invoice_status'      => $invoiceStatus,
        ]);

        // Optional: Log for debugging
        \Log::info("Invoice ID {$invoice->id} updated: Paid = $newTotalPaid, Balance = $balance, Payment Status = $paymentStatus, Invoice Status = $invoiceStatus");

        return redirect()
            ->route('collection.index')
            ->with('message', 'Collection saved successfully.');
    }



    public function edit(Collection $collection)
    {
        return view('collection.edit', compact('collection'));
    }

    public function update(Request $request, Collection $collection)
    {
        $request->validate([
            'amount_paid'   => 'required|numeric|min:0',
            'payment_date'  => 'required|date',
            'payment_status'=> 'required|string',
        ]);

        // Update collection
        $collection->update([
            'amount_paid'   => $request->amount_paid,
            'remarks'       => $request->remarks,
            'payment_date'  => $request->payment_date,
        ]);

        // Update linked invoice payment status + balance
        $invoice = $collection->invoice;
        if ($invoice) {
            $invoice->payment_status = $request->payment_status;

            // Optionally recalc balance:
            $invoice->outstanding_balance = max(
                $invoice->grand_total - $collection->amount_paid, 
                0
            );

            $invoice->save();
        }

        return redirect()->route('collection.index')
            ->with('message', 'Collection updated successfully!');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();
        return redirect()->route('collection.index')->with('message', 'Collection deleted successfully!');
    }

    public function showDetails($invoiceId)
    {
        $collection = Collection::with(['invoice.customer', 'invoice.paymentMode'])
            ->where('invoice_id', $invoiceId) // 
            ->firstOrFail();

        return view('collection.partials.details', compact('collection')); 
    }

    public function printReceipt($id)
    {
        $collection = Collection::with(['invoice.customer', 'invoice.paymentMode'])
                        ->findOrFail($id);

        $invoice = $collection->invoice;
        $customer = $invoice->customer;
        $paymentMode = $invoice->paymentMode->name ?? 'N/A';
        $paidAmount = $collection->amount_paid;
        $balance = $invoice->grand_total - $invoice->collections()->sum('amount_paid');
        $paymentDate = \Carbon\Carbon::parse($collection->payment_date)->format('M d, Y');

        return view('collection.printcollection', compact(
            'collection', 'invoice', 'customer', 'paymentMode', 'paidAmount', 'balance', 'paymentDate'
        ));
    }
}
