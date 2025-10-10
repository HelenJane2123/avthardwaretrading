<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PurchasePayment;
use App\Purchase;

class PurchasePaymentController extends Controller
{
    // Return payment info for modal
    public function paymentInfo($id)
    {
        $purchase = Purchase::with('payments')->findOrFail((int)$id);

        $totalPaid = $purchase->payments->sum('amount_paid');
        $outstanding = $purchase->grand_total - $totalPaid;
        $status = $totalPaid < $purchase->grand_total ? 'partial' : 'paid';

        return response()->json([
            'po_number' => $purchase->po_number,
            'outstanding_balance' => number_format($outstanding, 2),
            'payment_status' => $status,
        ]);
    }

    // Store payment
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
        ]);

        // Find the purchase
        $purchase = Purchase::findOrFail($request->purchase_id);

        // Calculate total paid so far
        $totalPaid = $purchase->payments()->sum('amount_paid') + $request->amount_paid;

        // Compute outstanding balance
        $outstandingBalance = $purchase->grand_total - $totalPaid;

        // Save payment including outstanding balance
        PurchasePayment::create([
            'purchase_id' => $request->purchase_id,
            'amount_paid' => $request->amount_paid,
            'outstanding_balance' => $outstandingBalance,
            'payment_date' => $request->payment_date,
            'payment_status' => $request->payment_status
        ]);

        return redirect()->route('purchase.index')
            ->with('message', 'Payment recorded successfully! Outstanding balance: ' . number_format($outstandingBalance, 2));
    }
}
