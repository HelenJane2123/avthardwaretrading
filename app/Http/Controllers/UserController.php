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
        $users = User::all();
        return view('user.index', compact('users'));
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
            'f_name' => 'required|min:3|regex:/^[a-zA-Z ]+$/',
            'l_name' => 'required|min:3|regex:/^[a-zA-Z ]+$/',
            'email' => 'required|email|unique:users',
            'contact' => 'nullable|digits:11',
            'password' => 'required|min:6',
            'user_role' => 'required|in:super_admin,admin,staff',
            'user_status' => 'required|in:active,inactive',
        ]);

        $user = new User();
        $user->f_name = $request->f_name;
        $user->l_name = $request->l_name;
        $user->email = $request->email;
        $user->contact = $request->contact;
        $user->password = bcrypt($request->password);
        $user->user_role = $request->user_role; // Hardcoded role
        $user->user_status = $request->user_status; // Hardcoded status
        $user->save();

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
        // Retrieve the user first
        $user = User::findOrFail($id);

        // Now use $user->id in the validation
        $request->validate([
            'f_name' => 'required|min:3|regex:/^[a-zA-Z ]+$/|unique:users,f_name,' . $user->id,
            'l_name' => 'required|min:3|regex:/^[a-zA-Z ]+$/|unique:users,l_name,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'contact' => 'required|digits:11',
            'user_role' => 'required|in:user,admin,super_admin,staff',
            'user_status' => 'required|in:active,inactive',
        ]);

        // Update user data
        $user->f_name = $request->f_name;
        $user->l_name = $request->l_name;
        $user->email = $request->email;
        $user->contact = $request->contact;
        $user->user_role = $request->user_role;
        $user->user_status = $request->user_status;
        $user->save();

        return redirect()->back()->with('message', 'User updated successfully');
    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect()->back();
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }
}
