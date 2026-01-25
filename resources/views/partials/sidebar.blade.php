<aside class="app-sidebar">
    @if (session('passwordResetNotice'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa fa-check-circle"></i> {{ session('passwordResetNotice') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="app-sidebar__user">
        @php
            $user = Auth::user();
            $userImage = $user && $user->image 
                ? asset('images/user/' . $user->image) 
                : asset('images/default-avatar.png'); // fallback image
        @endphp

        <img width="40px" class="app-sidebar__user-avatar" src="{{ $userImage }}" alt="User Image">

        <div>
            <p class="app-sidebar__user-name">{{ $user ? $user->fullname : 'Guest' }}</p>
        </div>
    </div>

    <ul class="app-menu">
        {{-- Dashboard --}}
        <li>
            <a class="app-menu__item {{ request()->is('/home') ? 'active' : ''}}" href="/home">
                <i class="app-menu__icon fa fa-dashboard"></i><span class="app-menu__label">Dashboard</span>
            </a>
        </li>

        {{-- Inventory --}}
        <li class="app-menu__label text-muted pl-3 mt-3" style="font-size: 12px;">— Inventory —</li>

        {{-- Product --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('product*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-cube"></i><span class="app-menu__label">Inventory</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('product.create') }}"><i class="icon fa fa-plus"></i> New Inventory</a></li>
                <li><a class="treeview-item" href="{{ route('product.index') }}"><i class="icon fa fa-edit"></i> Manage Inventory</a></li>
            </ul>
        </li>

        {{-- Transactions --}}
        <li class="app-menu__label text-muted pl-3 mt-3" style="font-size: 12px;">— Transactions —</li>

        {{-- Invoice --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('invoice*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-file"></i><span class="app-menu__label">Invoice</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('invoice.create') }}"><i class="icon fa fa-plus"></i> Create Invoice</a></li>
                <li><a class="treeview-item" href="{{ route('invoice.index') }}"><i class="icon fa fa-edit"></i> Manage Invoice</a></li>
            </ul>
        </li>

        {{-- View Sales --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('purchase*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-shopping-cart"></i><span class="app-menu__label">Purchase Order</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('purchase.create') }}"><i class="icon fa fa-plus"></i> Create Purchase</a></li>
                <li><a class="treeview-item" href="{{ route('purchase.index') }}"><i class="icon fa fa-edit"></i> Manage Purchase</a></li>
            </ul>
        </li>

        {{-- Collection --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('collection*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-money"></i>
                <span class="app-menu__label">Collection</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('collection.create') }}"><i class="icon fa fa-plus"></i> Create Collection</a></li>
                <li><a class="treeview-item" href="{{ route('collection.index') }}"><i class="icon fa fa-edit"></i> Manage Collection</a></li>
            </ul>
        </li>

        <li class="treeview">
            <a class="app-menu__item {{ request()->is('adjustment_collection*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-exchange"></i>
                <span class="app-menu__label">Collection Adjustment</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('adjustment_collection.create') }}"><i class="icon fa fa-plus"></i> Create Adjustment</a></li>
                <li><a class="treeview-item" href="{{ route('adjustment_collection.index') }}"><i class="icon fa fa-edit"></i> Manage Adjustment</a></li>
            </ul>
        </li>

        <!--<li class="treeview">
            <a class="app-menu__item {{ request()->is('pdc_collection*') ? 'active' : '' }}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-exchange"></i>
                <span class="app-menu__label">Collection PDC</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('pdc.create') }}"><i class="icon fa fa-plus"></i> Create Adjustment</a></li>
                <li><a class="treeview-item" href="{{ route('pdc.index') }}"><i class="icon fa fa-edit"></i> Manage Adjustment</a></li>
            </ul>
        </li>-->

        {{-- Contacts --}}
        <li class="app-menu__label text-muted pl-3 mt-3" style="font-size: 12px;">— Contacts —</li>

        {{-- Supplier --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('supplier*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-truck"></i><span class="app-menu__label">Supplier</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('supplier.create') }}"><i class="icon fa fa-circle-o"></i> Add Supplier</a></li>
                <li><a class="treeview-item" href="{{ route('supplier.index') }}"><i class="icon fa fa-circle-o"></i> Manage Suppliers</a></li>
            </ul>
        </li>

        {{-- Customer --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('customer*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-users"></i><span class="app-menu__label">Customer</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('customer.create') }}"><i class="icon fa fa-circle-o"></i> Add Customer</a></li>
                <li><a class="treeview-item" href="{{ route('customer.index') }}"><i class="icon fa fa-circle-o"></i> Manage Customer</a></li>
            </ul>
        </li>

        {{-- Salesmen --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('salesmen*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-briefcase"></i><span class="app-menu__label">Salesman</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('salesmen.create') }}"><i class="icon fa fa-circle-o"></i> Add Salesman</a></li>
                <li><a class="treeview-item" href="{{ route('salesmen.index') }}"><i class="icon fa fa-circle-o"></i> Manage Salesman</a></li>
            </ul>
        </li>


         {{-- 🛠 Maintenance --}}
        <li class="app-menu__label text-muted pl-3 mt-3" style="font-size: 12px;">— Maintenance —</li>

        {{-- User --}}
        @if(auth()->user()->user_role === 'super_admin')
            <li class="treeview">
                <a class="app-menu__item {{ request()->is('user*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-user"></i><span class="app-menu__label">User</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item" href="{{ route('user.create') }}"><i class="icon fa fa-plus"></i> Add User</a></li>
                    <li><a class="treeview-item" href="{{ route('user.index') }}"><i class="icon fa fa-edit"></i> Manage Users</a></li>
                </ul>
            </li>
        @endif

        {{-- Tax --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('tax*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-percent"></i><span class="app-menu__label">Discount</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('tax.create') }}"><i class="icon fa fa-circle-o"></i> Add Tax</a></li>
                <li><a class="treeview-item" href="{{ route('tax.index') }}"><i class="icon fa fa-circle-o"></i> Manage Tax</a></li>
            </ul>
        </li>

        {{-- Category --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('category*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-th"></i><span class="app-menu__label">Category</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('category.create') }}"><i class="icon fa fa-plus"></i> Create Category</a></li>
                <li><a class="treeview-item" href="{{ route('category.index') }}"><i class="icon fa fa-edit"></i> Manage Category</a></li>
            </ul>
        </li>

        {{-- Unit --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('unit*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-bars"></i><span class="app-menu__label">Unit</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('unit.create') }}"><i class="icon fa fa-circle-o"></i> Add Unit</a></li>
                <li><a class="treeview-item" href="{{ route('unit.index') }}"><i class="icon fa fa-circle-o"></i> Manage Unit</a></li>
            </ul>
        </li>

        {{-- Mode of Payment --}}
        <li class="treeview">
            <a class="app-menu__item {{ request()->is('modeofpayment*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-credit-card"></i><span class="app-menu__label">Mode of Payment</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('modeofpayment.create') }}"><i class="icon fa fa-circle-o"></i> Add Mode of Payment</a></li>
                <li><a class="treeview-item" href="{{ route('modeofpayment.index') }}"><i class="icon fa fa-circle-o"></i> Manage Mode of Payment</a></li>
            </ul>
        </li>

        {{-- Reports --}}
        <li class="app-menu__label text-muted pl-3 mt-3" style="font-size: 12px;">— Reports —</li>

        <li class="treeview">
            <a class="app-menu__item {{ request()->is('reports*') ? 'active' : ''}}" href="#" data-toggle="treeview">
                <i class="app-menu__icon fa fa-bar-chart"></i><span class="app-menu__label">Reports</span>
                <i class="treeview-indicator fa fa-angle-right"></i>
            </a>
            <ul class="treeview-menu">
                <li><a class="treeview-item" href="{{ route('reports.ar_aging_report') }}"><i class="icon fa fa-circle-o"></i> AR Aging</a></li>
                <li><a class="treeview-item" href="{{ route('reports.ap_aging_report') }}"><i class="icon fa fa-circle-o"></i> AP Aging</a></li>
                <li><a class="treeview-item" href="{{ route('reports.inventory_report') }}"><i class="icon fa fa-circle-o"></i> Inventory Report</a></li>
                <li><a class="treeview-item" href="{{ route('reports.collection_report') }}"><i class="icon fa fa-circle-o"></i> Collection Report</a></li>
                <li><a class="treeview-item" href="{{ route('reports.purchase_report') }}"><i class="icon fa fa-circle-o"></i> Purchase Report</a></li>
                <li><a class="treeview-item" href="{{ route('reports.sales_report') }}"><i class="icon fa fa-circle-o"></i> Sales Report</a></li>
                <li><a class="treeview-item" href="{{ route('reports.sales_invoice_summary_report') }}"><i class="icon fa fa-circle-o"></i> Sales Invoice Summary Report</a></li>
                <li><a class="treeview-item" href="{{ route('reports.customer_report') }}"><i class="icon fa fa-circle-o"></i> Customer Report</a></li>
                <li><a class="treeview-item" href="{{ route('reports.supplier_report') }}"><i class="icon fa fa-circle-o"></i> Supplier Report</a></li>
                @if(auth()->user()->user_role === 'super_admin')
                    <li><a class="treeview-item" href="{{ route('reports.estimated_income_report') }}"><i class="icon fa fa-circle-o"></i> Estimated Income Report</a></li>
                @endif
            </ul>
        </li>
    </ul>
</aside>
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
