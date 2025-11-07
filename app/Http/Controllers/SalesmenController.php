<?php

namespace App\Http\Controllers;

use App\Salesman;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalesmenController extends Controller
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
            'salesman_code' => 'required|unique:salesmen,salesman_code',
            'salesman_name' => 'required|min:3|regex:/^[a-zA-Z ]+$/|unique:salesmen,salesman_name',
            'phone'         => 'required|digits:11|unique:salesmen,phone',
            'address'       => 'required|min:3',
            'email'         => 'required|email|unique:salesmen,email',
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
            'salesman_name'=> 'required|min:3|regex:/^[a-zA-Z ]+$/|unique:salesmen,salesman_name,' . $salesman->id,
            'address' => 'required|min:3',
            'phone'   => 'required|digits:11|unique:salesmen,phone,' . $salesman->id,
            'email'   => 'required|email|unique:salesmen,email,' . $salesman->id,
            'status'  => 'required',
        ]);

        $salesman->update($request->only(['salesman_name', 'phone', 'address', 'email', 'status']));

        return redirect()->route('salesmen.index')->with('message', 'Salesman updated successfully.');
    }

    // Delete salesman
    public function destroy($id)
    {
        $salesman = Salesman::findOrFail($id);
        $isUsedInPurchase = $unit->purchases()->exists();

        if ($isUsedInPurchase) {
            return redirect()->back()->with('error', 'Cannot delete this salesman because it is used by existing purchases.');
        }

        $salesman->delete();

        return redirect()->back()->with('message', 'Salesman deleted successfully.');
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
