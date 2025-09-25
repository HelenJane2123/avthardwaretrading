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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add logo and header
        $this->addHeader($sheet, 'AR Aging Report');

        // Table headers (row 5 for spacing)
        $headers = [
            'Customer Code','Customer','Invoice #','Invoice Date','Due Date',
            'Invoice Amount','Outstanding','Amount Paid','Collection Date',
            'Remarks','Payment Method','Payment Term','Aging Bucket'
        ];

        $col = 'A';
        $headerRow = 5;
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Sort results by customer name for grouping
        usort($results, fn($a, $b) => strcmp($a->customer_name, $b->customer_name));

        // Initialize
        $row = $headerRow + 1;
        $currentCustomer = null;
        $subtotalInvoice = 0;
        $subtotalPaid = 0;
        $subtotalOutstanding = 0;

        foreach ($results as $record) {
            // Insert subtotal row when customer changes
            if ($currentCustomer && $currentCustomer !== $record->customer_name) {
                $sheet->setCellValue("C{$row}", "Subtotal for $currentCustomer");
                $sheet->setCellValue("F{$row}", $subtotalInvoice);
                $sheet->setCellValue("G{$row}", $subtotalOutstanding);
                $sheet->setCellValue("H{$row}", $subtotalPaid);
                $sheet->getStyle("A{$row}:M{$row}")->getFont()->setBold(true);
                $row++;

                // Reset subtotals
                $subtotalInvoice = $subtotalPaid = $subtotalOutstanding = 0;
            }

            $currentCustomer = $record->customer_name;

            // Fill row
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

            // Compute Outstanding dynamically in Excel formula
            $sheet->setCellValue("G{$row}", "=F{$row}-H{$row}");

            $sheet->setCellValue("J{$row}", $record->collection_remarks ?? '');
            $sheet->setCellValue("K{$row}", $record->payment_method ?? '');
            $sheet->setCellValue("L{$row}", $record->payment_term ?? '');
            $sheet->setCellValue("M{$row}", $record->aging_bucket ?? '');

            // Add to subtotals
            $subtotalInvoice += $record->invoice_amount ?? 0;
            $subtotalPaid += $record->amount_paid ?? 0;
            $subtotalOutstanding += ($record->invoice_amount ?? 0) - ($record->amount_paid ?? 0);

            $row++;
        }

        // Final subtotal for last customer
        if ($currentCustomer) {
            $sheet->setCellValue("C{$row}", "Subtotal for $currentCustomer");
            $sheet->setCellValue("F{$row}", $subtotalInvoice);
            $sheet->setCellValue("G{$row}", $subtotalOutstanding);
            $sheet->setCellValue("H{$row}", $subtotalPaid);
            $sheet->getStyle("A{$row}:M{$row}")->getFont()->setBold(true);
        }

        $lastRow = $row;

        // Format date columns
        foreach (['D','E','I'] as $col) {
            $sheet->getStyle("{$col}".($headerRow+1).":{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('[$-en-US]mmmm d, yyyy');
        }

        // Format amounts
        foreach (['F','G','H'] as $col) {
            $sheet->getStyle("{$col}".($headerRow+1).":{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Add borders
        $sheet->getStyle("A{$headerRow}:M{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Export
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

        // Call stored procedure to get AP Aging data
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

        // Add logo and header using your private function
        $this->addHeader($sheet, 'AP Aging Report');

        // Sort results by supplier name
        usort($results, fn($a, $b) => strcmp($a->supplier_name, $b->supplier_name));

        // Table headers
        $headers = [
            'Supplier Code','Supplier','Purchase #','Purchase Date','Purchase Amount',
            'Outstanding','Amount Paid','Payment Date','Payment Method','Payment Status',
            'Payment Term','Aging Bucket'
        ];

        $col = 'A';
        $headerRow = 5; // Start below your header (so logo/header have space)
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $sheet->getStyle($col.$headerRow)->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Initialize
        $row = $headerRow + 1;
        $currentSupplier = null;
        $subtotalInvoice = 0;
        $subtotalPaid = 0;
        $subtotalOutstanding = 0;

        foreach ($results as $record) {
            // Insert subtotal row if supplier changes
            if ($currentSupplier && $currentSupplier !== $record->supplier_name) {
                $sheet->setCellValue("C{$row}", "Subtotal for $currentSupplier");
                $sheet->setCellValue("E{$row}", $subtotalInvoice);
                $sheet->setCellValue("G{$row}", $subtotalPaid);
                $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
                $row++;

                // Reset subtotals
                $subtotalInvoice = $subtotalPaid = $subtotalOutstanding = 0;
            }

            $currentSupplier = $record->supplier_name;

            // Fill row data
            $sheet->setCellValue("A{$row}", $record->supplier_code ?? '');
            $sheet->setCellValue("B{$row}", $record->supplier_name ?? '');
            $sheet->setCellValue("C{$row}", $record->purchase_number ?? '');
            if (!empty($record->purchase_date)) {
                $sheet->setCellValue("D{$row}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                    \Carbon\Carbon::parse($record->purchase_date)->toDateTime()
                ));
            }
            $sheet->setCellValue("E{$row}", $record->purchase_amount ?? 0);
            $sheet->setCellValue("F{$row}", $record->outstanding_balance ?? 0);
            $sheet->setCellValue("G{$row}", $record->amount_paid ?? 0);
            if (!empty($record->payment_date)) {
                $sheet->setCellValue("H{$row}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                    \Carbon\Carbon::parse($record->payment_date)->toDateTime()
                ));
            }
            $sheet->setCellValue("I{$row}", $record->payment_method ?? '');
            $sheet->setCellValue("J{$row}", $record->payment_status ?? '');
            $sheet->setCellValue("K{$row}", $record->payment_term ?? '');
            $sheet->setCellValue("L{$row}", $record->aging_bucket ?? '');

            // Add to subtotals
            $subtotalInvoice += $record->purchase_amount ?? 0;
            $subtotalPaid += $record->amount_paid ?? 0;
            $subtotalOutstanding += $record->outstanding_balance ?? 0;

            $row++;
        }

        // Add final subtotal for last supplier
        if ($currentSupplier) {
            $sheet->setCellValue("C{$row}", "Subtotal for $currentSupplier");
            $sheet->setCellValue("E{$row}", $subtotalInvoice);
            $sheet->setCellValue("G{$row}", $subtotalPaid);
            $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
        }

        $lastRow = $row;

        // Format date columns
        foreach (['D','H'] as $col) {
            $sheet->getStyle("{$col}{$headerRow}:{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('[$-en-US]mmmm d, yyyy');
        }

        // Format amount columns
        foreach (['E','F','G'] as $col) {
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

        // Call your stored procedure for inventory report
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

        // Table headers
        $headers = [
            'Product ID',
            'Product Code',
            'Product Name',
            'Category',
            'Unit',
            'Supplier',
            'Sales Price',
            'Quantity',
            'Threshold',
            'Remaining Stock',
            'Status'
        ];

        $col = 'A';
        $headerRow = 2; // put table headers at row 2
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
            $sheet->setCellValue("G{$row}", $record->sales_price ?? 0);
            $sheet->setCellValue("H{$row}", $record->quantity ?? 0);
            $sheet->setCellValue("I{$row}", $record->threshold ?? 0);
            $sheet->setCellValue("J{$row}", $record->remaining_stock ?? 0);
            $sheet->setCellValue("K{$row}", $record->product_status ?? '');
            $row++;
        }

        $lastRow = $row - 1;

        // Format numeric columns
        foreach (['G','H','I','J'] as $col) {
            $sheet->getStyle("{$col}{$headerRow}:{$col}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Add borders
        $sheet->getStyle("A{$headerRow}:K{$lastRow}")->applyFromArray([
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



    /**
     * Helper: add logo and header
     */
    private function addHeader($sheet, $title)
    {
        $logoPath = public_path('images/avt_logo.png');
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }

        $sheet->setCellValue('C1', 'AVT Hardware Trading');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->setCellValue('C2', '123 Main St., Test, City');
        $sheet->getStyle('C2')->getFont()->setSize(12);

        $sheet->setCellValue('C3', $title);
        $sheet->getStyle('C3')->getFont()->setBold(true)->setSize(14);
    }
}

