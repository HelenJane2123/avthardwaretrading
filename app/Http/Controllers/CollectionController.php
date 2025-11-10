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
            'invoice_id'   => 'required|exists:invoices,id',
            'amount_paid'  => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'remarks'      => 'nullable|string|max:255',
            'check_date'   => 'required|date',
            'check_number' => 'nullable|string|max:100',
            'gcash_name'   => 'nullable|string|max:100',
            'gcash_mobile' => 'nullable|string|max:20',
        ]);

        $invoice = Invoice::with('paymentMode')->findOrFail($request->invoice_id);

        // Normalize mode of payment
        $paymentMode = strtolower(trim($invoice->paymentMode->name ?? ''));

        DB::transaction(function () use ($request, $invoice, $paymentMode, &$newTotalPaid, &$balance, &$paymentStatus, &$invoiceStatus) {
            $collectionData = [
                'collection_number' => $request->collection_number,
                'invoice_id'        => $invoice->id,
                'customer_id'       => $invoice->customer_id,
                'payment_date'      => $request->payment_date,
                'last_paid_amount'  => $request->amount_paid,
                'amount_paid'       => $request->amount_paid,
                'remarks'           => $request->remarks,
            ];

            // Match exact database name "PDC/Check" (case-insensitive)
            if ($paymentMode === 'pdc/check') {
                $collectionData['check_date'] = $request->check_date;
                $collectionData['check_number'] = $request->check_number;
            }

            // Handle GCash mode
            if ($paymentMode === 'gcash') {
                $collectionData['gcash_name']   = $request->gcash_name;
                $collectionData['gcash_number'] = $request->gcash_number;
            }

            // Create collection record
            Collection::create($collectionData);

            // Compute total paid
            $paidTotal = (float) Collection::where('invoice_id', $invoice->id)->sum('amount_paid');
            $balance   = max(0, round((float)$invoice->grand_total - $paidTotal, 2));

            // Determine payment status
            if (round($paidTotal, 2) >= round((float)$invoice->grand_total, 2)) {
                $paymentStatus = 'paid';
            } elseif ($paidTotal > 0 && $balance > 0) {
                $paymentStatus = 'partial';
            } else {
                $paymentStatus = 'pending';
            }

            // Mark overdue if balance remains and due date has passed
            if ($balance > 0 && now()->greaterThan($invoice->due_date)) {
                $paymentStatus = 'overdue';
            }

            // Invoice status logic
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

            $newTotalPaid = $paidTotal;
        });

        \Log::info("Invoice ID {$invoice->id} updated: Paid = {$newTotalPaid}, Balance = {$balance}, Payment Status = {$paymentStatus}");

        return redirect()
            ->route('collection.index')
            ->with('message', 'Collection saved successfully.');
    }

    public function edit(Collection $collection)
    {
        $collection->load([
            'invoice.paymentMode',
            'invoice.customer'
        ]);
        return view('collection.edit', compact('collection'));
    }

    public function update(Request $request, Collection $collection)
    {
        $request->validate([
            'amount_paid'    => 'required|numeric|min:0',
            'payment_date'   => 'required|date',
            'payment_status' => 'required|string',
            'remarks'        => 'nullable|string|max:255',
            'check_date'   => 'required|date',
            'check_number'   => 'nullable|string|max:100',
            'gcash_name'     => 'nullable|string|max:100',
            'gcash_mobile'   => 'nullable|string|max:20',
        ]);

        $invoice = $collection->invoice;

        // Determine payment mode
        $paymentMode = strtolower($invoice->paymentMode->name ?? '');

        // Prepare update data
        $updateData = [
            'amount_paid'  => $request->amount_paid,
            'remarks'      => $request->remarks,
            'payment_date' => $request->payment_date,
            'last_paid_amount' => $request->amount_paid
        ];

        // Conditional fields based on payment mode
        if ($paymentMode === 'pdc/check') {
            $updateData['check_date'] = $request->check_date;
            $updateData['check_number'] = $request->check_number;
            $updateData['gcash_name']   = null;
            $updateData['gcash_number'] = null;
        } elseif ($paymentMode === 'gcash') {
            $updateData['gcash_name']   = $request->gcash_name;
            $updateData['gcash_number'] = $request->gcash_number;
            $updateData['check_number'] = null;
            $updateData['check_date'] = null;
        } else {
            // For cash or others, clear all optional fields
            $updateData['check_number'] = null;
            $updateData['gcash_name']   = null;
            $updateData['gcash_number'] = null;
            $updateData['check_date'] = null;
        }

        // Update collection
        $collection->update($updateData);

        // Recalculate total payments and outstanding balance
        if ($invoice) {
            $totalPaid = (float) Collection::where('invoice_id', $invoice->id)->sum('amount_paid');
            $balance   = max(0, round($invoice->grand_total - $totalPaid, 2));

            $invoice->update([
                'outstanding_balance' => $balance,
                'payment_status'      => $request->payment_status,
            ]);
        }

        return redirect()
            ->route('collection.index')
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
