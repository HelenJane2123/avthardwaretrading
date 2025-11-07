<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\AdjustmentCollection;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;

class AdjustmentCollectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $adjustments = AdjustmentCollection::orderBy('created_at', 'desc')->get();
        return view('collection.adjustment_collection.index', compact('adjustments'));
    }

    public function search(Request $request)
    {
        $invoice = Invoice::where('invoice_no', $request->invoice_no)->first();
        return response()->json($invoice);
    }

    public function create()
    {
        // Get the latest adjustment
        $latest = AdjustmentCollection::orderBy('id', 'desc')->first();

        if ($latest) {
            // Extract number part and increment (e.g. "ADJ-0005" → 6)
            $number = intval(substr($latest->adjustment_no, 4)) + 1;
        } else {
            $number = 1;
        }

        // Format the new code
        $nextAdjustmentNumber = 'ADJ-' . str_pad($number, 4, '0', STR_PAD_LEFT);

        return view('collection.adjustment_collection.create', compact('nextAdjustmentNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'adjustment_no' => 'required|unique:adjustment_collections',
            'invoice_no' => 'required',
            'entry_type' => 'required',
            'collection_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
        ]);

        AdjustmentCollection::create([
            'adjustment_no' => $request->adjustment_no,
            'invoice_no' => $request->invoice_no,
            'entry_type' => $request->entry_type,
            'collection_date' => $request->collection_date,
            'account_name' => $request->account_name,
            'amount' => $request->amount,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('adjustment_collection.index')
            ->with('message', 'Collection Adjustment Created Successfully!');
    }

    public function edit($id)
    {
        $adjustment = AdjustmentCollection::findOrFail($id);
        return view('collection.adjustment_collection.edit', compact('adjustment'));
    }

    public function update(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'invoice_no' => 'required|string|max:50',
            'entry_type' => 'required|in:Debit,Credit',
            'collection_date' => 'required|date',
            'account_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Find the adjustment by ID
        $adjustment = AdjustmentCollection::findOrFail($id);

        // Update fields
        $adjustment->invoice_no = $request->invoice_no;
        $adjustment->entry_type = $request->entry_type;
        $adjustment->collection_date = $request->collection_date;
        $adjustment->account_name = $request->account_name;
        $adjustment->amount = $request->amount;
        $adjustment->remarks = $request->remarks;
        $adjustment->updated_at = now();

        // Save changes
        $adjustment->save();

        // Redirect back with success message
        return redirect()->route('adjustment_collection.index')
                        ->with('message', 'Collection adjustment updated successfully!');
    }

    public function destroy($id)
    {
        // Find the adjustment by ID or fail
        $adjustment = AdjustmentCollection::findOrFail($id);

        // Delete the record
        $adjustment->delete();

        // Redirect back with a success message
        return redirect()->route('adjustment_collection.index')
                        ->with('message', 'Collection adjustment deleted successfully!');
    }
}
