<?php

namespace App\Http\Controllers;

use App\PdcCollection;
use App\Pdc;
use App\Customer;
use App\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PDCCollectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $collections = PdcCollection::with('pdc')->latest()->paginate(10);

        return view('collection.pdc.index', compact('collections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($pdc_id = null)
    {
        $pdc = Pdc::find($pdc_id);

        return view('collection.pdc.create', compact('pdc'));
    }

    /**
     * Store a newly created collection record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pdc_id'        => 'required|exists:pdcs,id',
            'payment_date'  => 'required|date',
            'amount_paid'   => 'required|numeric|min:0',
            'remarks'       => 'nullable|string',
            'received_date' => 'required|date',
        ]);

        $collection = PdcCollection::create([
            'collection_number' => $collectionNumber,
            'amount'            => $request->amount_paid,     // total amount
            'received_date'     => $request->received_date,   // from user
            'remarks'           => $request->remarks,
        ]);

        return redirect()->route('collection.pdc.index')
                        ->with('success', 'PDC Collection added successfully.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $collection = PdcCollection::findOrFail($id);

        return view('collection.pdc.edit', compact('collection'));
    }

    /**
     * Update the specified collection record.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'payment_date'  => 'required|date',
            'amount_paid'   => 'required|numeric|min:0',
            'remarks'       => 'nullable|string',
        ]);

        $collection = PdcCollection::findOrFail($id);

        $collection->update([
            'payment_date'  => $request->payment_date,
            'amount_paid'   => $request->amount_paid,
            'remarks'       => $request->remarks,
        ]);

        return redirect()->route('collection.pdc.index')
                         ->with('success', 'PDC Collection updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy($id)
    {
        $collection = PdcCollection::findOrFail($id);
        $collection->delete();

        return back()->with('success', 'PDC Collection deleted successfully.');
    }

    public function searchPdc(Request $request)
    {
        $search = $request->input('search');

        // Get collection + join invoice + payment mode
        $collection = Collection::with(['invoice.paymentMode'])
            ->where('collection_number', $search)
            ->first();

        if (!$collection) {
            return response()->json(['error' => 'Collection not found'], 404);
        }

        // Check if collection has an invoice
        if (!$collection->invoice) {
            return response()->json(['error' => 'No invoice linked to this collection'], 404);
        }

        $invoice = $collection->invoice;

        // Check if payment mode is PDC/CHECK
        if ($invoice->paymentMode->name !== 'pdc/check') {
            return response()->json(['error' => 'Payment mode is not PDC/Check'], 400);
        }

        return response()->json([
            'collection_number' => $collection->collection_number,
            'invoice_number' => $invoice->invoice_number,
            'client_name' => $invoice->client_name,
            'payment_terms' => $invoice->terms,
            'invoice_total' => $invoice->total,
            'invoice_balance' => $invoice->balance,
        ]);
    }

}
