<?php

namespace App\Http\Controllers;

use App\User;
use App\Sale;
use App\Product;
use App\Supplier;
use App\Invoice;
use App\Collection;
use App\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    // public function index()
    // {
    //     return view('home');
    // }

    public function index()
    {
        // Totals
        $totalProducts  = Product::count();
        $totalSuppliers = Supplier::count();
        $totalInvoices  = Invoice::count();
        $totalCollections = Collection::count();
        $totalCustomer = Customer::count();
        $totalSales = Invoice::sum('grand_total');
        $latestSales = Invoice::with('customer')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        $recentProducts = Product::latest()
                ->take(5)
                ->get();

        // Monthly sales from invoices
        $monthlySales = Invoice::selectRaw('SUM(grand_total) as total_amount, MONTH(created_at) as month')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

        $formattedMonthlySales = [];
        foreach ($monthlySales as $sale) {
            $formattedMonthlySales[] = [
                'month' => \DateTime::createFromFormat('!m', $sale->month)->format('F'),
                'total_amount' => (int) $sale->total_amount
            ];
        }

        // Top 5 products sold (from invoice_items)
        $topProducts = DB::table('invoice_sales')
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $formattedTopSales = [];
        foreach ($topProducts as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $formattedTopSales[] = [
                    'productName' => $product->product_name,
                    'totalSales'  => $item->total_qty,
                ];
            }
        }

        // Today vs Yesterday Sales (from invoices)
        $today       = Carbon::today();
        $yesterday   = Carbon::yesterday();

        $todaySales     = Invoice::whereDate('created_at', $today)->sum('grand_total');
        $yesterdaySales = Invoice::whereDate('created_at', $yesterday)->sum('grand_total');

        // Weekly sales (invoices)
        $thisWeekSales = Invoice::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('grand_total');

        $lastWeekSales = Invoice::whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
            ->sum('grand_total');

        // Total collected payments
        $totalCollected = Collection::sum('amount_paid');

        $highestSelling = DB::table('invoice_sales')
            ->select('product_id', 
                    DB::raw('SUM(qty) as total_qty'),
                    DB::raw('SUM(amount) as total_sales'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();
            

        // Attach product names instead of IDs
        $highestSelling = $highestSelling->map(function($item) {
            $product = Product::find($item->product_id);
            $item->product_name = $product ? $product->product_name : 'Unknown';
            return $item;
        });

        // Latest 5 collections (payments made)
        $recentCollections = Collection::with('invoice.customer') // eager load relationships
            ->latest()
            ->take(5)
            ->get();

        return view('home', [
            'monthlySales'     => $formattedMonthlySales,
            'formattedTopSales'=> $formattedTopSales,
            'totalProducts'    => $totalProducts,
            'totalSuppliers'   => $totalSuppliers,
            'totalInvoices'    => $totalInvoices,
            'totalCollections' => $totalCollections,
            'totalCustomer'    => $totalCustomer,
            'todaySales'       => $todaySales,
            'yesterdaySales'   => $yesterdaySales,
            'thisWeekSales'    => $thisWeekSales,
            'lastWeekSales'    => $lastWeekSales,
            'totalCollected'   => $totalCollected,
            'totalSales'       => $totalSales, 
            'highestSelling'   => $highestSelling,
            'latestSales'      => $latestSales,
            'recentProducts'   => $recentProducts,
            'recentCollections' => $recentCollections
        ]);
    }


    public function edit_profile(){
         return view('profile.edit_profile');
    }

    public function update_profile(Request $request, $id){


        $user = User::find($id);
        $user->f_name = $request->f_name;
        $user->l_name = $request->l_name;
        $user->email = $request->email;

        if ($request->hasFile('image')){
            $image_path ="images/user/".$user->image;
            if (file_exists($image_path)){
                unlink($image_path);
            }
            $imageName =request()->image->getClientOriginalName();
            request()->image->move(public_path('images/user/'), $imageName);
            $user->image = $imageName;
        }

        if ($request->filled(['current_password', 'new_password', 'confirm_password'])) {
            // Validate password change fields
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|different:current_password',
                'confirm_password' => 'required|same:new_password',
            ]);
        
            // Verify if the entered current password matches the actual password
            if (Hash::check($request->current_password, $user->password)) {
                // Check if the new and confirm passwords match
                if ($request->new_password !== $request->confirm_password) {
                    return redirect()->back()->with('error', 'New and confirm passwords do not match');
                }
        
                // Hash and update the new password
                $user->password = Hash::make($request->new_password);
            } else {
                return redirect()->back()->with('error', 'Incorrect current password');
            }
        }
        
        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        // Generate temporary password
        $temporaryPassword = Str::random(8);

        // Update user’s password and flag
        $user->password = Hash::make($temporaryPassword);
        $user->password_reset_flag = true;
        $user->save();

        // Flash a success message
        return redirect()->back()->with('reset_success', "
            <div>
                <p><strong>Temporary password generated:</strong></p>
                <div class='input-group mb-2'>
                    <input type='text' class='form-control' id='tempPasswordField' value='{$temporaryPassword}' readonly>
                    <button class='btn btn-outline-secondary' type='button' onclick='copyTempPassword()'>Copy</button>
                </div>
                <small class='text-muted'>Please copy this password and give it to the user so they can log in and change it.</small>
            </div>
        ");
    }


    public function update_password(){
        return view('profile.password');
    }
}
