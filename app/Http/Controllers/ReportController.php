<?php
namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use Carbon\Carbon;
use App\Models\Invoice;

class ReportController extends Controller
{
    /**
     * Show AR Aging Report in Blade
     */
    public function ar_aging(Request $request)
    {
        // Filters
        $startDateInput = $request->input('as_of_date') ?: null;
        $customerId    = $request->input('customer_id') ?: null;
        $paymentModeId = $request->input('payment_mode_id') ?: null;
        $asOfDate = $startDateInput
            ? Carbon::createFromFormat('F d, Y', $startDateInput)->toDateString()
            : now()->startOfMonth()->toDateString();


        // Normalize date to YYYY-MM-DD (fallback to today)
        try {
            $asOfDate = $asOfDate ? \Carbon\Carbon::parse($asOfDate)->toDateString() : now()->toDateString();
        } catch (\Exception $e) {
            $asOfDate = now()->toDateString();
        }

        // Call stored procedure
        $agingData  = DB::select(
            'CALL get_ar_aging(:customerId, :paymentModeId, :asOfDate)',
            [
                'customerId'    => $customerId,
                'paymentModeId' => $paymentModeId,
                'asOfDate'      => $asOfDate,
            ]
        );

        // Provide filter dropdown data (so Blade can render selects)
        $customers = DB::table('customers')->orderBy('name')->get();
        $paymentMethods = DB::table('mode_of_payment')->orderBy('name')->get();

        // Pass everything to the view
        return view('reports.ar_aging_report', compact(
            'agingData',
            'customers',
            'paymentMethods',
            'customerId',
            'paymentModeId',
            'asOfDate'
        ));
    }

    public function exportARAging(Request $request)
    {
        $customerId    = $request->input('customer_id') ?: null;
        $paymentModeId = $request->input('payment_mode_id') ?: null;
        $asOfDate      = $request->input('as_of_date')
                        ? \Carbon\Carbon::parse($request->input('as_of_date'))->toDateString()
                        : now()->toDateString();

        // === Call your stored procedure ===
        $results = DB::select(
            'CALL get_ar_aging(:customerId, :paymentModeId, :asOfDate)',
            [
                'customerId'    => $customerId,
                'paymentModeId' => $paymentModeId,
                'asOfDate'      => $asOfDate,
            ]
        );

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // === HEADER TITLE ===
        $this->addHeader($sheet, 'AR Aging Report');
        $headerRow = 6;

        // === TABLE HEADERS ===
        $headers = [
            'Customer Code','Customer','Invoice #','Invoice Date','Due Date',
            'Invoice Amount','Adjustment Type','Adjustment Amount',
            'Outstanding Balance','Adjusted Outstanding',
            'Amount Paid','Collection Date',
            'Payment Method','Payment Status','Invoice Status'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // === SORT RESULTS ===
        usort($results, fn($a, $b) => strcmp($a->customer_name, $b->customer_name));

        $row = $headerRow + 1;
        $currentCustomer = null;
        $subtotalInvoice = $subtotalAdjust = $subtotalPaid = $subtotalOutstanding = $subtotalAdjusted = 0;
        $grandInvoice = $grandAdjust = $grandPaid = $grandOutstanding = $grandAdjusted = 0;

        foreach ($results as $record) {
            if ($currentCustomer && $currentCustomer !== $record->customer_name) {
                $sheet->setCellValue("C{$row}", "Subtotal for $currentCustomer");
                $sheet->setCellValue("F{$row}", $subtotalInvoice);
                $sheet->setCellValue("H{$row}", $subtotalAdjust);
                $sheet->setCellValue("I{$row}", $subtotalOutstanding);
                $sheet->setCellValue("J{$row}", $subtotalAdjusted);
                $sheet->setCellValue("K{$row}", $subtotalPaid);
                $sheet->getStyle("A{$row}:O{$row}")->getFont()->setBold(true);
                $row++;

                $subtotalInvoice = $subtotalAdjust = $subtotalPaid = $subtotalOutstanding = $subtotalAdjusted = 0;
            }

            $currentCustomer = $record->customer_name;

            // === DATA ROW ===
            $sheet->setCellValue("A{$row}", $record->customer_code ?? '');
            $sheet->setCellValue("B{$row}", $record->customer_name ?? '');
            $sheet->setCellValue("C{$row}", $record->invoice_number ?? '');

            if (!empty($record->invoice_date)) {
                $sheet->setCellValue("D{$row}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                    \Carbon\Carbon::parse($record->invoice_date)->toDateTime()
                ));
            }
            if (!empty($record->due_date)) {
                $sheet->setCellValue("E{$row}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                    \Carbon\Carbon::parse($record->due_date)->toDateTime()
                ));
            }
            if (!empty($record->collection_date)) {
                $sheet->setCellValue("L{$row}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                    \Carbon\Carbon::parse($record->collection_date)->toDateTime()
                ));
            }

            $sheet->setCellValue("F{$row}", $record->invoice_amount ?? 0);
            $sheet->setCellValue("G{$row}", $record->entry_type ?? '');
            $sheet->setCellValue("H{$row}", $record->adjustment_amount ?? 0);
            $sheet->setCellValue("I{$row}", $record->outstanding_balance ?? 0);
            $sheet->setCellValue("J{$row}", $record->adjusted_outstanding_balance ?? 0);
            $sheet->setCellValue("K{$row}", $record->amount_paid ?? 0);
            $sheet->setCellValue("M{$row}", $record->payment_method ?? '');
            $sheet->setCellValue("N{$row}", $record->payment_status ?? '');
            $sheet->setCellValue("O{$row}", $record->invoice_status ?? '');

            // === SUBTOTALS ===
            $subtotalInvoice    += $record->invoice_amount ?? 0;
            $subtotalAdjust     += $record->adjustment_amount ?? 0;
            $subtotalOutstanding+= $record->outstanding_balance ?? 0;
            $subtotalAdjusted   += $record->adjusted_outstanding_balance ?? 0;
            $subtotalPaid       += $record->amount_paid ?? 0;

            $grandInvoice    += $record->invoice_amount ?? 0;
            $grandAdjust     += $record->adjustment_amount ?? 0;
            $grandOutstanding+= $record->outstanding_balance ?? 0;
            $grandAdjusted   += $record->adjusted_outstanding_balance ?? 0;
            $grandPaid       += $record->amount_paid ?? 0;

            $row++;
        }

        // === FINAL SUBTOTAL ===
        if ($currentCustomer) {
            $sheet->setCellValue("C{$row}", "Subtotal for $currentCustomer");
            $sheet->setCellValue("F{$row}", $subtotalInvoice);
            $sheet->setCellValue("H{$row}", $subtotalAdjust);
            $sheet->setCellValue("I{$row}", $subtotalOutstanding);
            $sheet->setCellValue("J{$row}", $subtotalAdjusted);
            $sheet->setCellValue("K{$row}", $subtotalPaid);
            $sheet->getStyle("A{$row}:O{$row}")->getFont()->setBold(true);
            $row++;
        }

        // === GRAND TOTAL ===
        $sheet->setCellValue("C{$row}", "GRAND TOTAL");
        $sheet->setCellValue("F{$row}", $grandInvoice);
        $sheet->setCellValue("H{$row}", $grandAdjust);
        $sheet->setCellValue("I{$row}", $grandOutstanding);
        $sheet->setCellValue("J{$row}", $grandAdjusted);
        $sheet->setCellValue("K{$row}", $grandPaid);
        $sheet->getStyle("A{$row}:O{$row}")->getFont()->setBold(true);

        $lastRow = $row;

        // === DATE FORMATS ===
        foreach (['D','E','L'] as $col) {
            $sheet->getStyle("{$col}".($headerRow+1).":{$col}{$lastRow}")
                ->getNumberFormat()->setFormatCode('[$-en-US]mmmm d, yyyy');
        }

        // === NUMBER FORMATS ===
        foreach (['F','H','I','J','K'] as $col) {
            $sheet->getStyle("{$col}".($headerRow+1).":{$col}{$lastRow}")
                ->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // === BORDER STYLE ===
        $sheet->getStyle("A{$headerRow}:O{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // === EXPORT FILE ===
        $fileName = 'ar_aging_report_'.now()->format('Ymd_His').'.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
    }

    /**
     * Show AR Aging Report in Blade
     */
    public function ap_aging(Request $request)
    {
        // Filters
        $startDateInput = $request->input('as_of_date') ?: null;
        $supplierId    = $request->input('supplier_id') ?: null;
        $paymentModeId = $request->input('payment_id') ?: null;
        $asOfDate = $startDateInput
            ? Carbon::createFromFormat('F d, Y', $startDateInput)->toDateString()
            : now()->startOfMonth()->toDateString();

        try {
            $asOfDate = \Carbon\Carbon::parse($asOfDate)->toDateString();
        } catch (\Exception $e) {
            $asOfDate = now()->toDateString();
        }

        $agingData = DB::select(
            'CALL get_ap_aging(:supplierId, :paymentModeId, :asOfDate)',
            [
                'supplierId'    => $supplierId,
                'paymentModeId' => $paymentModeId,
                'asOfDate'      => $asOfDate,
            ]
        );

        $suppliers = DB::table('suppliers')->orderBy('name')->get();
        $paymentMethods = DB::table('mode_of_payment')->orderBy('name')->get();

        return view('reports.ap_aging_report', compact(
            'agingData', 'suppliers', 'paymentMethods', 'supplierId', 'paymentModeId', 'asOfDate'
        ));
    }

    public function exportAPAging(Request $request)
    {
        $supplierId    = $request->input('supplier_id') ?: null;
        $paymentModeId = $request->input('payment_id') ?: null;
        $asOfDate      = $request->input('as_of_date') 
                            ? \Carbon\Carbon::parse($request->input('as_of_date'))->toDateString() 
                            : now()->toDateString();

        // Call stored procedure
        $results = DB::select(
            'CALL get_ap_aging(:supplierId, :paymentModeId, :asOfDate)',
            [
                'supplierId'    => $supplierId,
                'paymentModeId' => $paymentModeId,
                'asOfDate'      => $asOfDate,
            ]
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add logo/header
        $this->addHeader($sheet, 'AP Aging Report');
        $headerRow = 6;

        // Table headers
        $headers = [
            'Supplier Code','Supplier','Purchase #','Purchase Date','Purchase Amount',
            'Total Paid','Outstanding','Last Payment Date',
            'Payment Method','Payment Term','Aging Bucket'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $row = $headerRow + 1;

        // Group by purchase
        $purchases = [];
        foreach ($results as $record) {
            $purchaseNo = $record->purchase_number;

            if (!isset($purchases[$purchaseNo])) {
                $purchases[$purchaseNo] = [
                    'supplier_code'   => $record->supplier_code,
                    'supplier_name'   => $record->supplier_name,
                    'purchase_number' => $record->purchase_number,
                    'purchase_date'   => $record->purchase_date,
                    'purchase_amount' => $record->purchase_amount,
                    'payment_method'  => $record->payment_method,
                    'payment_term'    => $record->payment_term,
                    'aging_bucket'    => $record->aging_bucket,
                    'total_paid'      => 0,
                    'last_payment_date' => null,
                ];
            }

            // Accumulate payments
            $purchases[$purchaseNo]['total_paid'] += $record->amount_paid ?? 0;

            if ($record->payment_date) {
                if (
                    !$purchases[$purchaseNo]['last_payment_date'] || 
                    $record->payment_date > $purchases[$purchaseNo]['last_payment_date']
                ) {
                    $purchases[$purchaseNo]['last_payment_date'] = $record->payment_date;
                }
            }
        }

        // Totals
        $grandTotalAmount = 0;
        $grandTotalPaid = 0;
        $grandTotalOutstanding = 0;

        // Write rows
        foreach ($purchases as $purchase) {
            $outstanding = $purchase['purchase_amount'] - $purchase['total_paid'];

            $sheet->setCellValue("A{$row}", $purchase['supplier_code']);
            $sheet->setCellValue("B{$row}", $purchase['supplier_name']);
            $sheet->setCellValue("C{$row}", $purchase['purchase_number']);
            $sheet->setCellValue("D{$row}", $purchase['purchase_date']);
            $sheet->setCellValue("E{$row}", $purchase['purchase_amount']);
            $sheet->setCellValue("F{$row}", $purchase['total_paid']);
            $sheet->setCellValue("G{$row}", $outstanding);
            $sheet->setCellValue("H{$row}", $purchase['last_payment_date']);
            $sheet->setCellValue("I{$row}", $purchase['payment_method']);
            $sheet->setCellValue("J{$row}", $purchase['payment_term']);
            $sheet->setCellValue("K{$row}", $purchase['aging_bucket']);

            // Accumulate totals
            $grandTotalAmount += $purchase['purchase_amount'];
            $grandTotalPaid += $purchase['total_paid'];
            $grandTotalOutstanding += $outstanding;

            $row++;
        }

        // Add Grand Totals row
        $sheet->setCellValue("C{$row}", "Grand Totals");
        $sheet->setCellValue("E{$row}", $grandTotalAmount);
        $sheet->setCellValue("F{$row}", $grandTotalPaid);
        $sheet->setCellValue("G{$row}", $grandTotalOutstanding);

        $sheet->getStyle("A{$row}:K{$row}")->getFont()->setBold(true);

        $lastRow = $row;

        // Format dates
        foreach (['D','H'] as $col) {
            $sheet->getStyle("{$col}{$headerRow}:{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('[$-en-US]mmmm d, yyyy');
        }

        // Format amounts
        foreach (['E','F','G'] as $col) {
            $sheet->getStyle("{$col}{$headerRow}:{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Borders
        $sheet->getStyle("A{$headerRow}:K{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Export
        $fileName = 'ap_aging_report_'.now()->format('Ymd_His').'.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
    }

     // Show Inventory Report
    public function inventory_report(Request $request)
    {
        // Filters
        $status = $request->input('status', 'All');
        $category_id = $request->input('category_id') ?: null;
        $supplier_id = $request->input('supplier_id') ?: null;

        // Call stored procedure
        $inventory = DB::select("CALL get_inventory_report(?, ?, ?)", [
            $status,
            $category_id,
            $supplier_id
        ]);

        // Dropdown data
        $categories = DB::table('categories')->select('id', 'name')->get();
        $suppliers  = DB::table('suppliers')->select('id', 'name')->get();

        return view('reports.inventory_report', compact('inventory', 'categories', 'suppliers'));
    }

    public function exportInventory(Request $request)
    {
        $status      = $request->input('status', 'All');
        $category_id = $request->input('category_id') ?: null;
        $supplier_id = $request->input('supplier_id') ?: null;

        // Call stored procedure for inventory report
        $results = DB::select(
            'CALL get_inventory_report(:status, :category_id, :supplier_id)',
            [
                'status'      => $status,
                'category_id' => $category_id,
                'supplier_id' => $supplier_id,
            ]
        );

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->addHeader($sheet, 'Inventory Report');
        $headerRow = 6;
        // Table headers
        $headers = [
            'Product ID',
            'Product Code',
            'Product Name',
            'Category',
            'Unit',
            'Supplier',
            'Supplier Address',
            'Sales Price',
            'Quantity',
            'Threshold',
            'Remaining Stock',
            'Status'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Fill data
        $row = $headerRow + 1;
        foreach ($results as $record) {
            $sheet->setCellValue("A{$row}", $record->product_id ?? '');
            $sheet->setCellValue("B{$row}", $record->product_code ?? '');
            $sheet->setCellValue("C{$row}", $record->product_name ?? '');
            $sheet->setCellValue("D{$row}", $record->category_name ?? '');
            $sheet->setCellValue("E{$row}", $record->unit_name ?? '');
            $sheet->setCellValue("F{$row}", $record->supplier_name ?? '');
            $sheet->setCellValue("G{$row}", $record->supplier_address ?? '');
            $sheet->setCellValue("H{$row}", $record->sales_price ?? 0);
            $sheet->setCellValue("I{$row}", $record->quantity ?? 0);
            $sheet->setCellValue("J{$row}", $record->threshold ?? 0);
            $sheet->setCellValue("K{$row}", $record->remaining_stock ?? 0);
            $sheet->setCellValue("L{$row}", $record->product_status ?? '');
            $row++;
        }

        $lastRow = $row - 1;

        // Format only numeric columns (H, I, J, K)
        foreach (['H','I','J','K'] as $col) {
            $sheet->getStyle("{$col}{$headerRow}:{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Add borders
        $sheet->getStyle("A{$headerRow}:L{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Export
        $fileName = 'inventory_report_'.now()->format('Ymd_His').'.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
    }

    public function sales_report(Request $request)
    {
        $startDateInput = $request->start_date; 
        $endDateInput   = $request->end_date;

        $customerName = $request->customer_id ?? null;
        $productName  = $request->product_id ?? null;
        $salesmanName = $request->salesman_name ?? null;
        $startDate = $startDateInput
            ? Carbon::parse($startDateInput)->toDateString()
            : Carbon::now()->startOfYear()->toDateString(); 

        $endDate = $endDateInput
            ? Carbon::parse($endDateInput)->toDateString()
            : Carbon::now()->toDateString();
        $location    = $request->input('location') ?: null;
    
        // Call stored procedure (customer, product, salesman, start, end)
        $sales = DB::select('CALL get_sales_report(?, ?, ?, ?, ?, ?)', [
            $customerName,
            $productName,
            $salesmanName,
            $startDate,
            $endDate,
            $location
        ]);

        // For filter dropdowns
        $products  = Product::orderBy('product_name')->get();
        $customers = Customer::orderBy('name')->get();

        // Dynamically get unique salesman names from invoices
        $salesmen = DB::table('invoices')
            ->leftJoin('salesman', 'invoices.salesman', '=', 'salesman.id')
            ->select('invoices.salesman', 'salesman.salesman_name') 
            ->whereNotNull('invoices.salesman')
            ->distinct()
            ->orderBy('salesman.salesman_name')
            ->get();

        $locations = Customer::select('location')->distinct()->orderBy('location')->get();
        return view('reports.sales_report', compact(
            'sales',
            'products',
            'customers',
            'salesmen',
            'locations',
        ));
    }

    /**
     * Export sales report to Excel.
     */
    public function exportSales(Request $request) 
    {
        $customerId = $request->input('customer_id');
        $productId  = $request->input('product_id');
        $salesmanId = $request->input('salesman_name');
        $location   = $request->input('location');

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->toDateString()
            : now()->startOfYear()->toDateString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->toDateString()
            : now()->toDateString();

        $sales = DB::select('CALL get_sales_report(?, ?, ?, ?, ?, ?)', [
            $customerId,
            $productId,
            $salesmanId,
            $startDate,
            $endDate,
            $location
        ]);

        $customerName = $customerId
            ? DB::table('customers')->where('id', $customerId)->value('name')
            : 'All Customers';

        $productName = $productId
            ? DB::table('products')->where('id', $productId)->value('product_name')
            : 'All Products';

        $salesmanName = $salesmanId
            ? DB::table('salesman')->where('id', $salesmanId)->value('salesman_name')
            : 'All Salesmen';

        $locationName = $location ?: 'All Locations';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sales Report');

        $sheet->mergeCells('A1:S1');
        $sheet->setCellValue('A1', "AVT Hardware Trading - Sales Report");

        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F2937']],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->fromArray([
            ['Date From:', Carbon::parse($startDate)->format('F j, Y')],
            ['Date To:', Carbon::parse($endDate)->format('F j, Y')],
            ['Customer:', $customerName],
            ['Product:', $productName],
            ['Salesman:', $salesmanName],
            ['Location:', $locationName],
        ], null, 'A3');

        $sheet->getStyle('A3:A8')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE5E7EB']],
        ]);

        $sheet->getStyle('A3:B8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', 'B') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header
        $headerRow = 10;

        $headers = [
            'Invoice #','Customer','Payment Method','Invoice Date','Due Date','Salesman','Location','Description',
            'Qty','Price','Total','Discount 1','Discount 2','Discount 3','Discount Type','Discount %','Discount Amount','Sub Total', 'Grand Total'
        ];

        $sheet->fromArray($headers, null, "A{$headerRow}");

        $sheet->getStyle("A{$headerRow}:S{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F2937']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $sheet->freezePane("A" . ($headerRow + 1));

        $row = $headerRow + 1;
        $invoiceTotals = [];
        $currentInvoice = null;
        $invoiceStartRow = $row;

        foreach ($sales as $record) {
            if ($currentInvoice !== null && $currentInvoice != $record->invoice_number) {
                // Add per-invoice total
                $sheet->setCellValue("R{$row}", "Grand Total:");
                $sheet->setCellValue("S{$row}", "=SUM(R{$invoiceStartRow}:R".($row-1).")");

                $sheet->getStyle("S{$row}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("R{$row}:S{$row}")->getFont()->setBold(true);
                $sheet->getStyle("R{$row}:S{$row}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEFEFEF');

                // Save this invoice total for final GRAND TOTAL
                $invoiceTotals[] = "S{$row}";

                $row++;
                $invoiceStartRow = $row;
            }
            $currentInvoice = $record->invoice_number;

            $qty   = (float) $record->quantity;
            $price = (float) $record->price;

            $discount1 = (float) ($record->discount_1 ?? 0);
            $discount2 = (float) ($record->discount_2 ?? 0);
            $discount3 = (float) ($record->discount_3 ?? 0);

            $discountType = $record->discount_less_add ?? 'less';

            $sheet->fromArray([
                $record->invoice_number,
                $record->customer_name,
                $record->payment_method,
                $record->sale_date,
                $record->due_date,
                $record->salesman_name,
                $record->location,
                $record->product_name,
                $qty,
                $price,
                '',
                $discount1,
                $discount2,
                $discount3,
                $discountType,
                '',
                '',
                '' // R column
            ], null, "A{$row}");

            $sheet->setCellValue("K{$row}", "=I{$row}*J{$row}");

            $sheet->setCellValue(
                "Q{$row}",
                "=K{$row}-(K{$row}*(1-L{$row}/100)*(1-M{$row}/100)*(1-N{$row}/100))"
            );

            // Sub Total becomes Grand Total
            $sheet->setCellValue("R{$row}", "=K{$row}-Q{$row}");

            $sheet->setCellValue(
                "P{$row}",
                "=IF(L{$row}>0,L{$row}&\"%\",\"\")"
                ."&IF(M{$row}>0,IF(L{$row}>0,\", \",\"\")&M{$row}&\"%\",\"\")"
                ."&IF(N{$row}>0,IF(OR(L{$row}>0,M{$row}>0),\", \",\"\")&N{$row}&\"%\",\"\")"
            );

            // Highlight Grand Total Column
            $sheet->getStyle("R{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFFF99');

            $sheet->getStyle("I{$row}:R{$row}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $row++;
        }

        if ($currentInvoice !== null) {
            $sheet->setCellValue("R{$row}", "Grand Total:");
            $sheet->setCellValue("S{$row}", "=SUM(R{$invoiceStartRow}:R".($row-1).")");
            $sheet->getStyle("S{$row}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle("R{$row}:S{$row}")->getFont()->setBold(true);
            $sheet->getStyle("R{$row}:S{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFEFEFEF');

            $invoiceTotals[] = "S{$row}";
            $row++;
        }

        // FINAL GRAND TOTAL: sum only per-invoice totals
        if (count($invoiceTotals) > 0) {
            $sumFormula = implode(",", $invoiceTotals);
            $sheet->setCellValue("R{$row}", "GRAND TOTAL:");
            $sheet->setCellValue("S{$row}", "=SUM({$sumFormula})");
            $sheet->getStyle("S{$row}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle("S{$row}")->getFont()->setBold(true);
            $sheet->getStyle("S{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFCC00');
        }

        $sheet->getStyle("A{$headerRow}:R{$row}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A','S') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Sales_Report_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName);
    }

    public function customer_report(Request $request)
    {
        $status = $request->status ?? null;
        $startDate = $request->start_date ?? null;
        $endDate = $request->end_date ?? null;

        // Call stored procedure
        $customers = DB::select('CALL get_customer_report(?, ?, ?)', [
            $status,
            $startDate,
            $endDate
        ]);

        return view('reports.customer_report', compact('customers'));
    }

    public function exportCustomer(Request $request)
    {
        $status = $request->input('status') ?? null;
        $startDate = $request->input('start_date') ?: null;
        $endDate = $request->input('end_date') ?: null;

        // Call stored procedure for customer report
        $results = DB::select('CALL get_customer_report(?, ?, ?)', [
            $status,
            $startDate,
            $endDate
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add header (company name, address, report title)
        $this->addHeader($sheet, 'Customer Report');

        // Table headers should start at row 6
        $headerRow = 6;
        $headers = [
            'Customer Code',
            'Name',
            'Email',
            'Phone',
            'Status',
            'Date Registered'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Fill data
        $activeCount = 0;
        $inactiveCount = 0;
        $row = $headerRow + 1;

        foreach ($results as $record) {
            $sheet->setCellValue("A{$row}", $record->customer_code ?? '');
            $sheet->setCellValue("B{$row}", $record->name ?? '');
            $sheet->setCellValue("C{$row}", $record->email ?? '');
            $sheet->setCellValue("D{$row}", $record->mobile ?? '');
            $sheet->setCellValue("E{$row}", ($record->status == 1 ? 'Active' : 'Inactive'));

            // Format created_at date
            $dateRegistered = $record->created_at 
                ? \Carbon\Carbon::parse($record->created_at)->format('M d, Y') 
                : '';
            $sheet->setCellValue("F{$row}", $dateRegistered);

            // Track counts
            if ($record->status == 1) {
                $activeCount++;
            } else {
                $inactiveCount++;
            }

            $row++;
        }

        // Add summary rows
        $sheet->setCellValue("D{$row}", "Total Active:");
        $sheet->setCellValue("E{$row}", $activeCount);
        $sheet->getStyle("D{$row}:E{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("D{$row}", "Total Inactive:");
        $sheet->setCellValue("E{$row}", $inactiveCount);
        $sheet->getStyle("D{$row}:E{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("D{$row}", "Grand Total:");
        $sheet->setCellValue("E{$row}", $activeCount + $inactiveCount);
        $sheet->getStyle("D{$row}:E{$row}")->getFont()->setBold(true);

        $lastRow = $row;

        // Add borders for all rows including summary
        $sheet->getStyle("A{$headerRow}:F{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Export Excel file
        $fileName = 'customer_report_'.now()->format('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
    }

    public function supplier_report(Request $request)
    {
        $status = $request->status ?? null;
        $startDate = $request->start_date ?? null;
        $endDate = $request->end_date ?? null;

        // Call stored procedure
        $suppliers = DB::select('CALL get_supplier_report(?, ?, ?)', [
            $status,
            $startDate,
            $endDate
        ]);

        return view('reports.supplier_report', compact('suppliers'));
    }

    public function exportSupplier(Request $request)
    {
        $status = $request->input('status') ?? null;
        $startDate = $request->input('start_date') ?: null;
        $endDate = $request->input('end_date') ?: null;

        // Call stored procedure for supplier report
        $results = DB::select('CALL get_supplier_report(?, ?, ?)', [
            $status,
            $startDate,
            $endDate
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add header (company name, address, report title)
        $this->addHeader($sheet, 'Supplier Report');

        // Table headers should start at row 6
        $headerRow = 6;
        $headers = [
            'Supplier Code',
            'Name',
            'Email',
            'Phone',
            'Status',
            'Date Registered'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Fill data
        $activeCount = 0;
        $inactiveCount = 0;
        $row = $headerRow + 1;

        foreach ($results as $record) {
            $sheet->setCellValue("A{$row}", $record->supplier_code ?? '');
            $sheet->setCellValue("B{$row}", $record->name ?? '');
            $sheet->setCellValue("C{$row}", $record->email ?? '');
            $sheet->setCellValue("D{$row}", $record->mobile ?? '');
            $sheet->setCellValue("E{$row}", ($record->status == 1 ? 'Active' : 'Inactive'));

            // Format created_at date
            $dateRegistered = $record->created_at 
                ? \Carbon\Carbon::parse($record->created_at)->format('M d, Y') 
                : '';
            $sheet->setCellValue("F{$row}", $dateRegistered);

            // Track counts
            if ($record->status == 1) {
                $activeCount++;
            } else {
                $inactiveCount++;
            }

            $row++;
        }

        // Add summary rows
        $sheet->setCellValue("D{$row}", "Total Active:");
        $sheet->setCellValue("E{$row}", $activeCount);
        $sheet->getStyle("D{$row}:E{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("D{$row}", "Total Inactive:");
        $sheet->setCellValue("E{$row}", $inactiveCount);
        $sheet->getStyle("D{$row}:E{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("D{$row}", "Grand Total:");
        $sheet->setCellValue("E{$row}", $activeCount + $inactiveCount);
        $sheet->getStyle("D{$row}:E{$row}")->getFont()->setBold(true);

        $lastRow = $row;

        // Add borders for all rows including summary
        $sheet->getStyle("A{$headerRow}:F{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Export Excel file
        $fileName = 'supplier_report_'.now()->format('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
    }

    public function estimated_income_report(Request $request)
    {
        // Filters
        $startDateInput = $request->start_date; 
        $endDateInput   = $request->end_date;

        $filterType  = $request->input('filter_type', 'monthly'); // weekly, monthly, quarterly, or custom
        $startDate = $startDateInput
            ? Carbon::createFromFormat('F d, Y', $startDateInput)->toDateString()
            : now()->startOfMonth()->toDateString();
        $endDate = $endDateInput
            ? Carbon::createFromFormat('F d, Y', $endDateInput)->toDateString()
            : now()->endOfMonth()->toDateString();
        $customerId  = $request->input('customer_id') ?: null;
        $productId   = $request->input('product_id') ?: null;
        $location    = $request->input('location') ?: null;

        // Call stored procedure (the one you created)
        $reportData = DB::select(
            'CALL sp_generate_estimated_income_report(:filterType, :startDate, :endDate, :customerId, :productId, :location)',
            [
                'filterType' => $filterType,
                'startDate'  => $startDate,
                'endDate'    => $endDate,
                'customerId' => $customerId,
                'productId'  => $productId,
                'location'   => $location,
            ]
        );

        // Dropdown data
        $customers = DB::table('customers')->orderBy('name')->get();
        $products  = DB::table('products')->orderBy('product_name')->get();
        $locations = Customer::select('location')->distinct()->orderBy('location')->get();

        // Pass to view
        return view('reports.estimated_income_report', compact(
            'reportData',
            'customers',
            'products',
            'filterType',
            'startDate',
            'endDate',
            'customerId',
            'productId',
            'locations'
        ));
    }

    public function exportEstimatedIncome(Request $request)
    {
        $startDateInput = $request->start_date; 
        $endDateInput   = $request->end_date;

        $filterType  = $request->input('filter_type', 'monthly');

        $startDate = $startDateInput
            ? Carbon::createFromFormat('F d, Y', $startDateInput)->toDateString()
            : now()->startOfMonth()->toDateString();
        $endDate = $endDateInput
            ? Carbon::createFromFormat('F d, Y', $endDateInput)->toDateString()
            : now()->endOfMonth()->toDateString();

        $customerId  = $request->input('customer_id');
        $productId   = $request->input('product_id');
        $location    = $request->input('location');

        try {
            $reportData = DB::select(
                'CALL sp_generate_estimated_income_report(?, ?, ?, ?, ?, ?)',
                [$filterType, $startDate, $endDate, $customerId, $productId, $location]
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating report: ' . $e->getMessage());
        }

        // ===============================
        // INITIALIZE EXCEL
        // ===============================
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headerRow = 6;

        $this->addHeader($sheet, 'Estimated Income Report');

        // Selling Price merged header
        $sheet->setCellValue("F{$headerRow}", 'Selling Price');
        $sheet->mergeCells("F{$headerRow}:J{$headerRow}");
        $sheet->getStyle("F{$headerRow}:J{$headerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFCCFFCC']], // light green
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Cost of Goods merged header
        $sheet->setCellValue("K{$headerRow}", 'Cost of Goods');
        $sheet->mergeCells("K{$headerRow}:P{$headerRow}");
        $sheet->getStyle("K{$headerRow}:P{$headerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFFCC99']], // light orange
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Estimated Income merged header
        $sheet->setCellValue("Q{$headerRow}", 'Estimated Income');
        $sheet->mergeCells("Q{$headerRow}:R{$headerRow}");
        $sheet->getStyle("Q{$headerRow}:R{$headerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF99CCFF']], // light blue
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Move to next row for column headers
        $headerRow++;

        // ===============================
        // COLUMN HEADERS
        // ===============================
        $headers = [
            'Invoice Number', 
            'Invoice Date', 
            'Customer Name', 
            'Customer Location',
            'Product Name',
            'Qty Sold', 
            'Sale Price', 
            'Sales Discount', 
            'Sales Net Price', 
            'Sales Net Amount',
            'Purchase Number', 
            'Qty Purchased', 
            'Unit Cost', 
            'Purchase Discount', 
            'Purchase Net Price',
            'Purchase Net Amount',
            'Estimated Income', 
            'Profit %'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col.$headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // ===============================
        // HELPER FUNCTION FOR DISCOUNTS
        // ===============================
        $getDiscountText = function(array $discounts, ?string $firstType = null) {
            $discountText = [];

            foreach ($discounts as $index => $discount) {
                // Skip null, 0, or empty string
                if (!empty($discount) && $discount != 0) {
                    $typePart = ($index === 0 && $firstType) ? $firstType . ' ' : '';
                    $discountText[] = $typePart . rtrim((float)$discount, '.0') . '%';
                }
            }

            return implode(', ', $discountText);
        };

        // ===============================
        // POPULATE DATA
        // ===============================
        $row = $headerRow + 1;
        $totalSales = 0;
        $totalIncome = 0;
        $profitSum = 0;

        foreach ($reportData as $record) {
            // Discounts
            $invoiceDiscountText = $getDiscountText(
                [$record->discount_1, $record->discount_2, $record->discount_3],
                $record->discount_less_add 
            );

            $purchaseDiscountText = $getDiscountText(
                [$record->purchase_discount_1, $record->purchase_discount_2, $record->purchase_discount_3],
                $record->purchase_discount_less_add
            );

            // Fill cells
            $sheet->setCellValue("A{$row}", $record->invoice_number);
            $sheet->setCellValue("B{$row}", $record->invoice_date);
            $sheet->setCellValue("C{$row}", $record->customer_name);
            $sheet->setCellValue("D{$row}", $record->customer_location);
            $sheet->setCellValue("E{$row}", $record->product_name);

            $sheet->setCellValue("F{$row}", $record->quantity_sold);
            $sheet->setCellValue("G{$row}", $record->sales_price);
            $sheet->setCellValue("H{$row}", $invoiceDiscountText);
            $sheet->setCellValue("I{$row}", $record->sales_net_price);
            $sheet->setCellValue("J{$row}", $record->sales_gross);

            $sheet->setCellValue("K{$row}", $record->po_number);
            $sheet->setCellValue("L{$row}", $record->quantity_purchased);
            $sheet->setCellValue("M{$row}", $record->unit_cost);
            $sheet->setCellValue("N{$row}", $purchaseDiscountText);
            $sheet->setCellValue("O{$row}", $record->net_price);
            $sheet->setCellValue("P{$row}", $record->purchase_net_of_net);

            $sheet->setCellValue("Q{$row}", $record->estimated_income);
            $sheet->setCellValue("R{$row}", ($record->profit_percentage ?? 0) / 100);

            // Totals
            $totalSales += $record->sales_net_of_net;
            $totalIncome += $record->estimated_income;
            $profitSum += ($record->profit_percentage ?? 0);

            $row++;
        }

        // ===============================
        // SUMMARY
        // ===============================
        $avgProfit = count($reportData) ? $profitSum / count($reportData) : 0;

        $sheet->setCellValue("P{$row}", 'Total Sales:');
        $sheet->getStyle("P{$row}")->getFont()->setBold(true);
        $sheet->setCellValue("Q{$row}", $totalSales);
        $sheet->getStyle("Q{$row}")->getNumberFormat()->setFormatCode('₱#,##0.00');
        $row++;

        $sheet->setCellValue("P{$row}", 'Total Estimated Income:');
        $sheet->getStyle("P{$row}")->getFont()->setBold(true);
        $sheet->setCellValue("Q{$row}", $totalIncome);
        $sheet->getStyle("Q{$row}")->getNumberFormat()->setFormatCode('₱#,##0.00');
        $row++;

        $sheet->setCellValue("P{$row}", 'Average Profit %:');
        $sheet->getStyle("P{$row}")->getFont()->setBold(true);
        $sheet->setCellValue("Q{$row}", $avgProfit / 100);
        $sheet->getStyle("Q{$row}")->getNumberFormat()->setFormatCode('0.00%');

        // ===============================
        // FORMATTING
        // ===============================
        $sheet->getStyle("F".($headerRow+1).":Q{$row}")
            ->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle("R".($headerRow+1).":R{$row}")
            ->getNumberFormat()->setFormatCode('0.00%');

        $sheet->getStyle("A{$headerRow}:R{$row}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // ===============================
        // OUTPUT
        // ===============================
        $fileName = 'estimated_income_report_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
    }

    public function purchase_report(Request $request)
    {
        $productName = $request->product_id ?? null;
        $startDateInput = $request->start_date; 
        $endDateInput   = $request->end_date;
        $supplierName = $request->supplier_id ?? null;

        $startDate = $startDateInput
            ? Carbon::createFromFormat('F d, Y', $startDateInput)->toDateString()
            : now()->startOfMonth()->toDateString();
        $endDate = $endDateInput
            ? Carbon::createFromFormat('F d, Y', $endDateInput)->toDateString()
            : now()->endOfMonth()->toDateString();

        $purchases = DB::select('CALL get_purchase_report(?, ?, ?, ?)', [
            $productName,
            $supplierName,
            $startDate,
            $endDate
        ]);

        $products = Product::orderBy('product_name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('reports.purchase_report', compact('purchases', 'products','suppliers'));
    }

    public function exportPurchase(Request $request)
    {
        $productId  = $request->product_id ?? null;
        $supplierId = $request->supplier_id ?? null;

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->toDateString()
            : now()->startOfYear()->toDateString();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->toDateString()
            : now()->toDateString();

        $purchases = DB::select('CALL get_purchase_report(?, ?, ?, ?)', [
            $productId,
            $supplierId,
            $startDate,
            $endDate
        ]);

        $productName = $productId
            ? DB::table('products')->where('id', $productId)->value('product_name')
            : 'All Products';

        $supplierName = $supplierId
            ? DB::table('suppliers')->where('id', $supplierId)->value('name')
            : 'All Suppliers';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Purchase Report');

        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', "AVT Hardware Trading - Purchase Report");
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F2937']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Filters Info
        $sheet->fromArray([
            ['Date From:', Carbon::parse($startDate)->format('F j, Y')],
            ['Date To:', Carbon::parse($endDate)->format('F j, Y')],
            ['Supplier:', $supplierName],
            ['Product:', $productName],
        ], null, 'A3');

        $sheet->getStyle('A3:A6')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE5E7EB']],
        ]);
        $sheet->getStyle('A3:B6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A','B') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

        // ===== Table Header =====
        $headerRow = 8;
        $headers = [
            'Purchase #','Purchase Date','Supplier','Product','Quantity','Unit Price','Total Amount',
            'Discount Type','Discount 1','Discount 2','Discount 3','Discount Amount','Sub Total','Payment Term','Grand Total'
        ];
        $sheet->fromArray($headers, null, "A{$headerRow}");
        $sheet->getStyle("A{$headerRow}:O{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F2937']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $sheet->freezePane("A" . ($headerRow + 1));

        // ===== Table Data =====
        $row = $headerRow + 1;
        $purchaseTotals = [];
        $currentPurchase = null;
        $purchaseStartRow = $row;

        foreach ($purchases as $record) {
            if ($currentPurchase !== null && $currentPurchase != $record->po_number) {
                // Add per-purchase total
                $sheet->setCellValue("N{$row}", "Grand Total:");
                $sheet->setCellValue("O{$row}", "=SUM(M{$purchaseStartRow}:M{$row})");

                $sheet->getStyle("O{$row}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("N{$row}:O{$row}")->getFont()->setBold(true);
                $sheet->getStyle("N{$row}:O{$row}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEFEFEF');

                // Save this purchase total for final GRAND TOTAL
                $purchaseTotals[] = "O{$row}";

                $row++;
                $purchaseStartRow = $row;
            }
            $currentPurchase = $record->po_number;

            $qty = $record->qty ?? 0;   
            $unitPrice = $record->unit_price ?? 0;

            // Base cells
            $qtyCell = "E{$row}";
            $unitPriceCell = "F{$row}";
            $totalBeforeDiscountCell = "G{$row}";
            $typeCell = "H{$row}";
            $d1Cell = "I{$row}";
            $d2Cell = "J{$row}";
            $d3Cell = "K{$row}";
            $discountAmtCell = "L{$row}";
            $grandTotalCell = "M{$row}";

            // Fill basic info
            $sheet->fromArray([
                $record->po_number ?? '',
                isset($record->purchase_date) ? Carbon::parse($record->purchase_date)->format('M d, Y') : '',
                $record->supplier_name ?? '',
                $record->product_name ?? '',
                $qty,
                $unitPrice,
                $qty * $unitPrice, // Total Amount before discount
                $record->discount_less_add ?? '',
                $record->discount_1 ?? 0,
                $record->discount_2 ?? 0,
                $record->discount_3 ?? 0,
                '', // Discount Amount
                '', // Sub Total
                $record->payment_method ?? '',
            ], null, "A{$row}");

            // Discount Amount = difference caused by sequential discounts
            $sheet->setCellValue($discountAmtCell,
                "=IF({$typeCell}=\"less\","
                    ."{$totalBeforeDiscountCell}-({$totalBeforeDiscountCell}*(1-{$d1Cell}/100)*(1-{$d2Cell}/100)*(1-{$d3Cell}/100)),"
                    ."({$totalBeforeDiscountCell}*(1+{$d1Cell}/100)*(1+{$d2Cell}/100)*(1+{$d3Cell}/100)) - {$totalBeforeDiscountCell}"
                .")"
            );

            // Grand Total = Total Amount before discount ± Discount Amount
            $sheet->setCellValue($grandTotalCell,
                "=IF({$typeCell}=\"less\",{$totalBeforeDiscountCell}-{$discountAmtCell},{$totalBeforeDiscountCell}+{$discountAmtCell})"
            );

            // Format numbers
            $sheet->getStyle("E{$row}:M{$row}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $row++;
        }

        if ($currentPurchase !== null) {
            $sheet->setCellValue("N{$row}", "Grand Total:");
            $sheet->setCellValue("O{$row}", "=SUM(M{$purchaseStartRow}:M{$row})");
            $sheet->getStyle("O{$row}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle("L{$row}:O{$row}")->getFont()->setBold(true);
            $sheet->getStyle("L{$row}:O{$row}")
                ->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFEFEFEF');

            $purchaseTotals[] = "O{$row}";
            $row++;
        }

        // FINAL GRAND TOTAL: sum only per-invoice totals
        if (!empty($purchaseTotals)) {
            $sumFormula = implode(',', $purchaseTotals);

            $sheet->setCellValue("N{$row}", "GRAND TOTAL:");
            $sheet->setCellValue("O{$row}", "=SUM($sumFormula)");

            $sheet->getStyle("O{$row}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $sheet->getStyle("O{$row}")->getFont()->setBold(true);

            $sheet->getStyle("O{$row}")
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFFFCC00');
        }

        $sheet->getStyle("A{$headerRow}:O{$row}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A','O') as $col) { 
            $sheet->getColumnDimension($col)->setAutoSize(true); 
        }

        // Write the spreadsheet to a file and return as download
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Purchase_Report_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName);
    }

    public function collection_report(Request $request)
    {
        $customerId = $request->customer_id ?? null;
        $startDateInput = $request->start_date; 
        $endDateInput   = $request->end_date;
        
        $startDate = $startDateInput
            ? Carbon::createFromFormat('F d, Y', $startDateInput)->toDateString()
            : now()->startOfMonth()->toDateString();
        $endDate = $endDateInput
            ? Carbon::createFromFormat('F d, Y', $endDateInput)->toDateString()
            : now()->endOfMonth()->toDateString();

        $reportData = DB::select('CALL get_collection_report(?, ?, ?)', [
            $customerId,
            $startDate,
            $endDate
        ]);

        $customers = DB::table('customers')->orderBy('name')->get();

        return view('reports.collection_report', compact('reportData','customers'));
    }

    public function exportCollection(Request $request)
    {
        $salesman   = $request->input('salesman') ?? null;
        $customerId = $request->input('customer_id') ?? null;
        $productId  = $request->input('product_id') ?? null;
        $startDate  = $request->input('start_date') ?? null;
        $endDate    = $request->input('end_date') ?? null;

        // Call stored procedure
        $collections = DB::select('CALL get_collection_report(?, ?, ?, ?, ?)', [
            $salesman,
            $customerId,
            $productId,
            $startDate,
            $endDate
        ]);

        // Create Excel sheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Collection Report');

        // Header info
        $sheet->setCellValue('A1', 'AVT Hardware Trading');
        $sheet->setCellValue('A2', 'Collection Report with Adjustments');
        $sheet->setCellValue('A3', 'Date Generated: ' . now()->format('M d, Y'));
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);

        // Table headers
        $headers = [
            'Invoice #',
            'Collection #',
            'Collection Date',
            'Salesman',
            'Customer',
            'Product',
            'Payment Mode',
            'Check Number',
            'Mobile Number',
            'Payment Status',
            'Remarks',
            'Outstanding Balance',
            'Amount Collected (₱)',
            'Adjustment Type',
            'Adjustment Amount (₱)',
            'Adjustment Date',
            'Adjustment Remarks'
        ];

        $col = 'A';
        $headerRow = 5;
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Group data by invoice
        $groupedData = collect($collections)->groupBy('invoice_number');
        $row = $headerRow + 1;
        $grandTotal = 0;

        foreach ($groupedData as $invoiceNumber => $records) {
            $sheet->setCellValue("A{$row}", "Invoice: " . $invoiceNumber);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            $invoiceTotal = 0;

            foreach ($records as $record) {
                $sheet->setCellValue("A{$row}", $record->invoice_number ?? '');
                $sheet->setCellValue("B{$row}", $record->collection_number ?? '');
                $sheet->setCellValue("C{$row}", \Carbon\Carbon::parse($record->collection_date)->format('M d, Y'));
                $sheet->setCellValue("D{$row}", $record->salesman ?? '');
                $sheet->setCellValue("E{$row}", $record->customer_name ?? '');
                $sheet->setCellValue("F{$row}", $record->product_name ?? '');
                $sheet->setCellValue("G{$row}", $record->payment_mode ?? '');
                $sheet->setCellValue("H{$row}", $record->check_number ?? '-');
                $sheet->setCellValue("I{$row}", $record->mobile_number ?? '-');
                $sheet->setCellValue("J{$row}", ucfirst($record->payment_status ?? '-'));
                $sheet->setCellValue("K{$row}", $record->remarks ?? '-');
                $sheet->setCellValue("L{$row}", number_format($record->outstanding_balance ?? 0, 2));
                $sheet->setCellValue("M{$row}", number_format($record->amount_collected ?? 0, 2));

                // New Adjustment Fields
                $sheet->setCellValue("N{$row}", $record->adjustment_type ?? '-');
                $sheet->setCellValue("O{$row}", number_format($record->adjustment_amount ?? 0, 2));
                $sheet->setCellValue("P{$row}", $record->adjustment_date ? \Carbon\Carbon::parse($record->adjustment_date)->format('M d, Y') : '-');
                $sheet->setCellValue("Q{$row}", $record->adjustment_remarks ?? '-');

                $invoiceTotal += $record->amount_collected ?? 0;
                $row++;
            }

            // Invoice subtotal
            $sheet->setCellValue("L{$row}", "Subtotal for {$invoiceNumber}:");
            $sheet->setCellValue("M{$row}", number_format($invoiceTotal, 2));
            $sheet->getStyle("L{$row}:M{$row}")->getFont()->setBold(true);
            $row++;

            $grandTotal += $invoiceTotal;
        }

        // Grand total
        // $sheet->setCellValue("L{$row}", "Grand Total:");
        // $sheet->setCellValue("M{$row}", number_format($grandTotal, 2));
        // $sheet->getStyle("L{$row}:M{$row}")->getFont()->setBold(true);

        // Borders
        $lastCol = 'Q';
        $lastRow = $row;
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Export
        $fileName = 'collection_report_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
    }

    public function sales_invoice_summary_report(Request $request)
    {
        $startDateInput = $request->start_date; 
        $endDateInput   = $request->end_date;
        $startDate = Carbon::now()->startOfYear()->toDateString();
        $endDate = $endDateInput
            ? Carbon::parse($endDateInput)->toDateString()
            : Carbon::now()->toDateString();
        $status       = $request->status ?: null;
        $location     = $request->location ?: null;
        $salesmanId   = $request->salesman ?: null;
        $customerId   = $request->customer_id ?: null;

        $sales = DB::select('CALL sp_invoice_summary_report(?, ?, ?, ?, ?, ?)', [
            $status,
            $location,
            $salesmanId,
            $customerId,
            $startDate,
            $endDate
        ]);

        $customers = Customer::orderBy('name')->get();
        $salesmen = DB::table('invoices')
            ->leftJoin('salesman', 'invoices.salesman', '=', 'salesman.id')
            ->select('invoices.salesman', 'salesman.salesman_name')
            ->whereNotNull('invoices.salesman')
            ->distinct()
            ->orderBy('salesman.salesman_name')
            ->get();

        $locations = Customer::select('location')
            ->distinct()
            ->orderBy('location')
            ->get();

        return view('reports.invoice_summary_report', compact(
            'sales',
            'customers',
            'salesmen',
            'locations'
        ));
    }

    public function exportSalesSummary(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::createFromFormat('F d, Y', $request->start_date)->toDateString()
            : now()->startOfYear()->toDateString();

        $endDate = $request->end_date
            ? Carbon::createFromFormat('F d, Y', $request->end_date)->toDateString()
            : now()->toDateString();

        $status     = $request->status ?: null;
        $location   = $request->location ?: null;
        $salesmanId = $request->salesman ?: null;
        $customerId = $request->customer_id ?: null;

        $results = DB::select('CALL sp_invoice_summary_report(?, ?, ?, ?, ?, ?)', [
            $status,
            $location,
            $salesmanId,
            $customerId,
            $startDate,
            $endDate
        ]);

        $results = collect($results)
            ->sortBy([
                ['customer_name', 'asc'],
                ['invoice_date', 'asc']
            ])->values();

        $customerName = $customerId
            ? DB::table('customers')->where('id', $customerId)->value('name')
            : 'All Customers';

        $salesmanName = $salesmanId
            ? DB::table('salesman')->where('id', $salesmanId)->value('salesman_name')
            : 'All Salesmen';

        $locationName = $location ?: 'All Locations';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /*
        |--------------------------------------------------------------------------
        | SALES SUMMARY SHEET
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', "AVT Hardware Trading - Sales Invoice Summary Report");
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F2937']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->fromArray([
            ['Date From:', Carbon::parse($startDate)->format('F j, Y')],
            ['Date To:', Carbon::parse($endDate)->format('F j, Y')],
            ['Customer:', $customerName],
            ['Salesman:', $salesmanName],
            ['Location:', $locationName],
        ], null, 'A3');

        $sheet->getStyle('A3:A7')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE5E7EB']],
        ]);

        $sheet->getStyle('A3:B7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $sheet->insertNewRowBefore(8, 1);

        $headerRow = 9;
        $headers = [
            'Invoice Number',
            'Invoice Date',
            'Invoice Due Date',
            'Customer Name',
            'Payment Method',
            'Location',
            'Salesman',
            'Total Sales'
        ];

        $sheet->fromArray($headers, null, "A{$headerRow}");

        $sheet->getStyle("A{$headerRow}:H{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F2937']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        foreach (range('A','H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane("A" . ($headerRow + 1));

        $row = $headerRow + 1;
        $grandTotal = 0;
        $currentCustomer = null;
        $subtotal = 0;

        foreach ($results as $record) {
            if ($currentCustomer && $currentCustomer !== $record->customer_name) {
                $sheet->setCellValue("G{$row}", "Subtotal for {$currentCustomer}");
                $sheet->setCellValue("H{$row}", $subtotal);
                $sheet->getStyle("G{$row}:H{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFDE68A']],
                ]);
                $row++;
                $subtotal = 0;
            }
            $currentCustomer = $record->customer_name;
            $paymentMethod = trim(
                ($record->payment_method ?? '') .
                ($record->payment_term ? '-' . $record->payment_term : '')
            );

            $invoiceDate = $record->invoice_date ? Carbon::parse($record->invoice_date)->format('F j, Y') : '';
            $dueDate     = $record->due_date ? Carbon::parse($record->due_date)->format('F j, Y') : '';

            $sheet->setCellValue("A{$row}", $record->dr_no ?? '');
            $sheet->setCellValue("B{$row}", $invoiceDate);
            $sheet->setCellValue("C{$row}", $dueDate);
            $sheet->setCellValue("D{$row}", $record->customer_name ?? '');
            $sheet->setCellValue("E{$row}", $paymentMethod);
            $sheet->setCellValue("F{$row}", $record->location ?? '');
            $sheet->setCellValue("G{$row}", $record->salesman_name ?? '');
            $sheet->setCellValue("H{$row}", $record->grand_total ?? 0);

            $subtotal += $record->grand_total ?? 0;
            $grandTotal += $record->grand_total ?? 0;
            $row++;
        }

        if ($currentCustomer) {
            $sheet->setCellValue("G{$row}", "Subtotal for {$currentCustomer}");
            $sheet->setCellValue("H{$row}", $subtotal);
            $sheet->getStyle("G{$row}:H{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFDE68A']],
            ]);
            $row++;
        }

        $sheet->setCellValue("G{$row}", 'GRAND TOTAL:');
        $sheet->setCellValue("H{$row}", $grandTotal);
        $sheet->getStyle("G{$row}:H{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFBBF24']],
        ]);

        $sheet->getStyle("H" . ($headerRow + 1) . ":H{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $sheet->getStyle("A{$headerRow}:H{$row}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

         /*
        |--------------------------------------------------------------------------
        | COUNTER RECEIPT SHEET
        |--------------------------------------------------------------------------
        */

        $counterSheet = $spreadsheet->createSheet();
        $counterSheet->setTitle('Counter Receipt');

        // ===== COMPANY HEADER =====
        $counterSheet->mergeCells('A1:G1');
        $counterSheet->setCellValue('A1', 'AVT Hardware Trading');
        $counterSheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(14);
        $counterSheet->getStyle('A1:G1')->getAlignment()->setHorizontal('center');

        $counterSheet->mergeCells('A2:G2');
        $counterSheet->setCellValue('A2', 'Wholesale of hardware, electricals, & plumbing supply etc.');
        $counterSheet->getStyle('A2:G2')->getAlignment()->setHorizontal('center');

        // ===== TITLE =====
        $counterSheet->mergeCells('A4:G4');
        $counterSheet->setCellValue('A4', 'COUNTER RECEIPT');
        $counterSheet->getStyle('A4:G4')->getFont()->setBold(true)->setSize(16);
        $counterSheet->getStyle('A4:G4')->getAlignment()->setHorizontal('center');

        // ===== FILTER INFO =====
        if ($customerId) {
            $counterSheet->fromArray([
                ['Customer:', $customerName],
                ['Date From:', Carbon::parse($startDate)->format('F j, Y')],
                ['Date To:', Carbon::parse($endDate)->format('F j, Y')],
            ], null, 'A6');

            $counterSheet->getStyle('A6:A8')->getFont()->setBold(true);

            $headerRow = 10;

            $headers = [
                'Invoice No',
                'Invoice Date',
                'Due Date',
                'Payment Term',
                'Amount',
                'Remarks'
            ];

            $counterSheet->fromArray($headers, null, "A{$headerRow}");

            $counterSheet->getStyle("A{$headerRow}:F{$headerRow}")
                ->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE5E7EB']
                    ],
                    'alignment' => ['horizontal' => 'center'],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                    ]
                ]);

            $row = $headerRow + 1;
            $totalAmount = 0;
            $count = 0;

            foreach ($results as $record) {

                $invoiceDate = $record->invoice_date
                    ? Carbon::parse($record->invoice_date)->format('F j, Y')
                    : '';

                $dueDate = $record->due_date
                    ? Carbon::parse($record->due_date)->format('F j, Y')
                    : '';

                $remarks = strtolower($record->payment_method ?? '') === 'cash'
                    ? 'Paid'
                    : '';

                $counterSheet->setCellValue("A{$row}", $record->dr_no ?? '');
                $counterSheet->setCellValue("B{$row}", $invoiceDate);
                $counterSheet->setCellValue("C{$row}", $dueDate);
                $counterSheet->setCellValue("D{$row}", $record->payment_term ?? '');
                $counterSheet->setCellValue("E{$row}", $record->grand_total ?? 0);
                $counterSheet->setCellValue("F{$row}", $remarks);

                $totalAmount += $record->grand_total ?? 0;
                $count++;
                $row++;
            }

            $counterSheet->setCellValue("D{$row}", "Total Transactions:");
            $counterSheet->setCellValue("E{$row}", $count);
            $counterSheet->setCellValue("F{$row}", $totalAmount);

            $counterSheet->getStyle("D{$row}:F{$row}")
                ->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFDE68A']
                    ]
                ]);

            $counterSheet->getStyle("A{$headerRow}:F{$row}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

        } else {
            $results = collect($results)
                ->sortBy([
                    ['customer_name', 'asc'],
                    ['invoice_date', 'asc']
                ]);

            $row = 6;
            $currentCustomer = null;
            $customerTotal = 0;
            $customerCount = 0;
            foreach ($results as $record) {
                if ($currentCustomer !== $record->customer_name) {
                    if ($currentCustomer !== null) {
                        $counterSheet->setCellValue("C{$row}", "Total Transactions:");
                        $counterSheet->setCellValue("D{$row}", $customerCount);
                        $counterSheet->setCellValue("E{$row}", $customerTotal);
                        $counterSheet->getStyle("C{$row}:E{$row}")
                            ->applyFromArray([
                                'font' => ['bold' => true],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FFFDE68A']
                                ],
                                'borders' => [
                                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                                ]
                            ]);
                        $counterSheet->getStyle("E1:E{$row}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                        $row += 3;
                    }
                    $currentCustomer = $record->customer_name;
                    $customerTotal = 0;
                    $customerCount = 0;

                    // Customer Header
                    $counterSheet->setCellValue("A{$row}", "Customer:");
                    $counterSheet->setCellValue("B{$row}", $currentCustomer);

                    $counterSheet->setCellValue("A" . ($row + 1), "Date From:");
                    $counterSheet->setCellValue("B" . ($row + 1), Carbon::parse($startDate)->format('F j, Y'));

                    $counterSheet->setCellValue("A" . ($row + 2), "Date To:");
                    $counterSheet->setCellValue("B" . ($row + 2), Carbon::parse($endDate)->format('F j, Y'));

                    $counterSheet->getStyle("A{$row}:A" . ($row + 2))
                        ->getFont()
                        ->setBold(true);

                    $row += 4;

                    // Table Header
                    $counterSheet->fromArray([
                        [
                            'Invoice No',
                            'Invoice Date',
                            'Due Date',
                            'Payment Term',
                            'Amount'
                        ]
                    ], null, "A{$row}");

                    $counterSheet->getStyle("A{$row}:E{$row}")
                        ->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFE5E7EB']
                            ],
                            'alignment' => ['horizontal' => 'center'],
                            'borders' => [
                                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                            ]
                        ]);

                    $row++;
                }

                $invoiceDate = $record->invoice_date
                    ? Carbon::parse($record->invoice_date)->format('F j, Y')
                    : '';

                $dueDate = $record->due_date
                    ? Carbon::parse($record->due_date)->format('F j, Y')
                    : '';

                $counterSheet->setCellValue("A{$row}", $record->dr_no ?? '');
                $counterSheet->setCellValue("B{$row}", $invoiceDate);
                $counterSheet->setCellValue("C{$row}", $dueDate);
                $counterSheet->setCellValue("D{$row}", $record->payment_term ?? '');
                $counterSheet->setCellValue("E{$row}", $record->grand_total ?? 0);

                $counterSheet->getStyle("A{$row}:E{$row}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $customerTotal += $record->grand_total ?? 0;
                $customerCount++;

                $row++;
            }

            // Last customer total
            if ($currentCustomer) {

                $counterSheet->setCellValue("C{$row}", "Total Transactions:");
                $counterSheet->setCellValue("D{$row}", $customerCount);
                $counterSheet->setCellValue("E{$row}", $customerTotal);

                $counterSheet->getStyle("C{$row}:E{$row}")
                    ->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFDE68A']
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                        ]
                    ]);
                $counterSheet->getStyle("E1:E{$row}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }

            
        }

        foreach (range('A', 'G') as $col) {
            $counterSheet->getColumnDimension($col)->setAutoSize(true);
        }

        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        $fileName = 'sales_invoice_summary_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        $writer->save('php://output');
        exit;
    }

    public function sales_report_by_customer_yearly(Request $request)
    {
        $year = $request->year ?: date('Y');
        $month = $request->month ?: null;
        $quarter = $request->quarter ?: null;

        $customerId = $request->customer_id ?: null;
        $status = $request->status ?: null;
        $location = $request->location ?: null;
        $salesmanId = $request->salesman ?: null;

        $results = DB::select(
            'CALL sp_sales_report_by_customer_yearly(?, ?, ?, ?, ?, ?, ?)',
            [
                $year,
                $month,
                $quarter,
                $customerId,
                $status,
                $location,
                $salesmanId
            ]
        );

        $salesman = DB::table('invoices')
            ->join('salesman', 'invoices.salesman', '=', 'salesman.id')
            ->whereNotNull('invoices.salesman')
            ->select('invoices.salesman', 'salesman.salesman_name')
            ->distinct()
            ->orderBy('salesman.salesman_name')
            ->get();

        $locations = Customer::select('location')
            ->distinct()
            ->orderBy('location')
            ->get();

        return view('reports.customer_sales_yearly', compact('results', 'year', 'salesman', 'locations'));
    }

    public function exportCustomerSalesYearly(Request $request)
    {
        $year = $request->year ?: date('Y');
        $month = $request->month ?: null;
        $quarter = $request->quarter ?: null;

        $periodLabel = "ANNUAL";
        if ($month) {
            $periodLabel = "MONTHLY - " . strtoupper(date('F', mktime(0, 0, 0, $month, 1)));
        } elseif ($quarter) {
            $quarterNames = [
                1 => 'Q1 (JAN - MAR)',
                2 => 'Q2 (APR - JUN)',
                3 => 'Q3 (JUL - SEP)',
                4 => 'Q4 (OCT - DEC)',
            ];

            $periodLabel = "QUARTERLY - " . ($quarterNames[$quarter] ?? "Q{$quarter}");
        }

        $customerId = $request->customer_id ?: null;
        $status = $request->status ?: null;
        $locationId = $request->location ?: null;
        $salesmanId = $request->salesman ?: null;

        $results = DB::select(
            'CALL sp_sales_report_by_customer_yearly(?, ?, ?, ?, ?, ?, ?)',
            [
                $year,
                $month,
                $quarter,
                $customerId,
                $status,
                $locationId,
                $salesmanId
            ]
        );

        $spreadsheet = new Spreadsheet();

        /*
        |--------------------------------------------------------------------------
        | SHEETS
        |--------------------------------------------------------------------------
        */

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sales Report');

        $dashboard = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Dashboard');
        $spreadsheet->addSheet($dashboard);

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'AVT HARDWARE TRADING');

        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', "SALES REPORT BY CUSTOMER FOR {$year}");

        $sheet->setCellValue('A3', $periodLabel);

        $sheet->getStyle('A1:A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79'] // dark blue
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */

        $filters = [];

        if ($locationId) {
            $filters[] = "LOCATION: " . strtoupper($locationId);
        }

        if ($salesmanId) {
            $salesmanName = DB::table('salesman')
                ->where('id', $salesmanId)
                ->value('salesman_name');

            $filters[] = "SALESMAN: " . strtoupper($salesmanName);
        }

        if ($customerId) {
            $customerName = DB::table('customers')
                ->where('id', $customerId)
                ->value('customer_name');

            $filters[] = "CUSTOMER: " . strtoupper($customerName);
        }

        if (!empty($filters)) {
            $sheet->mergeCells('A3:P3');
            $sheet->setCellValue('A3', implode(' | ', $filters));

            $sheet->getStyle('A3')->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => 'center']
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TABLE HEADER
        |--------------------------------------------------------------------------
        */

        $headerRow = 5;

        $headers = [
            '#',
            'CUSTOMER CODE',
            'CUSTOMER',
            'LOCATION',
            'SALESMAN',
            'JAN',
            'FEB',
            'MAR',
            'APR',
            'MAY',
            'JUN',
            'JUL',
            'AUG',
            'SEP',
            'OCT',
            'NOV',
            'DEC',
            'TOTAL'
        ];

        $sheet->fromArray($headers, null, "A{$headerRow}");

        $sheet->getStyle("A{$headerRow}:R{$headerRow}")
            ->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center']
            ]);

        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        $row = $headerRow + 1;
        $counter = 1;

        foreach ($results as $record) {

            $sheet->setCellValue("A{$row}", $counter++);
            $sheet->setCellValue("B{$row}", $record->customer_code);
            $sheet->setCellValue("C{$row}", $record->customer_name);
            $sheet->setCellValue("D{$row}", $record->location);
            $sheet->setCellValue("E{$row}", $record->salesman_name);

            $sheet->setCellValue("F{$row}", $record->january);
            $sheet->setCellValue("G{$row}", $record->february);
            $sheet->setCellValue("H{$row}", $record->march);
            $sheet->setCellValue("I{$row}", $record->april);
            $sheet->setCellValue("J{$row}", $record->may);
            $sheet->setCellValue("K{$row}", $record->june);
            $sheet->setCellValue("L{$row}", $record->july);
            $sheet->setCellValue("M{$row}", $record->august);
            $sheet->setCellValue("N{$row}", $record->september);
            $sheet->setCellValue("O{$row}", $record->october);
            $sheet->setCellValue("P{$row}", $record->november);
            $sheet->setCellValue("Q{$row}", $record->december);

            $sheet->setCellValue(
                "R{$row}",
                "=SUM(F{$row}:Q{$row})"
            );

            $row++;
        }

        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue("C{$row}", "GRAND TOTAL");

        foreach (range('F', 'R') as $col) {

            if ($col === 'R') {
                $sheet->setCellValue(
                    "{$col}{$row}",
                    "=SUM(R" . ($headerRow + 1) . ":R" . ($row - 1) . ")"
                );
            } else {
                $sheet->setCellValue(
                    "{$col}{$row}",
                    "=SUM({$col}" . ($headerRow + 1) . ":{$col}" . ($row - 1) . ")"
                );
            }
        }

        $sheet->getStyle("A{$row}:R{$row}")
            ->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFDE68A']
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD DATA
        |--------------------------------------------------------------------------
        */

        $dashboard->setCellValue("A1", "MONTH");
        $dashboard->setCellValue("B1", "TOTAL SALES");

        $months = [
            'JAN','FEB','MAR','APR','MAY','JUN',
            'JUL','AUG','SEP','OCT','NOV','DEC'
        ];

        foreach ($months as $index => $monthName) {

            $r = $index + 2;
            $colLetter = chr(70 + $index); // F = JAN

            $dashboard->setCellValue("A{$r}", $monthName);

            $dashboard->setCellValue(
                "B{$r}",
                "=SUM('Sales Report'!{$colLetter}" . ($headerRow + 1) . ":{$colLetter}" . ($row - 1) . ")"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CHART
        |--------------------------------------------------------------------------
        */
        $labels = [
            new DataSeriesValues('String', "'Dashboard'!\$A\$2:\$A\$13", null, 12),
        ];

        $values = [
            new DataSeriesValues('Number', "'Dashboard'!\$B\$2:\$B\$13", null, 12),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, 0),
            $labels,
            [],
            $values
        );

        $plotArea = new PlotArea(null, [$series]);

        $chart = new Chart(
            'Monthly Sales Chart',
            new Title('Monthly Sales Trend'),
            new Legend(Legend::POSITION_RIGHT, null, false),
            $plotArea
        );

        $chart->setTopLeftPosition('D2');
        $chart->setBottomRightPosition('P20');

        $dashboard->addChart($chart);

        /*
        |--------------------------------------------------------------------------
        | FORMATTING
        |--------------------------------------------------------------------------
        */
        $dashboard->getStyle('A1:B1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2F5597']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        $dashboard->getStyle("B2:B13")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $sheet->getStyle("F" . ($headerRow + 1) . ":R{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A6');

        /*
        |--------------------------------------------------------------------------
        | EXPORT
        |--------------------------------------------------------------------------
        */

        $spreadsheet->setActiveSheetIndexByName('Sales Report');

        $fileName = "sales_report_by_customer_{$year}.xlsx";

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
    
    public function sales_report_by_location_yearly(Request $request)
    {
        $year = $request->year ?: date('Y');
        $month = $request->month ?: null;
        $quarter = $request->quarter ?: null;

        $location = $request->location ?: null;
        $status = $request->status ?: null;
        $salesmanId = $request->salesman ?: null;

        $results = DB::select(
            'CALL sp_sales_yearly_by_location(?, ?, ?, ?, ?, ?, ?)',
            [
                $year,
                $month,
                $quarter,
                $status,
                $salesmanId,
                null,
                $location
            ]
        );

        $salesman = DB::table('salesman')
            ->orderBy('salesman_name')
            ->get();

        $locations = Customer::select('location')
            ->distinct()
            ->orderBy('location')
            ->get();

        return view('reports.location_sales_yearly', compact(
            'results',
            'year',
            'salesman',
            'locations'
        ));
    }

    public function exportLocationSalesYearly(Request $request)
    {
        $year = $request->year ?: date('Y');
        $month = $request->month ?: null;
        $quarter = $request->quarter ?: null;
        $status = $request->status ?: null;

        $periodLabel = "ANNUAL";
        if ($month) {
            $periodLabel = "MONTHLY - " . strtoupper(date('F', mktime(0,0,0,$month,1)));
        } elseif ($quarter) {
            $quarterNames = [
                1 => 'Q1 (JAN - MAR)',
                2 => 'Q2 (APR - JUN)',
                3 => 'Q3 (JUL - SEP)',
                4 => 'Q4 (OCT - DEC)',
            ];

            $periodLabel = "QUARTERLY - " . ($quarterNames[$quarter] ?? "Q{$quarter}");
        }

        $location = $request->location ?: null;
        $salesmanId = $request->salesman ?: null;

        $results = DB::select(
            'CALL sp_sales_yearly_by_location(?, ?, ?, ?, ?, ?, ?)',
            [$year,
            $month,
            $quarter,
            $status,
            $salesmanId,
            null,
            $location]
        );

        $spreadsheet = new Spreadsheet();

        /*
        |--------------------------------------------------------------------------
        | SHEETS
        |--------------------------------------------------------------------------
        */

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Location Sales');

        $dashboard = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Dashboard');
        $spreadsheet->addSheet($dashboard);

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'AVT HARDWARE TRADING');

        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', "SALES REPORT BY LOCATION FOR {$year}");

        $sheet->setCellValue('A3', $periodLabel);

        $sheet->getStyle('A1:A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        /*
        |--------------------------------------------------------------------------
        | TABLE HEADER
        |--------------------------------------------------------------------------
        */

        $headerRow = 5;

        $headers = [
            '#','LOCATION','JAN','FEB','MAR','APR','MAY','JUN',
            'JUL','AUG','SEP','OCT','NOV','DEC','TOTAL'
        ];

        $sheet->fromArray($headers, null, "A{$headerRow}");

        $sheet->getStyle("A{$headerRow}:O{$headerRow}")
            ->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E1F2']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        $row = $headerRow + 1;
        $counter = 1;

        foreach ($results as $r) {

            $sheet->setCellValue("A{$row}", $counter++);
            $sheet->setCellValue("B{$row}", $r->location);

            $sheet->setCellValue("C{$row}", $r->january);
            $sheet->setCellValue("D{$row}", $r->february);
            $sheet->setCellValue("E{$row}", $r->march);
            $sheet->setCellValue("F{$row}", $r->april);
            $sheet->setCellValue("G{$row}", $r->may);
            $sheet->setCellValue("H{$row}", $r->june);
            $sheet->setCellValue("I{$row}", $r->july);
            $sheet->setCellValue("J{$row}", $r->august);
            $sheet->setCellValue("K{$row}", $r->september);
            $sheet->setCellValue("L{$row}", $r->october);
            $sheet->setCellValue("M{$row}", $r->november);
            $sheet->setCellValue("N{$row}", $r->december);

            $sheet->setCellValue("O{$row}", "=SUM(C{$row}:N{$row})");

            $row++;
        }

        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue("B{$row}", "GRAND TOTAL");

        foreach (range('C','O') as $col) {
            $sheet->setCellValue(
                "{$col}{$row}",
                "=SUM({$col}" . ($headerRow + 1) . ":{$col}" . ($row - 1) . ")"
            );
        }

        $sheet->getStyle("A{$row}:O{$row}")
            ->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF2CC']
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD (FOR CHART)
        |--------------------------------------------------------------------------
        */

        $dashboard->setCellValue("A1", "MONTH");
        $dashboard->setCellValue("B1", "TOTAL SALES");

        $months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];

        foreach ($months as $i => $m) {

            $r = $i + 2;
            $colLetter = chr(67 + $i); // C = JAN

            $dashboard->setCellValue("A{$r}", $m);

            $dashboard->setCellValue(
                "B{$r}",
                "=SUM('Location Sales'!{$colLetter}" . ($headerRow + 1) . ":{$colLetter}" . ($row - 1) . ")"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CHART
        |--------------------------------------------------------------------------
        */

        $labels = [
            new DataSeriesValues('String', "'Dashboard'!\$A\$2:\$A\$13", null, 12),
        ];

        $values = [
            new DataSeriesValues('Number', "'Dashboard'!\$B\$2:\$B\$13", null, 12),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            [0],
            $labels,
            [],
            $values
        );

        $plotArea = new PlotArea(null, [$series]);

        $chart = new Chart(
            'Location Sales Chart',
            new Title('Monthly Sales Trend'),
            new Legend(Legend::POSITION_RIGHT, null, false),
            $plotArea
        );

        $chart->setTopLeftPosition('D2');
        $chart->setBottomRightPosition('P20');

        $dashboard->addChart($chart);

        /*
        |--------------------------------------------------------------------------
        | FORMATTING
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("C" . ($headerRow + 1) . ":O{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        foreach (range('A','O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A6');

        /*
        |--------------------------------------------------------------------------
        | EXPORT
        |--------------------------------------------------------------------------
        */

        $spreadsheet->setActiveSheetIndexByName('Location Sales');

        $fileName = "sales_report_by_location_{$year}.xlsx";

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function sales_report_by_salesman_yearly(Request $request)
    {
        $year = $request->year ?: date('Y');
        $month = $request->month ?: null;
        $quarter = $request->quarter ?: null;

        $salesmanId = $request->salesman ?: null;
        $status = $request->status ?: null;

        $results = DB::select(
            'CALL sp_sales_yearly_by_salesman(?, ?, ?, ?, ?)',
            [
                $year,
                $month,
                $quarter,
                $status,
                $salesmanId
            ]
        );

        $salesman = DB::table('salesman')->get();

        return view('reports.salesman_sales_yearly', compact(
            'results',
            'year',
            'salesman'
        ));
    }

    public function exportSalesmanSalesYearly(Request $request)
    {
        $year = $request->year ?: date('Y');
        $month = $request->month ?: null;
        $quarter = $request->quarter ?: null;

        $periodLabel = "ANNUAL";

        if ($month) {
            $periodLabel = "MONTHLY - " . strtoupper(date('F', mktime(0, 0, 0, $month, 1)));
        } elseif ($quarter) {
            $quarterNames = [
                1 => 'Q1 (JAN - MAR)',
                2 => 'Q2 (APR - JUN)',
                3 => 'Q3 (JUL - SEP)',
                4 => 'Q4 (OCT - DEC)',
            ];

            $periodLabel = "QUARTERLY - " . ($quarterNames[$quarter] ?? "Q{$quarter}");
        }

        $salesmanId = $request->salesman ?: null;
        $status = $request->status ?: null;

        $results = DB::select(
            'CALL sp_sales_yearly_by_salesman(?, ?, ?, ?, ?)',
            [
                $year,
                $month,
                $quarter,
                $status,
                $salesmanId
            ]
        );

        $spreadsheet = new Spreadsheet();

        /*
        |--------------------------------------------------------------------------
        | SHEETS
        |--------------------------------------------------------------------------
        */

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sales Report');

        $dashboard = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Dashboard');
        $spreadsheet->addSheet($dashboard);

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'AVT HARDWARE TRADING');

        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', "SALES REPORT BY SALESMAN FOR {$year}");

        $sheet->setCellValue('A3', $periodLabel);

        $sheet->getStyle('A1:A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E79']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        /*
        |--------------------------------------------------------------------------
        | TABLE HEADER
        |--------------------------------------------------------------------------
        */

        $headerRow = 5;

        $headers = [
            '#',
            'SALESMAN',
            'JAN',
            'FEB',
            'MAR',
            'APR',
            'MAY',
            'JUN',
            'JUL',
            'AUG',
            'SEP',
            'OCT',
            'NOV',
            'DEC',
            'TOTAL'
        ];

        $sheet->fromArray($headers, null, "A{$headerRow}");

        $sheet->getStyle("A{$headerRow}:O{$headerRow}")
            ->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E1F2']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        $row = $headerRow + 1;
        $counter = 1;

        foreach ($results as $r) {

            $sheet->setCellValue("A{$row}", $counter++);
            $sheet->setCellValue("B{$row}", $r->salesman_name);

            $sheet->setCellValue("C{$row}", $r->january);
            $sheet->setCellValue("D{$row}", $r->february);
            $sheet->setCellValue("E{$row}", $r->march);
            $sheet->setCellValue("F{$row}", $r->april);
            $sheet->setCellValue("G{$row}", $r->may);
            $sheet->setCellValue("H{$row}", $r->june);
            $sheet->setCellValue("I{$row}", $r->july);
            $sheet->setCellValue("J{$row}", $r->august);
            $sheet->setCellValue("K{$row}", $r->september);
            $sheet->setCellValue("L{$row}", $r->october);
            $sheet->setCellValue("M{$row}", $r->november);
            $sheet->setCellValue("N{$row}", $r->december);

            // row total
            $sheet->setCellValue("O{$row}", "=SUM(C{$row}:N{$row})");

            $row++;
        }

        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue("B{$row}", "GRAND TOTAL");

        foreach (range('C','O') as $col) {
            $sheet->setCellValue(
                "{$col}{$row}",
                "=SUM({$col}" . ($headerRow + 1) . ":{$col}" . ($row - 1) . ")"
            );
        }

        $sheet->getStyle("A{$row}:O{$row}")
            ->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF2CC']
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD SHEET
        |--------------------------------------------------------------------------
        */

        $dashboard->setCellValue("A1", "MONTH");
        $dashboard->setCellValue("B1", "TOTAL SALES");

        $months = [
            'JAN','FEB','MAR','APR','MAY','JUN',
            'JUL','AUG','SEP','OCT','NOV','DEC'
        ];

        foreach ($months as $i => $m) {

            $r = $i + 2;
            $colLetter = chr(67 + $i); // C = JAN

            $dashboard->setCellValue("A{$r}", $m);

            $dashboard->setCellValue(
                "B{$r}",
                "=SUM('Sales Report'!{$colLetter}" . ($headerRow + 1) . ":{$colLetter}" . ($row - 1) . ")"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CHART
        |--------------------------------------------------------------------------
        */

        $labels = [
            new DataSeriesValues('String', "'Dashboard'!\$A\$2:\$A\$13", null, 12),
        ];

        $values = [
            new DataSeriesValues('Number', "'Dashboard'!\$B\$2:\$B\$13", null, 12),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            [0],
            $labels,
            [],
            $values
        );

        $plotArea = new PlotArea(null, [$series]);

        $chart = new Chart(
            'Salesman Sales Chart',
            new Title('Monthly Sales Trend'),
            new Legend(Legend::POSITION_RIGHT, null, false),
            $plotArea
        );

        $chart->setTopLeftPosition('D2');
        $chart->setBottomRightPosition('P20');

        $dashboard->addChart($chart);

        /*
        |--------------------------------------------------------------------------
        | FORMATTING
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("C" . ($headerRow + 1) . ":O{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $dashboard->getStyle("B2:B13")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        foreach (range('A','O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A6');

        /*
        |--------------------------------------------------------------------------
        | EXPORT
        |--------------------------------------------------------------------------
        */

        $spreadsheet->setActiveSheetIndexByName('Sales Report');

        $fileName = "sales_report_by_salesman_{$year}.xlsx";

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function topSellingProducts(Request $request)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month ?? null;
        $quarter = $request->quarter ?? null;
        $location = $request->location ?? null;
        $salesman = $request->salesman ?? null;

        $results = DB::select('CALL sp_top_selling_products(?, ?, ?, ?, ?)', [
            $year,
            $month,
            $quarter,
            $location,
            $salesman
        ]);

        $locations = Customer::select('location')->distinct()->get();

        $salesman = DB::table('salesman')
            ->orderBy('salesman_name')
            ->get();

        return view('reports.top_selling_products', compact(
            'results',
            'locations',
            'salesman'
        ));
    }

    public function exportTopSellingProducts(Request $request)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month ?? null;
        $quarter = $request->quarter ?? null;
        $location = $request->location ?? null;
        $salesman = $request->salesman ?? null;

        $results = DB::select('CALL sp_top_selling_products(?, ?, ?, ?, ?)', [
            $year,
            $month,
            $quarter,
            $location,
            $salesman
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('A1:R1');
        $sheet->setCellValue('A1', 'AVT Hardware Trading');

        $sheet->mergeCells('A2:R2');
        $sheet->setCellValue('A2', 'TOP SELLING PRODUCTS REPORT');

        $sheet->getStyle('A1:A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F2937']
            ]
        ]);

        /*
        |--------------------------------------------------------------------------
        | FILTER INFO
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue('A4', 'Year:');
        $sheet->setCellValue('B4', $year);

        $sheet->setCellValue('D4', 'Month:');
        $sheet->setCellValue('E4', $month ?? 'All');

        $sheet->setCellValue('G4', 'Quarter:');
        $sheet->setCellValue('H4', $quarter ?? 'All');

        $sheet->setCellValue('J4', 'Location:');
        $sheet->setCellValue('K4', $location ?? 'All');

        $sheet->setCellValue('M4', 'Salesman:');
        $sheet->setCellValue('N4', $salesman ?? 'All');

        $sheet->getStyle('A4:N4')->getFont()->setBold(true);

        /*
        |--------------------------------------------------------------------------
        | TABLE HEADER
        |--------------------------------------------------------------------------
        */

        $headerRow = 6;

        $headers = [
            'Product Code',
            'Product',
            'Salesman',
            'Location',
            'Jan','Feb','Mar','Apr','May','Jun',
            'Jul','Aug','Sep','Oct','Nov','Dec',
            'Total Qty',
            'Total Sales'
        ];

        $sheet->fromArray($headers, null, "A{$headerRow}");

        $sheet->getStyle("A{$headerRow}:R{$headerRow}")
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F2937']
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        $row = $headerRow + 1;

        $grandQty = 0;
        $grandSales = 0;

        $grandMonths = array_fill(1, 12, 0);

        foreach ($results as $r) {

            $sheet->fromArray([
                $r->product_code,
                $r->product_name,
                $r->salesman,
                $r->location,
                $r->january,
                $r->february,
                $r->march,
                $r->april,
                $r->may,
                $r->june,
                $r->july,
                $r->august,
                $r->september,
                $r->october,
                $r->november,
                $r->december,
                $r->total_quantity,
                $r->total_sales
            ], null, "A{$row}");

            // totals
            $grandMonths[1]  += $r->january;
            $grandMonths[2]  += $r->february;
            $grandMonths[3]  += $r->march;
            $grandMonths[4]  += $r->april;
            $grandMonths[5]  += $r->may;
            $grandMonths[6]  += $r->june;
            $grandMonths[7]  += $r->july;
            $grandMonths[8]  += $r->august;
            $grandMonths[9]  += $r->september;
            $grandMonths[10] += $r->october;
            $grandMonths[11] += $r->november;
            $grandMonths[12] += $r->december;

            $grandQty += $r->total_quantity;
            $grandSales += $r->total_sales;

            $row++;
        }

        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL ROW
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue("A{$row}", "GRAND TOTAL");

        $sheet->setCellValue("E{$row}", $grandMonths[1]);
        $sheet->setCellValue("F{$row}", $grandMonths[2]);
        $sheet->setCellValue("G{$row}", $grandMonths[3]);
        $sheet->setCellValue("H{$row}", $grandMonths[4]);
        $sheet->setCellValue("I{$row}", $grandMonths[5]);
        $sheet->setCellValue("J{$row}", $grandMonths[6]);
        $sheet->setCellValue("K{$row}", $grandMonths[7]);
        $sheet->setCellValue("L{$row}", $grandMonths[8]);
        $sheet->setCellValue("M{$row}", $grandMonths[9]);
        $sheet->setCellValue("N{$row}", $grandMonths[10]);
        $sheet->setCellValue("O{$row}", $grandMonths[11]);
        $sheet->setCellValue("P{$row}", $grandMonths[12]);

        $sheet->setCellValue("Q{$row}", $grandQty);
        $sheet->setCellValue("R{$row}", $grandSales);

        $sheet->getStyle("A{$row}:R{$row}")
            ->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFBBF24']
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | NUMBER FORMAT
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("E7:Q{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        $sheet->getStyle("R7:R{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        /*
        |--------------------------------------------------------------------------
        | BORDERS
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("A6:R{$row}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | AUTO SIZE
        |--------------------------------------------------------------------------
        */

        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        /*
        |--------------------------------------------------------------------------
        | FREEZE HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane("A7");

        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        $fileName = 'top_selling_products_' . now()->format('Ymd_His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");

        $writer->save('php://output');
        exit;
    }

    public function purchaseYearly(Request $request)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month ?? null;
        $quarter = $request->quarter ?? null;
        $supplier = $request->supplier ?? null;

        $results = DB::select('CALL sp_yearly_purchase_report(?, ?, ?, ?)', [
            $year,
            $month,
            $quarter,
            $supplier
        ]);

        $suppliers = DB::table('suppliers')
            ->orderBy('name')
            ->get();

        return view('reports.purchase_yearly', compact(
            'results',
            'year',
            'suppliers'
        ));
    }

    public function exportPurchaseYearly(Request $request)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month ?? null;
        $quarter = $request->quarter ?? null;
        $supplier = $request->supplier ?? null;

        $results = DB::select('CALL sp_yearly_purchase_report(?, ?, ?, ?)', [
            $year,
            $month,
            $quarter,
            $supplier
        ]);

        $supplierName = 'All Suppliers';

        if ($supplier) {
            $supplierName = DB::table('suppliers')
                ->where('id', $supplier)
                ->value('name');
        }

        $monthName = $month
            ? Carbon::create()->month($month)->format('F')
            : 'All Months';

        $quarterName = $quarter
            ? 'Quarter '.$quarter
            : 'All Quarters';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /*
        |--------------------------------------------------------------------------
        | COMPANY HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'AVT Hardware Trading');

        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'YEARLY PURCHASE REPORT');

        $sheet->getStyle('A1:O2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => 'FFFFFFFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F2937']
            ]
        ]);

        /*
        |--------------------------------------------------------------------------
        | FILTER DETAILS
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue('A4', 'Year:');
        $sheet->setCellValue('B4', $year);

        $sheet->setCellValue('D4', 'Month:');
        $sheet->setCellValue('E4', $monthName);

        $sheet->setCellValue('G4', 'Quarter:');
        $sheet->setCellValue('H4', $quarterName);

        $sheet->setCellValue('J4', 'Supplier:');
        $sheet->setCellValue('K4', $supplierName);

        $sheet->getStyle('A4:K4')->getFont()->setBold(true);

        /*
        |--------------------------------------------------------------------------
        | TABLE HEADER
        |--------------------------------------------------------------------------
        */

        $headerRow = 6;

        $headers = [
            'Supplier',
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec',
            'Total Qty',
            'Total Amount'
        ];

        $sheet->fromArray($headers, null, "A{$headerRow}");

        $sheet->getStyle("A{$headerRow}:O{$headerRow}")
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1F2937']
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        $row = $headerRow + 1;

        $grandJanuary = 0;
        $grandFebruary = 0;
        $grandMarch = 0;
        $grandApril = 0;
        $grandMay = 0;
        $grandJune = 0;
        $grandJuly = 0;
        $grandAugust = 0;
        $grandSeptember = 0;
        $grandOctober = 0;
        $grandNovember = 0;
        $grandDecember = 0;

        $grandQty = 0;
        $grandAmount = 0;

        foreach ($results as $r) {

            $sheet->fromArray([
                $r->supplier_name,
                $r->january,
                $r->february,
                $r->march,
                $r->april,
                $r->may,
                $r->june,
                $r->july,
                $r->august,
                $r->september,
                $r->october,
                $r->november,
                $r->december,
                $r->total_quantity,
                $r->total_amount
            ], null, "A{$row}");

            $grandJanuary += $r->january;
            $grandFebruary += $r->february;
            $grandMarch += $r->march;
            $grandApril += $r->april;
            $grandMay += $r->may;
            $grandJune += $r->june;
            $grandJuly += $r->july;
            $grandAugust += $r->august;
            $grandSeptember += $r->september;
            $grandOctober += $r->october;
            $grandNovember += $r->november;
            $grandDecember += $r->december;

            $grandQty += $r->total_quantity;
            $grandAmount += $r->total_amount;

            $row++;
        }

        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue("A{$row}", "GRAND TOTAL");

        $sheet->setCellValue("B{$row}", $grandJanuary);
        $sheet->setCellValue("C{$row}", $grandFebruary);
        $sheet->setCellValue("D{$row}", $grandMarch);
        $sheet->setCellValue("E{$row}", $grandApril);
        $sheet->setCellValue("F{$row}", $grandMay);
        $sheet->setCellValue("G{$row}", $grandJune);
        $sheet->setCellValue("H{$row}", $grandJuly);
        $sheet->setCellValue("I{$row}", $grandAugust);
        $sheet->setCellValue("J{$row}", $grandSeptember);
        $sheet->setCellValue("K{$row}", $grandOctober);
        $sheet->setCellValue("L{$row}", $grandNovember);
        $sheet->setCellValue("M{$row}", $grandDecember);

        $sheet->setCellValue("N{$row}", $grandQty);
        $sheet->setCellValue("O{$row}", $grandAmount);

        $sheet->getStyle("A{$row}:O{$row}")
            ->applyFromArray([
                'font' => [
                    'bold' => true
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFFBBF24'
                    ]
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | NUMBER FORMATTING
        |--------------------------------------------------------------------------
        */

        // Monthly Qty + Total Qty
        $sheet->getStyle("B7:N{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        // Total Amount
        $sheet->getStyle("O7:O{$row}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        /*
        |--------------------------------------------------------------------------
        | BORDERS
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("A6:O{$row}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ]);

        /*
        |--------------------------------------------------------------------------
        | AUTO SIZE
        |--------------------------------------------------------------------------
        */

        foreach (range('A', 'O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        /*
        |--------------------------------------------------------------------------
        | FREEZE HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane('A7');

        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        $fileName = 'yearly_purchase_report_' . now()->format('Ymd_His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");

        $writer->save('php://output');
        exit;
    }

    /**
     * Helper: add logo and header
     */
    private function addHeader($sheet, $title)
    {
        // Logo
        $logoPath = public_path('images/avt_logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Company Logo');
            $drawing->setDescription('Company Logo');
            $drawing->setPath($logoPath);

            // setHeight controls the image height in pixels
            $drawing->setHeight(60);            // adjust smaller/larger if needed
            $drawing->setCoordinates('A1');    // anchor at A1
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }

        // Ensure columns have reasonable widths so text is visible
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Make room for logo (set row heights so logo does not overlap text)
        $sheet->getRowDimension(1)->setRowHeight(22); // small heading row
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(20);
        // Ensure enough height for the logo row
        $sheet->getRowDimension(1)->setRowHeight(60);

        // Merge cells for company name, address, and report title (adjust to cover as many columns as your table)
        $sheet->mergeCells('C1:F1');
        $sheet->mergeCells('C2:F2');
        $sheet->mergeCells('C3:F3');

        // Company Name
        $sheet->setCellValue('C1', 'AVT Hardware Trading');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                            ->setVertical(Alignment::VERTICAL_CENTER);

        // Company Address (ensure this value is present)
        $sheet->setCellValue('C2', ' Wholesale of hardware, electricals, & plumbing supply etc.<br>
            Contact: 0936-8834-275 / 0999-3669-539'); // ← change to your real address
        $sheet->getStyle('C2')->getFont()->setSize(12);
        $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                            ->setVertical(Alignment::VERTICAL_CENTER)
                                            ->setWrapText(true);

        // Report Title
        $sheet->setCellValue('C3', $title);
        $sheet->getStyle('C3')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                            ->setVertical(Alignment::VERTICAL_CENTER);

    }

}

