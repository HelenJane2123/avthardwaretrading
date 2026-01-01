<?php

namespace App\Http\Controllers;

use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalesmanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Show all salesmen
    public function index()
    {
        $salesmen = Salesman::all();
        return view('salesmen.index', compact('salesmen'));
    }

    // Show the create form
    public function create()
    {
        return view('salesmen.create');
    }

    // Store a new salesman
    public function store(Request $request)
    {
        $request->validate([
            'salesman_code' => 'required|unique:salesman,salesman_code',
            'salesman_name' => 'nullable|min:3|regex:/^[a-zA-Z ]+$/|unique:salesman,salesman_name',
            'phone'         => 'nullable|digits:11|unique:salesman,phone',
            'address'       => 'nullable|min:3',
            'email'         => 'nullable|email|unique:salesman,email',
            'status'        => 'required|boolean',
        ]);

        $salesman = new Salesman();
        $salesman->salesman_code = $request->salesman_code;
        $salesman->salesman_name = $request->salesman_name;
        $salesman->phone = $request->phone;
        $salesman->address = $request->address;
        $salesman->email = $request->email;
        $salesman->status = $request->status;
        $salesman->save();

        return redirect()->route('salesmen.index')->with('message', 'Salesman added successfully.');
    }

    // Edit salesman
    public function edit($id)
    {
        $salesman = Salesman::findOrFail($id);
        return view('salesmen.edit', compact('salesman'));
    }

    // Update salesman
    public function update(Request $request, $id)
    {
        $salesman = Salesman::findOrFail($id);

        $request->validate([
            'salesman_name'=> 'required|min:3|regex:/^[a-zA-Z ]+$/|unique:salesman,salesman_name,' . $salesman->id,
            'address' => 'nullable|min:3',
            'phone'   => 'nullable|digits:11|unique:salesman,phone,' . $salesman->id,
            'email'   => 'nullable|email|unique:salesman,email,' . $salesman->id,
            'status'  => 'required',
        ]);

        $salesman->update($request->only(['salesman_name', 'phone', 'address', 'email', 'status']));

        return redirect()->route('salesmen.index')->with('message', 'Salesman updated successfully.');
    }

    // Delete salesman
    public function destroy($id)
    {
        $salesman = Salesman::findOrFail($id);
        $isUsedInPurchase = $salesman->purchases()->exists();
        $isUsedInInvoice = $salesman->invoices()->exists();

        if ($isUsedInPurchase || $isUsedInInvoice) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete this salesman because it is used by existing purchases/invoices.'
            ]);
        }

        $salesman->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Salesman deleted successfully.'
        ]);
    }

     /**
     * Export Salesman details to excel
     *
     */
    public function export()
    {
        return Excel::download(new SalesmanExport, 'avthardwaretrading_salesman.xlsx');
    }
}
