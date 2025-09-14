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
        $collections = Collection::with('invoice.customer')->latest()->get();
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
        $invoice = Invoice::findOrFail($request->invoice_id);

        // Compute remaining balance
        $totalPaid = Collection::where('invoice_id', $invoice->id)->sum('amount_paid') + $request->amount_paid;
        $balance = $invoice->grand_total - $totalPaid;

        // Determine payment status
        if ($balance <= 0) {
            $status = 'paid';
            $balance = 0;
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        } else {
            $status = 'pending';
        }

        Collection::create([
            'invoice_id'     => $invoice->id,
            'customer_id'    => $invoice->customer_id,
            'payment_date'   => $request->payment_date,
            'amount_paid'    => $request->amount_paid,
            'balance'        => $balance,
            'payment_status' => $status,
            'remarks'        => $request->remarks,
        ]);

        return redirect()->route('collection.index')->with('message', 'Collection saved successfully.');
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
