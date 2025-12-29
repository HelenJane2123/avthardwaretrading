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
use Carbon\Carbon;

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

        $customerName = $request->customer_id ? Customer::find($request->customer_id)->name : null;
        $productName  = $request->product_id ? Product::find($request->product_id)->product_name : null;
        $salesmanName = $request->salesman_name ?? null;
        $startDate = $startDateInput
            ? Carbon::createFromFormat('F d, Y', $startDateInput)->toDateString()
            : now()->startOfMonth()->toDateString();
        $endDate = $endDateInput
            ? Carbon::createFromFormat('F d, Y', $endDateInput)->toDateString()
            : now()->endOfMonth()->toDateString();
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
            ->select('salesman')
            ->whereNotNull('salesman')
            ->distinct()
            ->orderBy('salesman')
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

    public function exportSales(Request $request)
    {
        $customerName = $request->input('customer_id') 
            ? Customer::find($request->input('customer_id'))->name 
            : null;

        $productName = $request->input('product_id') 
            ? Product::find($request->input('product_id'))->product_name 
            : null;

        $salesmanName = $request->input('salesman_name') ?? null;
        $startDate = $request->input('start_date') ?: null;
        $endDate = $request->input('end_date') ?: null;

        // Call stored procedure
        $results = DB::select('CALL get_sales_report(?, ?, ?, ?, ?)', [
            $customerName,
            $productName,
            $salesmanName,
            $startDate,
            $endDate
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add header
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
            'Payment Method',
            'Discounts'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $row = $headerRow + 1;
        $currentInvoice = null;
        $invoiceSubtotal = 0;
        $grandTotal = 0;

        foreach ($results as $record) {
            // Subtotal per invoice
            if ($currentInvoice !== null && $record->invoice_number !== $currentInvoice) {
                $sheet->setCellValue("F{$row}", "Subtotal:");
                $sheet->setCellValue("G{$row}", $invoiceSubtotal);
                $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);
                $row++;
                $invoiceSubtotal = 0;
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
            $sheet->setCellValue("I{$row}", $record->discount_display ?? '');
            $row++;

            $invoiceSubtotal += $record->total_amount ?? 0;
            $grandTotal += $record->total_amount ?? 0;
            $currentInvoice = $record->invoice_number;
        }

        // Add final subtotal + grand total
        if ($currentInvoice !== null) {
            $sheet->setCellValue("F{$row}", "Subtotal:");
            $sheet->setCellValue("G{$row}", $invoiceSubtotal);
            $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);
            $row++;
        }

        $sheet->setCellValue("F{$row}", "Grand Total:");
        $sheet->setCellValue("G{$row}", $grandTotal);
        $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);

        $lastRow = $row;

        // Number formatting
        foreach (['F','G'] as $col) {
            $sheet->getStyle("{$col}".($headerRow+1).":{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Borders
        $sheet->getStyle("A{$headerRow}:I{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Export file
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

        $this->addHeader($sheet, 'Estimated Income Report');

        $headerRow = 6;
        $headers = [
            'Invoice Number', 
            'Invoice Date', 
            'Customer Name', 
            'Customer Location',
            'Supplier Name', 
            'Product Name',
            'Quantity Sold', 
            'Sales Price', 
            'Sales Net Price', 
            'Sales Discounts',
            'Sales Gross Amount', 
            'Sales Net Amount',
            'Purchase Number', 
            'Quantity Purchased', 
            'Unit Cost', 
            'Purchase Net Price',
            'Purchase Discounts', 
            'Purchase Gross Amount', 
            'Purchase Net Amount',
            'Estimated Income', 
            'Profit Percentage'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // POPULATE DATA
        $row = $headerRow + 1;
        $totalSales = 0;
        $totalIncome = 0;
        $profitSum = 0;

        foreach ($reportData as $record) {

            // Invoice discounts
            $invoiceDiscountText = implode(', ', array_filter([
                $record->discount_less_add ? 'Type: '.$record->discount_less_add : null,
                $record->discount_1 ? 'D1: '.$record->discount_1.'%' : null,
                $record->discount_2 ? 'D2: '.$record->discount_2.'%' : null,
                $record->discount_3 ? 'D3: '.$record->discount_3.'%' : null,
            ]));

            // Purchase discounts
            $purchaseDiscountText = implode(', ', array_filter([
                $record->purchase_discount_1 ? 'D1: '.$record->purchase_discount_1.'%' : null,
                $record->purchase_discount_2 ? 'D2: '.$record->purchase_discount_2.'%' : null,
                $record->purchase_discount_3 ? 'D3: '.$record->purchase_discount_3.'%' : null,
            ]));

            $sheet->setCellValue("A{$row}", $record->invoice_number);
            $sheet->setCellValue("B{$row}", $record->invoice_date);
            $sheet->setCellValue("C{$row}", $record->customer_name);
            $sheet->setCellValue("D{$row}", $record->customer_location);
            $sheet->setCellValue("E{$row}", $record->supplier_name);
            $sheet->setCellValue("F{$row}", $record->product_name);

            $sheet->setCellValue("G{$row}", $record->quantity_sold);
            $sheet->setCellValue("H{$row}", $record->sales_price);
            $sheet->setCellValue("I{$row}", $record->sales_net_price);
            $sheet->setCellValue("J{$row}", $invoiceDiscountText);
            $sheet->setCellValue("K{$row}", $record->sales_gross);
            $sheet->setCellValue("L{$row}", $record->sales_net_of_net);

            $sheet->setCellValue("M{$row}", $record->po_number);
            $sheet->setCellValue("N{$row}", $record->quantity_purchased);
            $sheet->setCellValue("O{$row}", $record->unit_cost);
            $sheet->setCellValue("P{$row}", $record->net_price);
            $sheet->setCellValue("Q{$row}", $purchaseDiscountText);
            $sheet->setCellValue("R{$row}", $record->purchase_gross);
            $sheet->setCellValue("S{$row}", $record->purchase_net_of_net);

            $sheet->setCellValue("T{$row}", $record->estimated_income);
            $sheet->setCellValue("U{$row}", ($record->profit_percentage ?? 0) / 100);

            $totalSales += $record->sales_net_of_net;
            $totalIncome += $record->estimated_income;
            $profitSum += $record->profit_percentage;

            $row++;
        }

        // ===============================
        // SUMMARY
        // ===============================
        $avgProfit = count($reportData) ? $profitSum / count($reportData) : 0;

        $sheet->setCellValue("S{$row}", 'Total Sales:');
        $sheet->setCellValue("T{$row}", $totalSales);
        $row++;

        $sheet->setCellValue("S{$row}", 'Total Estimated Income:');
        $sheet->setCellValue("T{$row}", $totalIncome);
        $row++;

        $sheet->setCellValue("S{$row}", 'Average Profit %:');
        $sheet->setCellValue("T{$row}", $avgProfit / 100);
        $sheet->getStyle("T{$row}")->getNumberFormat()->setFormatCode('0.00%');

        // ===============================
        // FORMATTING
        // ===============================
        $sheet->getStyle("H".($headerRow+1).":T{$row}")
            ->getNumberFormat()
            ->setFormatCode('₱#,##0.00');

        $sheet->getStyle("U".($headerRow+1).":U{$row}")
            ->getNumberFormat()
            ->setFormatCode('0.00%');

        $sheet->getStyle("A{$headerRow}:U{$row}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
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
        $productName = $request->product_id ? Product::find($request->product_id)->product_name : null;
        $startDate   = $request->start_date ?? null;
        $endDate     = $request->end_date ?? null;

        // Call stored procedure (filters by product and date range)
        $purchases = DB::select('CALL get_purchase_report(?, ?, ?)', [
            $productName,
            $startDate,
            $endDate
        ]);

        $products = Product::orderBy('product_name')->get();

        return view('reports.purchase_report', compact('purchases', 'products'));
    }

    public function exportPurchase(Request $request)
    {
        $productName = $request->product_id ? Product::find($request->product_id)->product_name : null;
        $startDate   = $request->start_date ?? null;
        $endDate     = $request->end_date ?? null;

        try {
            // Call stored procedure (filters by product and date range)
            $purchases = DB::select('CALL get_purchase_report(?, ?, ?)', [
                $productName,
                $startDate,
                $endDate
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating purchase report: ' . $e->getMessage());
        }

        // Create spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add header title (company name, address, etc.)
        $this->addHeader($sheet, 'Purchase Report');

        // Header row for table
        $headerRow = 6;
        $headers = [
            'Purchase Number',
            'Purchase Date',
            'Supplier Name',
            'Product',
            'Quantity Purchased',
            'Unit Price',
            'Total Amount',
            'Payment Term',
            'Remarks',
        ];

        // Write table headers
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Fill data
        $row = $headerRow + 1;
        $totalAmount = 0;

        foreach ($purchases as $record) {
            $sheet->setCellValue("A{$row}", $record->po_number ?? '');
            $sheet->setCellValue("B{$row}", isset($record->purchase_date) 
                ? \Carbon\Carbon::parse($record->purchase_date)->format('M d, Y') 
                : '');
            $sheet->setCellValue("C{$row}", $record->supplier_name ?? '');
            $sheet->setCellValue("D{$row}", $record->product_name ?? '');
            $sheet->setCellValue("E{$row}", $record->quantity ?? 0);
            $sheet->setCellValue("F{$row}", $record->unit_price ?? 0);
            $sheet->setCellValue("G{$row}", $record->total_amount ?? 0);
            $paymentName = strtolower($record->name);
            if (in_array($paymentName, ['cash', 'gcash'])) {
                $sheet->setCellValue("H{$row}", $record->name);
            } else {
                $termText = $record->term ? "{$record->name} ({$record->term} Days)" : $record->name;
                $sheet->setCellValue("H{$row}", $termText);
            }
            $sheet->setCellValue("I{$row}", $record->remarks ?? '');

            $totalAmount += $record->total_amount ?? 0;
            $row++;
        }

        // Add summary row (totals)
        $sheet->setCellValue("F{$row}", "Grand Total:");
        $sheet->setCellValue("G{$row}", $totalAmount);
        $sheet->getStyle("F{$row}:G{$row}")->getFont()->setBold(true);

        // Add borders around data
        $sheet->getStyle("A{$headerRow}:I{$row}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Output file
        $fileName = 'purchase_report_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
    }

    public function collection_report(Request $request)
    {
        $salesman   = $request->salesman ?? null;
        $customerId = $request->customer_id ?? null;
        $productId  = $request->product_id ?? null;
        $startDate  = Carbon::createFromFormat('F d, Y', $request->start_date)->toDateString() ?? null;
        $endDate    = Carbon::createFromFormat('F d, Y', $request->end_date)->toDateString() ?? null;

        // Call stored procedure (we'll define next)
        $reportData = DB::select('CALL get_collection_report(?, ?, ?, ?, ?)', [
            $salesman,
            $customerId,
            $productId,
            $startDate,
            $endDate,
        ]);

        // Get filter dropdown options
        $customers = DB::table('customers')->select('id', 'name')->orderBy('name')->get();
        $products = DB::table('products')->select('id', 'product_name')->orderBy('product_name')->get();

        // Get unique salesmen from invoices
        $salesmen = DB::table('invoices')
            ->select('salesman')
            ->whereNotNull('salesman')
            ->distinct()
            ->orderBy('salesman')
            ->get();

        return view('reports.collection_report', compact('reportData', 'salesmen', 'customers', 'products', 'startDate', 'endDate'));
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

