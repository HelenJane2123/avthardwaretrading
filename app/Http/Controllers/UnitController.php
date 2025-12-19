<?php

namespace App\Http\Controllers;

use App\Tax;
use App\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $units = Unit::all();
        return view('unit.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('unit.create');
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
            'name' => 'required|min:2|unique:units|regex:#^[a-zA-Z\s.\'/-]+$#',
        ]);

        $unit = new Unit();
        $unit->name = $request->name;
        $unit->slug = Str::slug($request->name);
        $unit->status = 1;
        $unit->save();
        return redirect()->route('unit.index')->with('message', 'Unit Created Successfully');
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
        $unit = Unit::findOrFail($id);
        return view('unit.edit', compact('unit'));
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
            'name' => ["required", "min:2", "regex:/^[a-zA-Z\s.\'\/\-]+$/"],
        ]);

        $unit = Unit::findOrFail($id);
        $unit->name = $request->name;
        $unit->slug = Str::slug($request->name);
        $unit->save();

        return redirect()->route('unit.index')->with('message', 'Unit Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);

        // Check if unit is used in related modules
        $isUsedInPurchase = $unit->purchaseItems()->exists();
        $isUsedInSupplier = $unit->supplierItems()->exists();
        $isUsedInProducts = $unit->products()->exists();
        // If used in purchase or supplier items
        if ($isUsedInPurchase || $isUsedInSupplier || $isUsedInProduct) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete this unit because it is used by existing modules.'
            ]);
        }

        // Safe to delete
        $unit->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Unit deleted successfully.'
        ]);
    }
}
