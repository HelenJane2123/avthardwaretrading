<?php

namespace App\Http\Controllers;

use App\Collection;
use App\Invoice;
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
        return view('collection.create', compact('invoices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        $totalPaid = $invoice->collections()->sum('amount_paid') + $request->amount_paid;
        $balance   = $invoice->grand_total - $totalPaid;
        $status    = $balance <= 0 ? 'paid' : 'partial';

        Collection::create([
            'invoice_id'     => $invoice->id,
            'customer_id'    => $invoice->customer_id,
            'payment_date'   => now(),
            'amount_paid'    => $request->amount_paid,
            'balance'        => $balance,
            'payment_status' => $status,
            'remarks'        => $request->remarks,
        ]);

        $invoice->update(['status' => $status]);

        return redirect()->route('collection.index')->with('message', 'Collection recorded successfully!');
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
