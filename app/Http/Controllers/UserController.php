<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $user = User::all();
        return view('user.index', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('user.create');
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
            'name' => 'required|min:3|unique:customers|regex:/^[a-zA-Z ]+$/',
            'address' => 'required|min:3',
            'mobile' => 'required|min:3|digits:11',
            'email' => 'required|email|unique:customers',
            'details' => 'required|min:3|',
            'previous_balance' => 'min:3',

        ]);

        $customer = new User();
        $customer->name = $request->name;
        $customer->address = $request->address;
        $customer->mobile = $request->mobile;
        $customer->email = $request->email;
        $customer->details = $request->details;
        $customer->previous_balance = $request->previous_balance;
        $customer->save();

        return redirect()->back()->with('message', 'User added successfully');
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
        $user = User::findOrFail($id);
        return view('user.edit', compact('user'));
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
        // $request->validate([
        //     'name' => 'required|min:3|regex:/^[a-zA-Z ]+$/',
        //     'address' => 'required|min:3',
        //     'mobile' => 'required|min:3|digits:11',
        //     'details' => 'required|min:3|',
        //     'previous_balance' => 'min:3',
        // ]);

        // $customer = Customer::findOrFail($id);
        // $customer->name = $request->name;
        // $customer->address = $request->address;
        // $customer->mobile = $request->mobile;
        // $customer->details = $request->details;
        // $customer->previous_balance = $request->previous_balance;
        // $customer->save();

        // return redirect()->back()->with('message', 'Customer Updated Successfully');
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
}
