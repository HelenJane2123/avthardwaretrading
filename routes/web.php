<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ExportSupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ModeofPaymentController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PurchasePaymentController;
use App\Http\Controllers\SalesmenController;
use App\Http\Controllers\AdjustmentCollectionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PDCCollectionController;
 // Don’t forget this too if you're using it
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('landing'); 
});

Auth::routes();

// Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/edit_profile', [HomeController::class, 'edit_profile'])->name('edit_profile');
Route::post('/update_profile/{id}', [HomeController::class, 'update_profile'])->name('update_profile');
Route::get('/password_change/', [HomeController::class, 'update_password'])->name('update_password');
Route::post('/user/{id}/reset-password', [HomeController::class, 'resetPassword'])->name('user.resetPassword');
Route::post('/signout', [LoginController::class, 'logout'])
    ->middleware('web')
    ->name('logout');
    
Route::resource('tax', TaxController::class);
Route::resource('category', CategoryController::class);
Route::resource('unit', UnitController::class);
Route::resource('supplier', SupplierController::class);
Route::resource('customer', CustomerController::class);
Route::resource('product', ProductController::class);
Route::resource('invoice', InvoiceController::class);
Route::resource('purchase', PurchaseController::class);
Route::resource('user', UserController::class);
Route::resource('modeofpayment', ModeofPaymentController::class);
Route::resource('collection', CollectionController::class);
Route::resource('salesmen', SalesmenController::class);
Route::resource('adjustment_collection', AdjustmentCollectionController::class);
Route::resource('pdc', PDCCollectionController::class);

Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
Route::get('/findPrice', [InvoiceController::class, 'findPrice'])->name('findPrice');
Route::get('/findPricePurchase', [PurchaseController::class, 'findPricePurchase'])->name('findPricePurchase');
Route::get('/supplier/{id}/products', [SupplierController::class, 'showProducts'])
    ->name('supplier.supplier-products');
Route::get('/supplier/{id}/info', [SupplierController::class, 'getInfo'])->name('supplier.info');
Route::get('/getproduct/{id}', [ProductController::class, 'getProductInfo']);
Route::get('/po/latest', [PurchaseController::class, 'getLatestPoNumber']);

//suggest product based on user input
Route::get('/products/suggest', [ProductController::class, 'suggest'])->name('products.suggest');
Route::get('/products/suppliers', [ProductController::class, 'suppliers'])->name('products.suppliers');
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/details', [ProductController::class, 'getProductDetails'])->name('products.details');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::post('/import-product', [ImportController::class, 'import_product'])->name('import.product');

//get supplier information and items
Route::get('/supplier/{id}/items', [PurchaseController::class, 'getSupplierItems']);
Route::post('/import-supplier', [ImportController::class, 'import'])->name('import.supplier');

//get customer information
Route::get('/customers/{id}', [InvoiceController::class, 'getCustomerInformation']);

//get purchase details
Route::get('/purchase/{id}/details', [PurchaseController::class, 'showDetails']);
Route::put('/purchase/{id}/approve', [PurchaseController::class, 'approve'])->name('purchase.approve');

//get invoice details
Route::get('/invoices/{id}/details', [InvoiceController::class, 'details'])->name('invoice.details');
Route::patch('/invoice/{id}/status', [InvoiceController::class, 'updateStatus'])->name('invoice.updateStatus');
Route::get('/invoice/{id}/print', [InvoiceController::class, 'print'])->name('invoice.print');
Route::get('/invoices/search', [InvoiceController::class, 'search'])->name('invoices.search');
Route::put('/invoice/{id}/approve', [InvoiceController::class, 'approve'])->name('invoice.approve');

//collection details
Route::get('/collection/{id}/details', [CollectionController::class, 'showDetails'])
    ->name('collection.details');
Route::get('collection/{id}/receipt', [CollectionController::class, 'printReceipt'])->name('collection.receipt');
Route::put('/collection/{id}/approve', [CollectionController::class, 'approve'])->name('collection.approve');

//Export data to excel
Route::get('/export/customers', [ExportController::class, 'exportCustomers'])->name('export.customers');
Route::get('/export/salesman', [ExportController::class, 'exportSalesman'])->name('export.salesman');
Route::get('/export/products', [ExportController::class, 'exportProducts'])->name('export.products');
Route::get('/supplier/{supplier}/products/export', [ExportSupplierController::class, 'exportSupplierProducts'])->name('supplier.supplier-products.export');
Route::get('/export/invoices', [ExportController::class, 'exportInvoices'])->name('export.invoices');
Route::get('/export/purchase', [ExportController::class, 'exportPurchases'])->name('export.purchase');
Route::get('/export/collection', [ExportController::class, 'exportCollections'])->name('export.collections');

//Print pdf receipt
Route::get('/purchase/{id}/print', [PurchaseController::class, 'print'])->name('purchase.print');
Route::get('purchase/{purchase}/payment-info', [PurchasePaymentController::class, 'paymentInfo'])->name('purchase.payment.info');
Route::post('purchase/{purchase}/payment-store', [PurchasePaymentController::class, 'store'])->name('purchase.payment.store');
Route::post('/validate-admin-password', [InvoiceController::class, 'validateAdminPassword'])->name('validate.admin.password');


Route::prefix('reports')->group(function () {
    // AR Aging Report (view in Blade)
    Route::get('ar_aging_report', [ReportController::class, 'ar_aging'])->name('reports.ar_aging_report');
    // AR Aging Report (export to Excel)
    Route::get('ar_aging/export', [ReportController::class, 'exportARAging'])->name('reports.ar_aging_export');
    // Show AP Aging report
    Route::get('ap_aging_report', [ReportController::class, 'ap_aging'])->name('reports.ap_aging_report');
    // Export to Excel
    Route::get('/reports/ap-aging/export', [ReportController::class, 'exportAPAging'])->name('reports.ap_aging_export');
    //Inventory Report
    Route::get('inventory_report', [ReportController::class, 'inventory_report'])->name('reports.inventory_report');
    // Export to Excel
    Route::get('/reports/inventory/export', [ReportController::class, 'exportInventory'])->name('reports.inventory_report_export');
    //Sales Report
    Route::get('sales_report', [ReportController::class, 'sales_report'])->name('reports.sales_report');
    // Export to Excel
    Route::get('/reports/sales/export', [ReportController::class, 'exportSales'])->name('reports.sales_report_export');
    //Customer Report
    Route::get('customer_report', [ReportController::class, 'customer_report'])->name('reports.customer_report');
    // Export to Excel
    Route::get('/reports/customer/export', [ReportController::class, 'exportCustomer'])->name('reports.customer_report_export');
    //Supplier Report
    Route::get('supplier_report', [ReportController::class, 'supplier_report'])->name('reports.supplier_report');
    // Export to Excel
    Route::get('/reports/supplier/export', [ReportController::class, 'exportSupplier'])->name('reports.supplier_report_export');
   // Estimated Income Report
    Route::get('reports/estimated-income', [ReportController::class, 'estimated_income_report'])
        ->name('reports.estimated_income_report');

    // Optional: Export to Excel
    Route::get('reports/estimated-income/export', [ReportController::class, 'exportEstimatedIncome'])
        ->name('reports.estimated_income_export');
    
    Route::get('/reports/purchase-report', [ReportController::class, 'purchase_report'])->name('reports.purchase_report');
    Route::get('reports/purchase_report/export', [ReportController::class, 'exportPurchase'])
        ->name('reports.purchase_report_export');

    Route::get('/reports/collection-report', [ReportController::class, 'collection_report'])->name('reports.collection_report');
    Route::get('reports/collection_report/export', [ReportController::class, 'exportCollection'])
        ->name('reports.collection_report_export');
});


