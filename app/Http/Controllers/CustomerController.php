<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Supplier;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerExport;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $customers = Customer::all();
        return view('customer.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('customer.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->merge([
            'tax' => preg_replace('/^(\d{3})(\d{3})(\d{3})$/', '$1-$2-$3', $request->tax)
        ]);

        $request->validate([
            'customer_code' => 'required|unique:customers,customer_code',
            'name' => 'required|min:3|regex:/^[a-zA-Z ]+$/|unique:customers,name',
            'address' => 'required|min:3',
            'mobile' => 'required|digits:11|unique:customers,mobile',
            'email' => 'required|email|unique:customers,email',
            'tax' => 'required|regex:/^\d{3}-\d{3}-\d{3}-\d{3}$/|unique:customers',
            'details' => 'required|min:3',
            'previous_balance' => 'nullable|numeric|min:0',
        ]);

        $customer = new Customer();
        $customer->customer_code = $request->customer_code;
        $customer->name = $request->name;
        $customer->address = $request->address;
        $customer->mobile = $request->mobile;
        $customer->email = $request->email;
        $customer->tax = $request->tax;
        $customer->details = $request->details;
        $customer->previous_balance = $request->previous_balance;
        $customer->save();

        return redirect()->route('customer.index')->with('message', 'Customer added successfully');
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
        $customer = Customer::findOrFail($id);
        return view('customer.edit', compact('customer'));
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
         $request->validate([
            'name' => 'required|min:3|regex:/^[a-zA-Z ]+$/',
            'address' => 'required|min:3',
            'mobile' => 'required|min:3|digits:11',
            'tax' => [
                'required',
                'regex:/^\d{3}-\d{3}-\d{3}-\d{3}$/',
                Rule::unique('customers', 'tax')->ignore($id),
            ],
            'details' => 'required|min:3',
            'previous_balance' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->name = $request->name;
        $customer->address = $request->address;
        $customer->mobile = $request->mobile;
        $customer->tax = $request->tax;
        $customer->details = $request->details;
        $customer->previous_balance = $request->previous_balance;
        $customer->save();

        return redirect()->route('customer.index')->with('message', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = Customer::find($id);
        $customer->delete();
        return redirect()->back();

    }

    /**
     * Export Customer details to excel
     *
     */
    public function export()
    {
        return Excel::download(new CustomerExport, 'avthardwaretrading_customers.xlsx');
    }
}
