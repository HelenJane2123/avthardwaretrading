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


Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/edit_profile', [HomeController::class, 'edit_profile'])->name('edit_profile');
Route::post('/update_profile/{id}', [HomeController::class, 'update_profile'])->name('update_profile');
Route::get('/password_change/', [HomeController::class, 'update_password'])->name('update_password');

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

//get supplier information and items
Route::get('/supplier/{id}/items', [PurchaseController::class, 'getSupplierItems']);

//get customer information
Route::get('/customers/{id}', [InvoiceController::class, 'getCustomerInformation']);

//get purchase details
Route::get('/purchase/{id}/details', [PurchaseController::class, 'showDetails']);

//get invoice details
Route::get('/invoices/{id}/details', [InvoiceController::class, 'details'])->name('invoice.details');
Route::patch('/invoice/{id}/status', [InvoiceController::class, 'updateStatus'])->name('invoice.updateStatus');
Route::get('/invoice/{id}/print', [InvoiceController::class, 'print'])->name('invoice.print');
Route::get('/invoices/search', [InvoiceController::class, 'search'])->name('invoices.search');

//collection details
Route::get('/collection/{id}/details', [CollectionController::class, 'showDetails']);
Route::get('collection/{id}/receipt', [CollectionController::class, 'printReceipt'])->name('collection.receipt');

//Export data to excel
Route::get('/export/customers', [ExportController::class, 'exportCustomers'])->name('export.customers');
Route::get('/export/products', [ExportController::class, 'exportProducts'])->name('export.products');
Route::get('/supplier/{supplier}/products/export', [ExportSupplierController::class, 'exportSupplierProducts'])->name('supplier.supplier-products.export');

//Print pdf receipt
Route::get('/purchase/{id}/print', [PurchaseController::class, 'print'])->name('purchase.print');

Route::post('/validate-admin-password', [InvoiceController::class, 'validateAdminPassword'])->name('validate.admin.password');


