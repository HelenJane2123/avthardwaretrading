<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\Purchase;
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
        $totalCollections = Collection::sum('amount_paid');
        $totalCustomer = Customer::count();
        $totalSales = Invoice::whereIn('invoice_status', [
                    'printed'
                ])->sum('grand_total');
        $totalPurchases = Purchase::where('is_completed', 1)
                    ->where('is_approved', 1)
                    ->sum('grand_total');
        $latestSales = Invoice::with('customer')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        $recentProducts = Product::latest()
                ->take(5)
                ->get();
        $estimatedIncome = $totalSales - $totalPurchases;

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
            ->take(20)
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

        // First 3 months Estimated Income (stored procedure)
        $startDate = Carbon::now()->startOfYear()->toDateString();
        $endDate   = Carbon::now()->endOfYear()->toDateString();

        $monthlyEstimatedIncome = DB::select(
            'CALL sp_monthly_estimated_income(?, ?)',
            [$startDate, $endDate]
        );

        $topStores = DB::table('invoices as sales')
            ->join('customers', 'sales.customer_id', '=', 'customers.id') // or stores table
            ->select(
                'customers.name as store_name',
                DB::raw('SUM(sales.grand_total) as total_sales')
            )
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_sales')
            ->limit(20)
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
            'recentCollections' => $recentCollections,
            'monthlyEstimatedIncome' => $monthlyEstimatedIncome,
            'topStores'         => $topStores,
            'totalPurchases'    => $totalPurchases,
            'estimatedIncome'   => $estimatedIncome
        ]);
    }


    public function edit_profile(){
         return view('profile.edit_profile');
    }

    public function update_profile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'f_name' => 'required|string|max:50',
            'l_name' => 'required|string|max:50',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'current_password' => 'nullable|required_with:new_password,new_password_confirmation|min:8',
            'new_password' => 'nullable|required_with:current_password,new_password_confirmation|min:8|different:current_password|confirmed',
        ], [
            'new_password.confirmed' => 'The new password confirmation does not match.',
        ]);

        $user->f_name = $validated['f_name'];
        $user->l_name = $validated['l_name'];
        $user->email = $validated['email'];

        if ($request->hasFile('image')) {
            $oldImage = $user->image;
            if ($oldImage && file_exists(public_path('images/user/' . $oldImage))) {
                unlink(public_path('images/user/' . $oldImage));
            }

            $imageName = time() . '_' . preg_replace('/\s+/', '_', $request->file('image')->getClientOriginalName());
            $request->file('image')->move(public_path('images/user'), $imageName);
            $user->image = $imageName;
        }

        if (!empty($request->new_password)) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->with('error', 'Incorrect current password');
            }

            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Your profile has been updated successfully.');
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
