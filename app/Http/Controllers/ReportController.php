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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Product;
use App\Customer;

class ReportController extends Controller
{
    /**
     * Show AR Aging Report in Blade
     */
    public function ar_aging(Request $request)
    {
        // Filters
        $customerId    = $request->input('customer_id') ?: null;
        $paymentModeId = $request->input('payment_mode_id') ?: null;
        $asOfDate      = $request->input('as_of_date');

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

        // === Header ===
        $this->addHeader($sheet, 'AR Aging Report');
        $headerRow = 6;

        // === Table Headers ===
        $headers = [
            'Customer Code','Customer','Invoice #','Invoice Date','Due Date',
            'Invoice Amount','Outstanding','Amount Paid','Collection Date',
            'Remarks','Payment Method','Payment Term','Aging Bucket',
            'Payment Status','Invoice Status'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // === Sort results ===
        usort($results, fn($a, $b) => strcmp($a->customer_name, $b->customer_name));

        // === Initialize ===
        $row = $headerRow + 1;
        $currentCustomer = null;
        $subtotalInvoice = $subtotalPaid = $subtotalOutstanding = 0;
        $grandInvoice = $grandPaid = $grandOutstanding = 0;

        foreach ($results as $record) {
            // Subtotal row per customer
            if ($currentCustomer && $currentCustomer !== $record->customer_name) {
                $sheet->setCellValue("C{$row}", "Subtotal for $currentCustomer");
                $sheet->setCellValue("F{$row}", $subtotalInvoice);
                $sheet->setCellValue("G{$row}", $subtotalOutstanding);
                $sheet->setCellValue("H{$row}", $subtotalPaid);
                $sheet->getStyle("A{$row}:O{$row}")->getFont()->setBold(true);
                $row++;

                $subtotalInvoice = $subtotalPaid = $subtotalOutstanding = 0;
            }

            $currentCustomer = $record->customer_name;

            // === Row Values ===
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
                $sheet->setCellValue("I{$row}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                    \Carbon\Carbon::parse($record->collection_date)->toDateTime()
                ));
            }

            $sheet->setCellValue("F{$row}", $record->invoice_amount ?? 0);
            $sheet->setCellValue("H{$row}", $record->amount_paid ?? 0);
            $sheet->setCellValue("G{$row}", "=F{$row}-H{$row}");
            $sheet->setCellValue("J{$row}", $record->collection_remarks ?? '');
            $sheet->setCellValue("K{$row}", $record->payment_method ?? '');
            $sheet->setCellValue("L{$row}", $record->payment_term ?? '');
            $sheet->setCellValue("M{$row}", $record->aging_bucket ?? '');
            $sheet->setCellValue("N{$row}", $record->payment_status ?? '');
            $sheet->setCellValue("O{$row}", $record->invoice_status ?? '');

            // === Optional Color Coding for Payment Status ===
            $statusCell = "N{$row}";
            switch (strtolower($record->payment_status)) {
                case 'paid':
                    $sheet->getStyle($statusCell)->getFont()->getColor()->setARGB('FF008000'); // green
                    break;
                case 'overdue':
                    $sheet->getStyle($statusCell)->getFont()->getColor()->setARGB('FFFF0000'); // red
                    break;
                case 'partially paid':
                    $sheet->getStyle($statusCell)->getFont()->getColor()->setARGB('FFFFA500'); // orange
                    break;
            }

            // === Subtotals ===
            $subtotalInvoice += $record->invoice_amount ?? 0;
            $subtotalPaid += $record->amount_paid ?? 0;
            $subtotalOutstanding += ($record->invoice_amount ?? 0) - ($record->amount_paid ?? 0);

            $grandInvoice += $record->invoice_amount ?? 0;
            $grandPaid += $record->amount_paid ?? 0;
            $grandOutstanding += ($record->invoice_amount ?? 0) - ($record->amount_paid ?? 0);

            $row++;
        }

        // === Final subtotal ===
        if ($currentCustomer) {
            $sheet->setCellValue("C{$row}", "Subtotal for $currentCustomer");
            $sheet->setCellValue("F{$row}", $subtotalInvoice);
            $sheet->setCellValue("G{$row}", $subtotalOutstanding);
            $sheet->setCellValue("H{$row}", $subtotalPaid);
            $sheet->getStyle("A{$row}:O{$row}")->getFont()->setBold(true);
            $row++;
        }

        // === Grand Totals ===
        $sheet->setCellValue("C{$row}", "GRAND TOTAL");
        $sheet->setCellValue("F{$row}", $grandInvoice);
        $sheet->setCellValue("G{$row}", $grandOutstanding);
        $sheet->setCellValue("H{$row}", $grandPaid);
        $sheet->getStyle("A{$row}:O{$row}")->getFont()->setBold(true);

        $lastRow = $row;

        // === Format date columns ===
        foreach (['D','E','I'] as $col) {
            $sheet->getStyle("{$col}".($headerRow+1).":{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('[$-en-US]mmmm d, yyyy');
        }

        // === Format amounts ===
        foreach (['F','G','H'] as $col) {
            $sheet->getStyle("{$col}".($headerRow+1).":{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // === Borders ===
        $sheet->getStyle("A{$headerRow}:O{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // === Export ===
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
        $supplierId    = $request->input('supplier_id') ?: null;
        $paymentModeId = $request->input('payment_id') ?: null;
        $asOfDate      = $request->input('as_of_date') ?: now()->toDateString();

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
        $customerName = $request->customer_id ? Customer::find($request->customer_id)->name : null;
        $productName = $request->product_id ? Product::find($request->product_id)->name : null;
        $startDate = $request->start_date ?? null;
        $endDate = $request->end_date ?? null;

        // Call the stored procedure
        $sales = DB::select('CALL get_sales_report(?, ?, ?, ?)', [
            $customerName,
            $productName,
            $startDate,
            $endDate
        ]);

        $products = Product::all();
        $customers = Customer::all();

        return view('reports.sales_report', compact('sales', 'products', 'customers'));
    }

    public function exportSales(Request $request)
    {
        // Convert IDs to names for the stored procedure
        $customerName = $request->input('customer_id') 
            ? Customer::find($request->input('customer_id'))->name 
            : null;

        $productName = $request->input('product_id') 
            ? Product::find($request->input('product_id'))->name 
            : null;

        $startDate = $request->input('start_date') ?: null;
        $endDate = $request->input('end_date') ?: null;

        // Call stored procedure for sales report
        $results = DB::select('CALL get_sales_report(?, ?, ?, ?)', [
            $customerName,
            $productName,
            $startDate,
            $endDate
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add company header (logo, name, address, report title)
        $this->addHeader($sheet, 'Sales Report');

        $headerRow = 6;
        $headers = [
            'Invoice #',
            'Date',
            'Customer',
            'Product',
            'Quantity',
            'Price',
            'Total',
            'Payment Method'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Fill data with subtotals
        $row = $headerRow + 1;
        $currentInvoice = null;
        $invoiceSubtotal = 0;
        $grandTotal = 0;

        foreach ($results as $record) {
            // Insert subtotal when invoice changes
            if ($currentInvoice !== null && $record->invoice_number !== $currentInvoice) {
                $sheet->setCellValue("F{$row}", "Subtotal:");
                $sheet->setCellValue("G{$row}", $invoiceSubtotal);
                $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);
                $row++;

                $invoiceSubtotal = 0; // reset subtotal
            }

            // Write row data
            $sheet->setCellValue("A{$row}", $record->invoice_number ?? '');
            $sheet->setCellValue("B{$row}", $record->sale_date 
                ? \Carbon\Carbon::parse($record->sale_date)->format('M d, Y') 
                : '');
            $sheet->setCellValue("C{$row}", $record->customer_name ?? '');
            $sheet->setCellValue("D{$row}", $record->product_name ?? '');
            $sheet->setCellValue("E{$row}", $record->quantity ?? 0);
            $sheet->setCellValue("F{$row}", $record->price ?? 0);
            $sheet->setCellValue("G{$row}", $record->total_amount ?? 0);
            $sheet->setCellValue("H{$row}", $record->payment_method ?? '');
            $row++;

            // Totals
            $invoiceSubtotal += $record->total_amount ?? 0;
            $grandTotal += $record->total_amount ?? 0;
            $currentInvoice = $record->invoice_number;
        }

        // Add last invoice subtotal
        if ($currentInvoice !== null) {
            $sheet->setCellValue("F{$row}", "Subtotal:");
            $sheet->setCellValue("G{$row}", $invoiceSubtotal);
            $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);
            $row++;
        }

        // Add grand total
        $sheet->setCellValue("F{$row}", "Grand Total:");
        $sheet->setCellValue("G{$row}", $grandTotal);
        $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);

        $lastRow = $row;

        // Format numbers (Price and Total)
        foreach (['F','G'] as $col) {
            $sheet->getStyle("{$col}".($headerRow+1).":{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Add borders (include summary rows)
        $sheet->getStyle("A{$headerRow}:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Export Excel
        $fileName = 'sales_report_'.now()->format('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
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
        $sheet->setCellValue('C2', '123 Main St., Test City'); // ← change to your real address
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

