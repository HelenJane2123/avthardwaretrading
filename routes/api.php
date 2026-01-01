<?php

use Illuminate\Http\Request;
use App\Models\Purchase;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/po/latest', function () {
    $latest = Purchase::latest('id')->first(); // or ->orderBy('id','desc')->first()
    return response()->json([
        'po_number' => $latest?->po_number
    ]);
});