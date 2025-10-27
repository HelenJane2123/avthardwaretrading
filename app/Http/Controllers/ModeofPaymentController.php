<?php

namespace App\Http\Controllers;

use App\ModeofPayment;
use Illuminate\Http\Request;

class ModeofPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $modeofpayments = ModeofPayment::all();
        return view('modeofpayment.index', compact('modeofpayments'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('modeofpayment.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $modeofpayment = new ModeofPayment();
        $modeofpayment->name = $request->name;
        $modeofpayment->term = $request->term;
        $modeofpayment->description = $request->description;
        $modeofpayment->is_active = 1; // default to active
        $modeofpayment->save();
        return redirect()->route('modeofpayment.index')->with('message', 'Mode of payment added successfully!');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $modeofpayment = ModeofPayment::findOrFail($id);
        return view('modeofpayment.edit', compact('modeofpayment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $modeofpayment = ModeofPayment::findOrFail($id);

        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $modeofpayment->name = $request->name;
        $modeofpayment->term = $request->term;
        $modeofpayment->description = $request->description;
        $modeofpayment->save();

        return redirect()->route('modeofpayment.index')->with('message', 'Mode of payment updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $modeofpayment = ModeofPayment::findOrFail($id);

        // Check if mode of payment is used in purchases or invoices
        $isUsedInPurchases = $modeofpayment->purchases()->exists();
        $isUsedInInvoices = $modeofpayment->invoices()->exists();

        if ($isUsedInPurchases || $isUsedInInvoices) {
            return redirect()->back()->with('error', 'Cannot delete this mode of payment because it is used in purchases or invoices.');
        }

        // Safe to delete
        $modeofpayment->delete();
        return redirect()->back();
    }

}

