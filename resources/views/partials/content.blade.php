<style>
/* Section Styling */
.section-container {
  background-color: #fff;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  margin-bottom: 30px;
  overflow: hidden;
}
.section-header {
  font-size: 16px;
  font-weight: bold;
  color: #333;
  padding: 15px 20px;
  border-bottom: 1px solid #e0e0e0;
  position: relative;
  text-transform: uppercase;
}
.section-header::after {
  content: '';
  display: block;
  width: 100%;
  height: 3px;
  background-color: #007bff;
  position: absolute;
  bottom: -1px;
  left: 0;
}
.section-container table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}
.section-container th, .section-container td {
  padding: 12px 20px;
  border: 1px solid #e0e0e0;
  text-align: left;
  vertical-align: middle;
  color: #333;
}
.section-container th {
  background-color: #f5f5f5;
  color: #555;
  font-weight: bold;
}
.section-container tbody tr:last-child td {
  border-bottom: none;
}
.latest-sales-table td a {
  color: #007bff;
  text-decoration: none;
}
.latest-sales-table td a:hover {
  text-decoration: underline;
}
.product-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.product-list-item {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
}
.product-list-item:last-child {
  border-bottom: none;
}
.product-list-item .product-icon {
  font-size: 20px;
  margin-right: 15px;
  color: #666;
}
.product-list-item .product-details {
  flex-grow: 1;
}
.product-list-item .product-name {
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}
.product-list-item .product-category {
  font-size: 12px;
  color: #777;
}
.product-list-item .product-price {
  background-color: #ffc107;
  color: #fff;
  padding: 5px 10px;
  border-radius: 4px;
  font-weight: bold;
  font-size: 14px;
  margin-left: 15px;
}

.dashboard-card {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}
.fade-in {
    animation: fadeIn 0.6s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<main class="app-content">
  <div class="app-title">
    <div>
      <h1><i class="fa fa-dashboard"></i> Dashboard</h1>
    </div>
    <ul class="app-breadcrumb breadcrumb">
      <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i></li>
      <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
    </ul>
  </div>

  {{-- Widgets --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card dashboard-card text-center p-3">
                <i class="fa fa-line-chart fa-2x text-primary mb-2"></i>
                <h6>Total Sales</h6>
                <h4>₱{{ number_format($totalSales ?? 0, 2) }}</h4>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card text-center p-3">
                <i class="fa fa-cubes fa-2x text-warning mb-2"></i>
                <h6>Products</h6>
                <h4>{{ $totalProducts ?? 0 }}</h4>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card text-center p-3">
                <i class="fa fa-users fa-2x text-success mb-2"></i>
                <h6>Customers</h6>
                <h4>{{ $totalCustomer ?? 0 }}</h4>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card text-center p-3">
                <i class="fa fa-truck fa-2x text-danger mb-2"></i>
                <h6>Suppliers</h6>
                <h4>{{ $totalSuppliers ?? 0 }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card text-center p-3">
                <i class="fa fa-money fa-2x text-info mb-2"></i>
                <h6>Total Collections</h6>
                <h4>₱{{ number_format($totalCollected ?? 0, 2) }}</h4>
            </div>
        </div>
    </div>

  {{-- Tables and Lists --}}
  <div class="row">
    <div class="col-lg-6 fade-in">
        <div class="section-container">
            <div class="section-header"><i class="fa fa-cube"></i> Recently Added Products</div>
            <ul class="product-list">
                @foreach($recentProducts as $rp)
                <li class="product-list-item">
                <i class="fa fa-cube product-icon"></i>
                <div class="product-details">
                    <div class="product-name">{{ $rp->product_name }}</div>
                    <div class="product-category">{{ $rp->category->name ?? 'Uncategorized' }}</div>
                </div>
                <div class="product-price">₱{{ number_format($rp->sales_price, 2) }}</div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    

    <div class="col-lg-6 fade-in">
        <div class="section-container">
        <div class="section-header"><i class="fa fa-shopping-cart"></i> Latest Sales</div>
        <table class="latest-sales-table">
            <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total Sale</th>
            </tr>
            </thead>
            <tbody>
            @forelse($latestSales as $sale)
            <tr>
                <td>{{ $sale->id }}</td>
                <td>{{ $sale->customer->name ?? 'Unknown' }}</td>
                <td>{{ $sale->created_at->format('Y-m-d') }}</td>
                <td>₱{{ number_format($sale->grand_total, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center">No recent sales</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
    </div>

    {{-- Lists --}}
    <div class="row mt-4">
        <div class="col-lg-6 fade-in">
        <div class="section-container">
            <div class="section-header"><i class="fa fa-th"></i> Highest Selling Products</div>
                <table>
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Total Sold</th>
                        <th>Total Quantity</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($highestSelling as $p)
                    <tr>
                        <td>{{ $p->product_name }}</td>
                        <td>{{ number_format($p->total_sales, 2) }}</td>
                        <td>{{ $p->total_qty }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
        </div>
    </div>

        <div class="col-lg-6 fade-in">
            <div class="section-container">
                <div class="section-header"><i class="fa fa-cash-register"></i> Recent Collections</div>
                    <table>
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Invoice</th>
                            <th>Amount Paid</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentCollections as $collection)
                        <tr>
                            <td>{{ $collection->id }}</td>
                            <td>{{ $collection->invoice->customer->name ?? 'N/A' }}</td>
                            <td>
                            <a href="{{ route('invoice.show', $collection->invoice->id) }}">
                                {{ $collection->invoice->id }}
                            </a>
                            </td>
                            <td>₱{{ number_format($collection->amount_paid, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($collection->created_at)->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">No recent collections</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

  {{-- Charts --}}
  <div class="row">
    <div class="col-md-6">
      <div class="tile">
        <h3 class="tile-title">Monthly Sales</h3>
        <div id="monthlySalesChart" style="width: 100%; height: 300px;"></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="tile">
        <h3 class="tile-title">Top 5 Sales Product</h3>
        <div id="topSalesChart" style="width: 100%; height: 300px;"></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="tile">
        <h3 class="tile-title">Today's vs. Yesterday's Sales</h3>
        <div id="sales_chart" style="width: 100%; height: 360px;"></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="tile">
        <h3 class="tile-title">Weekly Sales Comparison</h3>
        <div id="weekSalesChart" style="width: 100%; height: 360px;"></div>
      </div>
    </div>
  </div>
</main>
{{-- Google Charts --}}
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
google.charts.load('current', {'packages':['corechart','bar']});

// Monthly Sales
google.charts.setOnLoadCallback(function() {
  var salesData = @json($monthlySales);
  var data = new google.visualization.DataTable();
  data.addColumn('string', 'Month');
  data.addColumn('number', 'Total Sales');
  salesData.forEach(function(sale) { data.addRow([sale.month, sale.total_amount]); });
  var options = { legend: { position: 'bottom' }, curveType: 'function', series: {0:{color:'#28a745'}} };
  new google.visualization.LineChart(document.getElementById('monthlySalesChart')).draw(data, options);
});

// Top 5 Sales
google.charts.setOnLoadCallback(function() {
  var data = new google.visualization.DataTable();
  data.addColumn('string', 'Product');
  data.addColumn('number', 'Sales');
  data.addRows([
    @foreach($formattedTopSales as $sale)
      ['{{ $sale['productName'] }}', {{ $sale['totalSales'] }}],
    @endforeach
  ]);
  new google.visualization.PieChart(document.getElementById('topSalesChart'))
    .draw(data, { pieHole:0.4, colors:['#2196F3','#4CAF50','#FFC107','#9C27B0','#E91E63'] });
});

// Today vs Yesterday
google.charts.setOnLoadCallback(function() {
  var data = google.visualization.arrayToDataTable([
    ['Day','Sales'],
    ['Yesterday', {{ $yesterdaySales }}],
    ['Today', {{ $todaySales }}],
  ]);
  new google.charts.Bar(document.getElementById('sales_chart'))
    .draw(data, google.charts.Bar.convertOptions({ colors:['#1b9e77','#d95f02'] }));
});

// Weekly Comparison
google.charts.setOnLoadCallback(function() {
  var data = google.visualization.arrayToDataTable([
    ['Week','Sales',{ role:'style' }],
    ['This Week', {{ $thisWeekSales }}, 'color:#3366CC'],
    ['Last Week', {{ $lastWeekSales }}, 'color:#DC3912']
  ]);
  new google.visualization.BarChart(document.getElementById('weekSalesChart'))
    .draw(data, { chartArea:{width:'50%'}, hAxis:{minValue:0} });
});
</script>
