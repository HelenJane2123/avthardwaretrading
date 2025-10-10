<?php

namespace App\Http\Controllers;

use App\Collection;
use App\Invoice;
use App\ModeofPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'invoice_id'  => 'required|exists:invoices,id',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date'=> 'required|date',
            'remarks'     => 'nullable|string|max:255',
        ]);

        // Load invoice (you may optionally lock it for update to avoid race conditions)
        $invoice = Invoice::with('paymentMode')->findOrFail($request->invoice_id);

        // Wrap in transaction to keep things consistent if multiple payments occur concurrently
        DB::transaction(function () use ($request, $invoice, &$newTotalPaid, &$balance, &$paymentStatus, &$invoiceStatus) {

            // Create the collection (payment) record first
            Collection::create([
                'collection_number' => $request->collection_number,
                'invoice_id'   => $invoice->id,
                'customer_id'  => $invoice->customer_id,
                'payment_date' => $request->payment_date,
                'amount_paid'  => $request->amount_paid,
                'remarks'      => $request->remarks,
            ]);

            // Recompute total paid from DB (authoritative)
            $paidTotal = (float) Collection::where('invoice_id', $invoice->id)->sum('amount_paid');

            // Determine balance (never negative)
            $balance = max(0, round((float)$invoice->grand_total - $paidTotal, 2));

            // Determine payment status (use >= to avoid float-equality issues)
            if (round($paidTotal, 2) >= round((float)$invoice->grand_total, 2)) {
                $paymentStatus = 'paid';
            } elseif ($paidTotal > 0 && $balance > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }

            // Overdue check (if still has balance and past due date)
            if ($balance > 0 && now()->greaterThan($invoice->due_date)) {
                $paymentStatus = 'overdue';
            }

            // Determine invoice status (your existing logic)
            $invoiceStatus = $invoice->invoice_status;
            if ($invoice->paymentMode && strtolower($invoice->paymentMode->name) !== 'cash' && $invoiceStatus === 'pending') {
                $invoiceStatus = 'approved';
            }

            // Update invoice
            $invoice->update([
                'outstanding_balance' => $balance,
                'payment_status'      => $paymentStatus,
                'invoice_status'      => $invoiceStatus,
            ]);

            // set values for logging / return if needed
            $newTotalPaid = $paidTotal;
        });

        \Log::info("Invoice ID {$invoice->id} updated: Paid = {$newTotalPaid}, Balance = {$balance}, Payment Status = {$paymentStatus}");

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

    public function showDetails($collectionId)
    {
        $collection = Collection::with(['invoice.customer', 'invoice.paymentMode'])
            ->findOrFail($collectionId);

        return view('collection.partials.details', compact('collection')); 
    }

    public function printReceipt($id)
    {
        $collection = Collection::with(['invoice.customer', 'invoice.paymentMode', 'invoice.collections'])
                        ->findOrFail($id);

        $invoice = $collection->invoice;

        $latestPayment = $invoice->collections->sortByDesc('payment_date')->first();

        $customer = $invoice->customer;
        $paymentMode = $invoice->paymentMode->name ?? 'N/A';
        $allPayments = $invoice->collections;

        $totalPaid = $allPayments->sum('amount_paid');
        $balance = $invoice->grand_total - $totalPaid;

        // Decide if this receipt is the latest
        $isLatest = $collection->id === $latestPayment->id;

        return view('collection.printcollection', compact(
            'collection', 'invoice', 'customer', 'paymentMode', 'allPayments', 'balance', 'isLatest'
        ));
    }
}
