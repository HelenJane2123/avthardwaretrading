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
        ]);

        // Auto-generate collection number
        $nextNumber = PdcCollection::max('id') + 1;
        $collectionNumber = "COL-" . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        PdcCollection::create([
            'collection_number' => $collectionNumber,
            'pdc_id'            => $request->pdc_id,
            'payment_date'      => $request->payment_date,
            'amount_paid'       => $request->amount_paid,
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

    public function getPdcCollections()
    {
        $collections = Collection::with(['invoice.paymentMode', 'payment'])
            ->whereHas('invoice.paymentMode', function($q) {
                $q->where('name', 'PDC/Check');
            })
            ->get();

        return response()->json($collections);
    }
}
