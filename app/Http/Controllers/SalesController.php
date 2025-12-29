<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\Product;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index()
    {
        $sales = Sales::with('product')->get(); // Include products related to sales
        

        return view('sales.index', compact('sales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_sold' => 'required|integer|min:1',
            'sale_date' => 'required|date',
            // Add other validations if needed
        ]);

        DB::transaction(function () use ($request) {
            // Save the sale
            $sale = Sales::create([
                'product_id' => $request->product_id,
                'quantity_sold' => $request->quantity_sold,
                'sale_date' => $request->sale_date,
                // add other fields as needed
            ]);

            // Update product inventory
            $product = Product::find($request->product_id);

            if ($product) {
                // Deduct quantity
                $product->quantity -= $request->quantity_sold;

                // Calculate threshold as 10% of quantity (you can adjust this logic)
                $product->threshold = round($product->quantity * 0.1);

                // Set status
                if ($product->quantity <= 0) {
                    $product->status = 'Out of Stock';
                    $product->quantity = 0; // prevent negative values
                } elseif ($product->quantity <= $product->threshold) {
                    $product->status = 'Low Stock';
                } else {
                    $product->status = 'In Stock';
                }

                $product->save();
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sale recorded and product updated!');
    }
}