<style>
/* Page Title */
.app-title h1 {
    font-weight: 700;
    letter-spacing: -0.5px;
}
/* Section Styling */
.section-container {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    border: none;
    padding:20px;
}
.section-header {
    background: linear-gradient(90deg, #f8f9fc, #ffffff);
    font-size: 14px;
    font-weight: 700;
    color: #343a40;
    padding: 16px 20px;
    border-bottom: 1px solid #eef0f5;
    text-transform: uppercase;
}

.section-header::after {
    display: none;
}
.section-container table {
    font-size: 13px;
    width: 100%;
}

.section-container th {
    background: #f9fafc;
    border: none;
    color: #6c757d;
}

.section-container td {
    border: none;
    border-bottom: 1px solid #f1f3f6;
}

.section-container tbody tr:hover {
    background: #f8fbff;
}

.section-container th, .section-container td {
  padding: 12px 20px;
  border: 1px solid #e0e0e0;
  text-align: left;
  vertical-align: middle;
  color: #333;
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
    padding: 16px 20px;
    transition: background .2s ease;
}
.product-list-item:hover {
    background: #f8fbff;
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
.product-list-item {
  background-color: #ffc107;
  color: #fff;
  padding: 5px 10px;
  border-radius: 4px;
  font-weight: bold;
  font-size: 14px;
  margin-left: 15px;
}

.product-price {
    background: linear-gradient(135deg, #ff9f43, #ff7849);
    font-size: 13px;
    border-radius: 20px;
}

.tile {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    padding: 20px;
}

.tile-title {
    font-weight: 700;
    font-size: 15px;
    margin-bottom: 15px;
}

.dashboard-hero {
    background: linear-gradient(135deg, #0f172a 0%, #2563eb 55%, #38bdf8 100%);
    color: #fff;
    border-radius: 20px;
    padding: 24px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    box-shadow: 0 16px 40px rgba(37, 99, 235, 0.18);
}

.dashboard-hero h2 {
    font-size: 1.45rem;
    font-weight: 700;
    margin: 0 0 6px;
}

.dashboard-hero p {
    margin: 0;
    color: rgba(255,255,255,0.9);
}

.dashboard-hero .hero-badge {
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(255,255,255,0.2);
    padding: 10px 14px;
    border-radius: 999px;
    font-size: 0.9rem;
    font-weight: 600;
}

.dashboard-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
    border-radius: 18px;
    box-shadow: 0 12px 26px rgba(15, 23, 42, 0.06);
    border: 1px solid rgba(148, 163, 184, 0.14);
    transition: all .25s ease;
    min-height: 132px;
    padding: 18px;
}

.dashboard-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
}

.dashboard-card .stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    font-size: 1.1rem;
}

.dashboard-card .stat-icon.warning { background: rgba(245, 158, 11, 0.15); color: #b45309; }
.dashboard-card .stat-icon.success { background: rgba(16, 185, 129, 0.15); color: #047857; }
.dashboard-card .stat-icon.danger { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }
.dashboard-card .stat-icon.primary { background: rgba(37, 99, 235, 0.15); color: #2563eb; }
.dashboard-card .stat-icon.info { background: rgba(6, 182, 212, 0.15); color: #0f766e; }

.dashboard-card h6 {
    font-size: 12px;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 5px;
    letter-spacing: 0.04em;
}

.dashboard-card h4 {
    font-weight: 700;
    font-size: 20px;
    margin-bottom: 0;
    color: #0f172a;
}

.section-card {
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    overflow: hidden;
    border: 1px solid rgba(226, 232, 240, 0.9);
}

.section-card .section-header {
    background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
    padding: 16px 20px;
    font-weight: 700;
    color: #334155;
    text-transform: none;
}

.section-card table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.section-card th,
.section-card td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
}

.section-card thead th {
    background: #f8fafc;
    color: #64748b;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.section-card tbody tr:hover {
    background: #f8fbff;
}

.chart-card {
    border-radius: 18px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    border: 1px solid rgba(226, 232, 240, 0.9);
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
  <div class="dashboard-hero fade-in">
      <div>
          <p class="text-uppercase small mb-2" style="letter-spacing: 0.16em; opacity: 0.8;">Business overview</p>
          <h2>Welcome back — your operations are looking healthy.</h2>
          <p>Monitor sales, inventory, collections, and supplier activity from one streamlined dashboard.</p>
      </div>
      <div class="hero-badge"><i class="fa fa-calendar-check-o me-2"></i> Updated {{ now()->format('M d, Y') }}</div>
  </div>

  <div class="row g-3 mb-4">
      <div class="col-lg-3 col-md-6">
          <div class="card dashboard-card">
              <div class="stat-icon warning"><i class="fa fa-cubes"></i></div>
              <h6>Products</h6>
              <h4>{{ $totalProducts ?? 0 }}</h4>
          </div>
      </div>

      <div class="col-lg-3 col-md-6">
          <div class="card dashboard-card">
              <div class="stat-icon success"><i class="fa fa-users"></i></div>
              <h6>Customers</h6>
              <h4>{{ $totalCustomer ?? 0 }}</h4>
          </div>
      </div>

      <div class="col-lg-3 col-md-6">
          <div class="card dashboard-card">
              <div class="stat-icon danger"><i class="fa fa-truck"></i></div>
              <h6>Suppliers</h6>
              <h4>{{ $totalSuppliers ?? 0 }}</h4>
          </div>
      </div>

      <div class="col-lg-3 col-md-6">
          <div class="card dashboard-card">
              <div class="stat-icon primary"><i class="fa fa-line-chart"></i></div>
              <h6>Total Sales</h6>
              <h4>₱{{ number_format($totalSales ?? 0, 2) }}</h4>
          </div>
      </div>

      <div class="col-lg-3 col-md-6">
          <div class="card dashboard-card">
              <div class="stat-icon primary"><i class="fa fa-shopping-cart"></i></div>
              <h6>Total Purchases</h6>
              <h4>₱{{ number_format($totalPurchases ?? 0, 2) }}</h4>
          </div>
      </div>

      <div class="col-lg-3 col-md-6">
          <div class="card dashboard-card">
              <div class="stat-icon warning"><i class="fa fa-dollar"></i></div>
              <h6>Estimated Income</h6>
              <h4>₱{{ number_format($estimatedIncome ?? 0, 2) }}</h4>
          </div>
      </div>

      <div class="col-lg-3 col-md-6">
          <div class="card dashboard-card">
              <div class="stat-icon info"><i class="fa fa-money"></i></div>
              <h6>Total Collections</h6>
              <h4>₱{{ number_format($totalCollected ?? 0, 2) }}</h4>
          </div>
      </div>
  </div>

  
    <div class="row mt-4">
      <div class="col-lg-6 fade-in">
          <div class="section-card">
            <div class="section-header"><i class="fa fa-th me-2"></i> Highest Selling Products</div>
            <div class="table-responsive">
              <table class="latest-sales-table">
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
      </div>
      <div class="col-lg-6 fade-in">
        <div class="section-card">
        <div class="section-header"><i class="fa fa-shopping-cart me-2"></i> Latest Sales</div>
        <div class="table-responsive">
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
                <td>{{ $sale->created_at->format('M d, Y') }}</td>
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
        </div>
    </div>
  </div>

  <div class="row mt-4">
      <div class="col-lg-6 fade-in">
          <div class="section-card">
              <div class="section-header"><i class="fa fa-store me-2"></i> Top 20 Stores by Sales</div>
              <div id="topStoresChart" style="width: 100%; height: 400px;"></div>
          </div>
      </div>
      <div class="col-lg-6 fade-in">
        <div class="section-card">
            <div class="section-header"><i class="fa fa-cash-register me-2"></i> Recent Collections</div>
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Collection Number</th>
                        <th>Amount Paid</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recentCollections as $collection)
                    <tr>
                        <td>{{ $collection->invoice->customer->name ?? 'N/A' }}</td>
                        <td><a href="{{ route('invoice.show', $collection->collection_number) }}">{{ $collection->collection_number }}</a></td>
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
  <div class="row mt-4 g-4">
    <div class="col-md-6">
      <div class="tile chart-card">
        <h3 class="tile-title">First 3 Months Sales</h3>
        <div id="monthlySalesChart" style="width: 100%; height: 300px;"></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="tile chart-card">
        <h3 class="tile-title">Top 20 Sales Product</h3>
        <div id="topSalesChart" style="width: 100%; height: 300px;"></div>
      </div>
    </div>
  </div>
  
  <div class="row g-4 mt-1">
    <div class="col-md-6">
      <div class="tile chart-card">
        <h3 class="tile-title">Monthly Estimated Income</h3>
        <div id="estimatedIncomeChart" style="width: 100%; height: 400px;"></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="tile chart-card">
        <h3 class="tile-title">Weekly Sales Comparison</h3>
        <div id="weekSalesChart" style="width: 100%; height: 360px;"></div>
      </div>
    </div>
  </div>
</main>

{{-- Google Charts --}}
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

  // Top 20 Sales
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
      .draw(data, {
          pieHole: 0.4,
          colors: [
              '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd',
              '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf',
              '#393b79', '#637939', '#8c6d31', '#843c39', '#7b4173',
              '#3182bd', '#31a354', '#756bb1', '#e6550d', '#6baed6'
          ],
          pieSliceText: 'percentage',   
          tooltip: {                  
              text: 'both'
          },
          legend: { position: 'right' }
      });
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


  //get first 3 months estimated income
  function drawEstimatedIncomeChart() {
      // Convert your Laravel $monthlyEstimatedIncome array to a JavaScript object
      var incomeData = @json($monthlyEstimatedIncome);

      // Create a DataTable for Google Charts
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Month');
      data.addColumn('number', 'Estimated Income');

      // Add rows from your data
      incomeData.forEach(function(row) {
          data.addRow([
              row.month,
              parseFloat(row.estimated_income)
          ]);
      });

      // Chart options
      var options = {
          legend: { position: 'bottom' },
          curveType: 'function',
          vAxis: {
              title: 'Estimated Income (₱)',
              format: '₱#,##0.00'
          },
          chartArea: {
              left: 60,
              right: 30,
              top: 30,
              bottom: 50
          },
          series: {
              0: { color: '#17a2b8' } // teal line
          }
      };

      // Draw the chart
      var chart = new google.visualization.LineChart(
          document.getElementById('estimatedIncomeChart')
      );
      chart.draw(data, options);
  }

  google.charts.load('current', { packages: ['corechart'] });
  google.charts.setOnLoadCallback(drawEstimatedIncomeChart);

  google.charts.setOnLoadCallback(drawTopStoresChart);
    function drawTopStoresChart() {
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Store');
      data.addColumn('number', 'Total Sales');

      data.addRows([
          @foreach($topStores as $store)
              ['{{ $store->store_name }}', {{ $store->total_sales }}],
          @endforeach
      ]);

      var options = {
          title: 'Top 20 Stores by Sales',
          chartArea: { width: '60%' },
          hAxis: { title: 'Total Sales (₱)', minValue: 0, format: '₱#,##0.00', currency: 'PHP' },
          vAxis: { title: 'Store' },
          colors: ['#36A2EB'],
          legend: { position: 'none' },
      };

      var chart = new google.visualization.BarChart(document.getElementById('topStoresChart'));
      chart.draw(data, options);
  }
   document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('dashboardCalendar');

      var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          height: 400,
          headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },
          events: [
              // Mock events, replace with real events later
              { title: 'Meeting with Supplier', start: '{{ now()->format('Y-m-d') }}' },
              { title: 'Product Launch: Coffee Beans', start: '{{ now()->addDays(3)->format('Y-m-d') }}' },
              { title: 'Inventory Audit', start: '{{ now()->addDays(7)->format('Y-m-d') }}' }
          ]
      });

      calendar.render();
  });
</script>
