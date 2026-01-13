<?php
namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductSupplier;
use App\Models\Invoice;
use App\Models\Collection;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\ModeofPayment;
use App\Models\Salesman;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ExportController extends Controller
{
    public function exportCustomers()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->addHeader($sheet, 'Customer List');

        // Style: Company name
        $sheet->getStyle('B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Style: Address
        $sheet->getStyle('B2')->applyFromArray([
            'font' => ['bold' => false, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Style: Subtitle
        $sheet->getStyle('B3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Leave a row for spacing
        $headerRow = 5;

        // Column headers
        $headers = ['Customer Code','Customer', 'Address', 'Contact', 'Email', 'Tax No.', 'Details'];
        $sheet->fromArray($headers, null, 'A' . $headerRow);

        // Style header row
        $sheet->getStyle('A' . $headerRow . ':I' . $headerRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Populate customer data
        $customers = Customer::all();
        $row = $headerRow + 1;
        foreach ($customers as $customer) {
            $sheet->setCellValue('A' . $row, $customer->customer_code);
            $sheet->setCellValue('B' . $row, $customer->name);
            $sheet->setCellValue('C' . $row, $customer->address);
            $sheet->setCellValue('D' . $row, $customer->mobile);
            $sheet->setCellValue('E' . $row, $customer->email);
            $sheet->setCellValue('F' . $row, $customer->tax);
            $sheet->setCellValue('G' . $row, $customer->details);
            $sheet->getStyle('G' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            // Apply borders to each row
            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
            

            $row++;
        }

        // Auto-size columns A–E
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $fileName = 'avthardwaretrading_customers_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    public function exportSalesman()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->addHeader($sheet, 'Salesman List');

        // Style: Company name
        $sheet->getStyle('B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Style: Address
        $sheet->getStyle('B2')->applyFromArray([
            'font' => ['bold' => false, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Style: Subtitle
        $sheet->getStyle('B3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Leave a row for spacing
        $headerRow = 5;

        // Column headers
        $headers = ['Saleman Code','Salesman', 'Address', 'Contact', 'Email', 'Status'];
        $sheet->fromArray($headers, null, 'A' . $headerRow);

        // Style header row
        $sheet->getStyle('A' . $headerRow . ':I' . $headerRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Populate customer data
        $salesmen = Salesman::all();
        $row = $headerRow + 1;
        foreach ($salesmen as $salesman) {
            $sheet->setCellValue('A' . $row, $salesman->salesman_code);
            $sheet->setCellValue('B' . $row, $salesman->salesman_name);
            $sheet->setCellValue('C' . $row, $salesman->address);
            $sheet->setCellValue('D' . $row, $salesman->phone);
            $sheet->setCellValue('E' . $row, $salesman->email);
            $sheet->setCellValue('F' . $row, $salesman->status == 1 ? 'Active' : 'Inactive');
            // Apply borders to each row
            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);
            
            $row++;
        }

        // Auto-size columns A–E
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $fileName = 'avthardwaretrading_salesman_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    public function exportUsers()
    {
        $users = \App\Models\User::all();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Role');

        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue("A{$row}", $user->name);
            $sheet->setCellValue("B{$row}", $user->email);
            $sheet->setCellValue("C{$row}", $user->role);
            $row++;
        }

        return $this->downloadExcel($spreadsheet, 'avthardwaretrading_users.xlsx');
    }

    public function exportProducts()
    {
        $groupedProducts = Product::with(['category', 'suppliers'])->get()->groupBy('category.name');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add header / logo (if exists)
        $this->addHeader($sheet, 'Product List');

        $row = 5;

        foreach ($groupedProducts as $categoryName => $products) {
            $sheet->setCellValue('A' . $row, 'Category: ' . $categoryName);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;

            // Headers
            $headers = [
                'Image', 'Product Code', 'Name', 'Serial Number', 'Model', 'Category',
                'Sales Price', 'Quantity', 'Remaining Stock', 'Threshold', 'Status',
                'Supplier', 'Price', 'Adjustments', 'Remarks', 'Adjustment Status'
            ];

            $colIndex = 1;
            foreach ($headers as $header) {
                $cell = Coordinate::stringFromColumnIndex($colIndex) . $row;
                $sheet->setCellValue($cell, $header);
                $sheet->getStyle($cell)->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                ]);
                $colIndex++;
            }

            $row++;

            foreach ($products as $product) {
                foreach ($product->suppliers as $supplier) {
                    $colIndex = 1;

                    // Image
                    if ($product->image) {
                        $imagePath = public_path('images/product/' . $product->image);
                        if (file_exists($imagePath)) {
                            $drawing = new Drawing();
                            $drawing->setPath($imagePath);
                            $drawing->setHeight(50);
                            $drawing->setCoordinates(Coordinate::stringFromColumnIndex($colIndex) . $row);
                            $drawing->setWorksheet($sheet);
                        }
                    }

                    $colIndex++; // Move after image

                    // Parse JSON adjustments
                    $adjustments = $product->adjustments ? json_decode($product->adjustments, true) : [];
                    $adjustmentText = '';
                    $remarksText = '';
                    $statusText = '';

                    foreach ($adjustments as $adj) {
                        $adjustmentText .= ($adj['adjustment'] ?? '') . "\n";
                        $remarksText .= ($adj['remarks'] ?? '') . "\n";
                        $statusText .= ($adj['adjustment_status'] ?? '') . "\n";
                    }

                    $data = [
                        $product->product_code,
                        $product->product_name,
                        $product->serial_number,
                        $product->model,
                        $product->category->name ?? '',
                        $product->sales_price,
                        $product->quantity,
                        $product->remaining_stock,
                        $product->threshold,
                        $product->status,
                        $supplier->name . ' ' . ($supplier->code ?? ''),
                        $supplier->pivot->price,
                        trim($adjustmentText),
                        trim($remarksText),
                        trim($statusText),
                    ];

                    foreach ($data as $value) {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $row, $value);
                    }

                    $row++;
                }
            }

            $row++;
        }

        // Output file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'avthardwaretrading_products_' . now()->format('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
    }

    //Export Invoice
    public function exportInvoices()
{
    $statuses = ['pending', 'approved', 'printed'];
    $spreadsheet = new Spreadsheet();

    // Fetch all invoices with customers and items
    $invoices = Invoice::with(['customer', 'items.product'])
        ->orderBy('customer_id')
        ->orderBy('invoice_date')
        ->get();

    // Group invoices by location
    $invoicesByLocation = $invoices->groupBy(function ($invoice) {
        return $invoice->customer->location ?? 'N/A';
    });

    // === SUMMARY SHEET WITH ALL INVOICES PER CUSTOMER ===
    $summarySheet = $spreadsheet->createSheet(0);
    $summarySheet->setTitle('Summary Invoices');
    $row = 2;

    $summarySheet->setCellValue('A' . $row, 'Location');
    $summarySheet->setCellValue('B' . $row, 'DR Number');
    $summarySheet->setCellValue('C' . $row, 'Customer');
    $summarySheet->setCellValue('D' . $row, 'Invoice #');
    $summarySheet->setCellValue('E' . $row, 'Status');
    $summarySheet->setCellValue('F' . $row, 'Grand Total');
    $summarySheet->setCellValue('G' . $row, 'Date');
    $summarySheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
    $row++;

    foreach ($invoicesByLocation as $location => $locationInvoices) {
        foreach ($locationInvoices as $invoice) {
            $customerName = $invoice->customer->name ?? 'N/A';
            $drNumber = $invoice->invoice_number ?? ($invoice->doctor->invoice_number ?? 'N/A');
            $invoiceNumber = $invoice->invoice_number ?? $invoice->id;
            $status = $invoice->invoice_status ?? 'N/A';
            $grandTotal = $invoice->grand_total ?? 0;
            $invoiceDate = $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : '';

            $summarySheet->setCellValue('A' . $row, $location);
            $summarySheet->setCellValue('B' . $row, $drNumber);
            $summarySheet->setCellValue('C' . $row, $customerName);
            $summarySheet->setCellValue('D' . $row, $invoiceNumber);
            $summarySheet->setCellValue('E' . $row, $status);
            $summarySheet->setCellValue('F' . $row, $grandTotal); // numeric
            $summarySheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $summarySheet->setCellValue('G' . $row, $invoiceDate);

            // Highlight grand total
            $summarySheet->getStyle('F' . $row)->getFont()->setBold(true);
            $summarySheet->getStyle('F' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFF00');

            $row++;
        }
    }

    foreach (range('A', 'G') as $col) {
        $summarySheet->getColumnDimension($col)->setAutoSize(true);
    }

    // === PER-LOCATION SHEETS ===
    foreach ($invoicesByLocation as $location => $locationInvoices) {
        $safeLocation = substr(preg_replace('/[:\\\\\/\?\*\[\]]/', '', $location), 0, 31);
        if (empty($safeLocation)) $safeLocation = 'Unknown';

        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($safeLocation);
        $row = 6;

        $this->addHeader($sheet, "Invoice List for Location: $location");

        foreach ($statuses as $status) {
            $statusColor = match($status) {
                'pending' => 'FFFFCC',
                'approved' => 'CCFFCC',
                'printed' => 'CCCCFF',
                default => 'FFFFFF',
            };

            $sheet->setCellValue('A' . $row, strtoupper($status) . ' INVOICES');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A' . $row . ':E' . $row)
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($statusColor);
            $row += 2;

            $statusInvoices = $locationInvoices->where('invoice_status', $status);
            if ($statusInvoices->isEmpty()) {
                $sheet->setCellValue('A' . $row, '(no invoices)');
                $row += 2;
                continue;
            }

            $totalsPerCustomer = [];
            $currentCustomer = null;
            $customerSubtotal = 0;

            foreach ($statusInvoices as $invoice) {
                $customerName = $invoice->customer->name ?? 'N/A';
                $grandTotal = $invoice->grand_total ?? 0;

                $totalsPerCustomer[$customerName] = ($totalsPerCustomer[$customerName] ?? 0) + $grandTotal;

                if ($currentCustomer && $currentCustomer !== $customerName) {
                    $sheet->setCellValue('C' . $row, 'Subtotal for ' . $currentCustomer);
                    $sheet->setCellValue('E' . $row, $customerSubtotal);
                    $sheet->getStyle('C' . $row . ':E' . $row)->getFont()->setBold(true);
                    $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                    $row += 2;
                    $customerSubtotal = 0;
                }
                $currentCustomer = $customerName;

                $sheet->setCellValue('A' . $row, 'Invoice #: ' . ($invoice->invoice_number ?? $invoice->id));
                $sheet->setCellValue('C' . $row, 'Customer: ' . $customerName);
                $sheet->setCellValue('E' . $row, 'Date: ' . ($invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : ''));
                $sheet->getStyle('A' . $row . ':E' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($statusColor);
                $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
                $row++;

                $sheet->setCellValue('A' . $row, 'Status: ' . ($invoice->invoice_status ?? ''));
                $sheet->setCellValue('C' . $row, 'Grand Total: ' . $grandTotal);
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('A' . $row . ':E' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($statusColor);
                $row++;

                // Items header
                $sheet->setCellValue('A' . $row, 'Product');
                $sheet->setCellValue('B' . $row, 'Qty');
                $sheet->setCellValue('C' . $row, 'Unit Price');
                $sheet->setCellValue('D' . $row, 'Discount');
                $sheet->setCellValue('E' . $row, 'Amount');
                $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
                $row++;

                foreach ($invoice->items ?? [] as $item) {
                    $productName = $item->product->product_name ?? $item->product->name ?? 'Unknown Product';
                    $qty = $item->qty ?? $item->quantity ?? 0;
                    $unitPrice = $item->price ?? $item->unit_price ?? 0;
                    $discount = $item->discount ?? 0;
                    $amount = $item->amount ?? ($qty * $unitPrice - $discount);

                    $sheet->setCellValue('A' . $row, $productName);
                    $sheet->setCellValue('B' . $row, $qty);
                    $sheet->setCellValue('C' . $row, $unitPrice);
                    $sheet->setCellValue('D' . $row, $discount);
                    $sheet->setCellValue('E' . $row, $amount);

                    $sheet->getStyle('C' . $row . ':E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle('A' . $row . ':E' . $row)
                        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($statusColor);
                    $row++;
                }

                $customerSubtotal += $grandTotal;
                $row += 2;
            }

            // Final subtotal for last customer
            if ($currentCustomer) {
                $sheet->setCellValue('C' . $row, 'Subtotal for ' . $currentCustomer);
                $sheet->setCellValue('E' . $row, $customerSubtotal);
                $sheet->getStyle('C' . $row . ':E' . $row)->getFont()->setBold(true);
                $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $row += 2;
            }

            // Grand totals per customer
            $sheet->setCellValue('A' . $row, 'Grand Totals Per Customer (' . strtoupper($status) . ')');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue('A' . $row, 'Customer');
            $sheet->setCellValue('B' . $row, 'Total');
            $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
            $row++;
            foreach ($totalsPerCustomer as $customer => $total) {
                $sheet->setCellValue('A' . $row, $customer);
                $sheet->setCellValue('B' . $row, $total);
                $sheet->getStyle('B' . $row)->getFont()->setBold(true);
                $sheet->getStyle('B' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                $row++;
            }

            // Grand total for this status in location
            $totalForLocation = $statusInvoices->sum('grand_total'); // only this status
            $sheet->setCellValue('A' . $row, 'Grand Total for Location (' . strtoupper($status) . ')');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue('A' . $row, $location);
            $sheet->setCellValue('B' . $row, $totalForLocation);
            $sheet->getStyle('B' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('B' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
            $row += 4;

            foreach (range('A', 'E') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    // Remove default empty sheet if exists
    if ($spreadsheet->getSheetCount() > 1 && $spreadsheet->getSheet(0)->getTitle() === 'Worksheet') {
        $spreadsheet->removeSheetByIndex(0);
    }

    $writer = new Xlsx($spreadsheet);
    $fileName = 'invoices_by_location_' . now()->format('Ymd_His') . '.xlsx';
    $tempFile = tempnam(sys_get_temp_dir(), 'invoices_export_') . '.xlsx';
    $writer->save($tempFile);

    return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
}


     /**
     * Export purchase orders.
     */
    public function exportPurchases()
    {
        $purchases = Purchase::with([
            'supplier',
            'items.supplierItem',
            'payments',
            'paymentMode'
        ])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->addHeader($sheet, 'Purchases Export');

        // Table headers
        $sheet->setCellValue('A6', 'PO #');
        $sheet->setCellValue('B6', 'Supplier');
        $sheet->setCellValue('C6', 'Date');
        $sheet->setCellValue('D6', 'Product Code');
        $sheet->setCellValue('E6', 'Product');
        $sheet->setCellValue('F6', 'Quantity');
        $sheet->setCellValue('G6', 'Price');
        $sheet->setCellValue('H6', 'Amount');
        $sheet->setCellValue('I6', 'Total Paid');
        $sheet->setCellValue('J6', 'Outstanding Balance');
        $sheet->setCellValue('K6', 'Payment Method');
        $sheet->setCellValue('L6', 'Payment Terms');
        $sheet->setCellValue('M6', 'Payment Status');

        $row = 7;

        foreach ($purchases as $purchase) {
            // Calculate totals and outstanding balance
            $totalPaid = $purchase->payments->sum('amount_paid');
            $outstandingBalance = $purchase->grand_total - $totalPaid;

            // Determine payment status
            if ($totalPaid <= 0) {
                $status = 'Unpaid';
            } elseif ($totalPaid < $purchase->grand_total) {
                $status = 'Partial Payment';
            } else {
                $status = 'Fully Paid';
            }

            // Get payment details
            $paymentMethod = $purchase->paymentMode->name ?? '-';
            $paymentTerms = $purchase->paymentMode->term ?? '-';

            foreach ($purchase->items as $item) {
                $sheet->setCellValue("A{$row}", $purchase->po_number);
                $sheet->setCellValue("B{$row}", $purchase->supplier->name ?? 'N/A');
                $sheet->setCellValue("C{$row}", $purchase->created_at->format('Y-m-d'));
                $sheet->setCellValue("D{$row}", $item->product_code ?? 'N/A');
                $sheet->setCellValue("E{$row}", $item->supplierItem->item_description ?? 'N/A');
                $sheet->setCellValue("F{$row}", $item->qty);
                $sheet->setCellValue("G{$row}", number_format($item->unit_price, 2));
                $sheet->setCellValue("H{$row}", number_format($item->total, 2));
                $sheet->setCellValue("I{$row}", number_format($totalPaid, 2));
                $sheet->setCellValue("J{$row}", number_format($outstandingBalance, 2));
                $sheet->setCellValue("K{$row}", $paymentMethod);
                $sheet->setCellValue("L{$row}", $paymentTerms);
                $sheet->setCellValue("M{$row}", $status);
                $row++;
            }
        }

        // Auto-size columns for readability
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Export the Excel file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'purchases_export_' . now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'purchase_export_') . '.xlsx';
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }


    /**
     * Export payment collections.
     */
    public function exportCollections()
    {
        $collections = Collection::with(['invoice.customer', 'invoice.collections','invoice.paymentMode'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->addHeader($sheet, 'Collections');

        // Table headers (Row 6)
        $sheet->setCellValue('A6', 'Collection ID');
        $sheet->setCellValue('B6', 'Customer');
        $sheet->setCellValue('C6', 'Invoice #');
        $sheet->setCellValue('D6', 'Mode of Payment');
        $sheet->setCellValue('E6', 'Amount Paid');
        $sheet->setCellValue('F6', 'Outstanding Balance');
        $sheet->setCellValue('G6', 'Collection Date');

        $row = 7;

        foreach ($collections as $col) {
            $outstandingBalance = 0;
            if ($col->invoice) {
                $outstandingBalance = ($col->invoice->grand_total ?? 0) - ($col->invoice->collections->sum('amount_paid') ?? 0);
            }

            $sheet->setCellValue("A{$row}", $col->collection_number ?? $col->id);
            $sheet->setCellValue("B{$row}", $col->invoice->customer->name ?? 'N/A');
            $sheet->setCellValue("C{$row}", $col->invoice->invoice_number ?? 'N/A');
            $sheet->setCellValue("D{$row}", $col->invoice->paymentMode->name ?? 'N/A');
            $sheet->setCellValue("E{$row}", number_format($col->amount_paid, 2));
            $sheet->setCellValue("F{$row}", number_format($outstandingBalance, 2));
            $sheet->setCellValue("G{$row}", $col->created_at->format('Y-m-d'));

            $row++;
        }

        // Write file to temp and return download
        $writer = new Xlsx($spreadsheet);
        $fileName = 'collection_export_' . now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'collection_export_') . '.xlsx'; // ensure .xlsx
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
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
            $drawing->setWorksheet($sheet);
        }

        $sheet->setCellValue('C1', 'AVT Hardware Trading');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->setCellValue('C2', '123 Main St., Test, City');
        $sheet->getStyle('C2')->getFont()->setSize(12);

        $sheet->setCellValue('C3', $title);
        $sheet->getStyle('C3')->getFont()->setBold(true)->setSize(14);
    }



    // Shared function
    private function downloadExcel(Spreadsheet $spreadsheet, $filename)
    {
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp_file);
        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }
}
