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
        $invoice = Invoice::with('paymentMode')->findOrFail($request->invoice_id);

        $grandTotal   = $invoice->grand_total;
        $existingPaid = Collection::where('invoice_id', $invoice->id)->sum('amount_paid');
        $newTotalPaid = $existingPaid + $request->amount_paid;
        $balance      = $grandTotal - $newTotalPaid;

        // Determine payment status
        $paymentStatus = 'pending';

        if ($balance <= 0) {
            $paymentStatus = 'paid';
            $balance = 0;
        } elseif ($newTotalPaid > 0 && $balance > 0) {
            $paymentStatus = 'partial';
        }

        if ($balance > 0 && now()->greaterThan($invoice->due_date)) {
            $paymentStatus = 'overdue';
        }

        // Save collection
        Collection::create([
            'invoice_id'     => $invoice->id,
            'customer_id'    => $invoice->customer_id,
            'payment_date'   => $request->payment_date,
            'amount_paid'    => $request->amount_paid,
            'remarks'        => $request->remarks,
        ]);

        // Determine invoice status separately
        $invoiceStatus = $invoice->invoice_status; // keep current status by default

        // If non-cash payment, auto-approve invoice
        $paymentMode = strtolower($invoice->paymentMode->name);
        if ($paymentMode !== 'cash' && $invoiceStatus === 'pending') {
            $invoiceStatus = 'approved';
        }

        // Update invoice
        $invoice->update([
            'outstanding_balance' => $balance,
            'invoice_status'      => $invoiceStatus,
            'payment_status'      => $paymentStatus,
        ]);

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
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $collection->update([
            'amount_paid'  => $request->amount_paid,
            'remarks'      => $request->remarks,
            'payment_date' => now(),
        ]);

        return redirect()->route('collection.index')->with('message', 'Collection updated successfully!');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();
        return redirect()->route('collection.index')->with('message', 'Collection deleted successfully!');
    }
}
