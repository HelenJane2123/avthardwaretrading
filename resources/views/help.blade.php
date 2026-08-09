@extends('layouts.master')

@section('content')
<main class="container py-4">
    <div class="app-title">
        <h1>AVT Hardware System Help</h1>
        <p class="mb-0">A step-by-step user guide for using the system from Dashboard to Reports.</p>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="tile mb-4">
                <h3 class="tile-title">Getting Started</h3>
                <p>Use the left navigation menu to access core modules. The system is designed to help you manage products, purchases, invoices, and reports in one place.</p>
                <ol>
                    <li><strong>Dashboard:</strong> Check key summary metrics, recent sales, stock alerts, and pending actions.</li>
                    <li><strong>Product Inventory:</strong> Add, update, or deactivate products. Only active products appear in invoices and purchases.</li>
                    <li><strong>Purchase Orders:</strong> Create purchase orders using supplier products, enter remarks when required, then save or print.</li>
                    <li><strong>Invoices:</strong> Build invoices by selecting active products, entering quantities, and applying discounts or shipping charges.</li>
                    <li><strong>Reports:</strong> Generate summaries for sales, inventory, suppliers, customers, and collections.</li>
                </ol>
            </div>

            <div class="tile mb-4">
                <h3 class="tile-title">Dashboard</h3>
                <p>The dashboard gives you a quick overview of system performance:</p>
                <ul>
                    <li><strong>Sales summary</strong> shows total invoices and revenue for the current period.</li>
                    <li><strong>Stock alerts</strong> highlight low-stock or out-of-stock products.</li>
                    <li><strong>Notifications</strong> help you track purchase orders, recent invoices, and outstanding tasks.</li>
                </ul>
            </div>

            <div class="tile mb-4">
                <h3 class="tile-title">Product Inventory</h3>
                <p>Manage your products and control which items are available for sale.</p>
                <ul>
                    <li><strong>Adding products:</strong> Enter product name, codes, unit price, supplier details, and initial stock.</li>
                    <li><strong>Editing products:</strong> Update pricing, stock, and supplier data from the product list.</li>
                    <li><strong>Deactivating products:</strong> Use the toggle button to deactivate a product. Deactivated products are excluded from invoice and purchase selection.</li>
                    <li><strong>Status badges:</strong> "In Stock", "Low Stock", and "Out of Stock" help you understand inventory health at a glance.</li>
                </ul>
            </div>

            <div class="tile mb-4">
                <h3 class="tile-title">Purchase Orders</h3>
                <p>Use purchase orders to bring stock into inventory from selected suppliers.</p>
                <ul>
                    <li>Select the supplier and product items to build the PO.</li>
                    <li>Ensure each line item has an approved remark if required.</li>
                    <li>Review the order before saving or printing the purchase order.</li>
                    <li>After receiving stock, update product quantities and statuses accordingly.</li>
                </ul>
            </div>

            <div class="tile mb-4">
                <h3 class="tile-title">Invoice Management</h3>
                <p>Use invoices to record sales and calculate totals accurately.</p>
                <ul>
                    <li>Open the product selection modal, then search by product code or name.</li>
                    <li>Only active products with stock are shown in the modal.</li>
                    <li>Choose a product and enter the quantity. The system prevents duplicates.</li>
                    <li>Apply discounts, shipping, and other charges before submitting.</li>
                </ul>
            </div>

            <div class="tile mb-4">
                <h3 class="tile-title">Reports</h3>
                <p>Reports help you review activity, monitor inventory, and make decisions.</p>
                <div class="row gx-3 gy-3">
                    <div class="col-md-6">
                        <div class="tile p-3 h-100">
                            <h5><i class="fa fa-chart-line text-primary"></i> Sales Reports</h5>
                            <p class="mb-2">Analyze revenue by date, customer, or product to identify top performers and sales trends.</p>
                            <a href="{{ route('reports.sales_report') }}" class="btn btn-outline-primary btn-sm">Open Sales Report</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="tile p-3 h-100">
                            <h5><i class="fa fa-boxes text-primary"></i> Inventory Reports</h5>
                            <p class="mb-2">Track stock levels, view low-stock and out-of-stock items, and keep inventory healthy.</p>
                            <a href="{{ route('reports.inventory_report') }}" class="btn btn-outline-primary btn-sm">Open Inventory Report</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="tile p-3 h-100">
                            <h5><i class="fa fa-truck text-primary"></i> Supplier & Purchase Reports</h5>
                            <p class="mb-2">Review purchase history and supplier performance to manage stock replenishment.</p>
                            <a href="{{ route('reports.purchase_report') }}" class="btn btn-outline-primary btn-sm">Open Purchase Report</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="tile p-3 h-100">
                            <h5><i class="fa fa-users text-primary"></i> Customer Reports</h5>
                            <p class="mb-2">View customer purchase behavior and identify valuable clients.</p>
                            <a href="{{ route('reports.customer_report') }}" class="btn btn-outline-primary btn-sm">Open Customer Report</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tile mb-4">
                <h3 class="tile-title">Other Modules</h3>
                <p>Access additional system modules that support collections and payment adjustments.</p>
                <div class="row gx-3 gy-3">
                    <div class="col-md-6">
                        <div class="tile p-3 h-100">
                            <h5><i class="fa fa-file-invoice-dollar text-primary"></i> Collections Module</h5>
                            <p class="mb-2">Use the collection module to manage incoming payments, link them to invoices, and track payment status.</p>
                            <a href="{{ route('collection.index') }}" class="btn btn-outline-primary btn-sm">Open Collection Module</a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="tile p-3 h-100">
                            <h5><i class="fa fa-exchange-alt text-primary"></i> Collection Adjustments</h5>
                            <p class="mb-2">Record adjustments to collection entries and update payment records accurately.</p>
                            <a href="{{ route('adjustment_collection.index') }}" class="btn btn-outline-primary btn-sm">Open Adjustments</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tile mb-4">
                <h3 class="tile-title">Common Troubleshooting</h3>
                <ul>
                    <li><strong>Problem:</strong> Search returns no products. <strong>Fix:</strong> Make sure the product is active and has remaining stock.</li>
                    <li><strong>Problem:</strong> Cannot edit a product. <strong>Fix:</strong> Deactivated products cannot be edited from the product list.</li>
                    <li><strong>Problem:</strong> Missing invoice line item. <strong>Fix:</strong> Ensure the product is not deactivated and the quantity does not exceed available stock.</li>
                    <li><strong>Problem:</strong> Unable to log in. <strong>Fix:</strong> Ask the Super Admin to reset your password.</li>
                </ul>
            </div>

            <div class="text-center">
                <a href="{{ route('home') }}" class="btn btn-primary">Return to Dashboard</a>
            </div>
        </div>
    </div>
</main>
@endsection
