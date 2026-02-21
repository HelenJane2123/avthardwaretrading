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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportController extends Controller
{
    public function exportCustomers()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->addHeader($sheet, 'Customer List');

        $sheet->getStyle('B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('B2')->applyFromArray([
            'font' => ['bold' => false, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle('B3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headerRow = 5;

        $headers = ['Customer Code','Customer', 'Address', 'Contact', 'Email', 'Tax No.', 'Details'];
        $sheet->fromArray($headers, null, 'A' . $headerRow);

        $sheet->getStyle('A' . $headerRow . ':I' . $headerRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

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

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

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

     /**
     * Export invoice orders.
     */
    public function exportInvoices()
    {
        $spreadsheet = new Spreadsheet();

        $invoices = Invoice::with(['customer', 'items.product', 'salesman_relation'])
            ->where('invoice_status', 'printed')
            ->orderByRaw('CAST(invoice_number AS UNSIGNED) ASC')
            ->get();

        // --- SUMMARY SHEET ---
        $summarySheet = $spreadsheet->createSheet(0);
        $summarySheet->setTitle('Summary Invoices');

        $summaryRow = 2;
        $summaryHeaders = ['DR Number', 'Date', 'Customer', 'Salesman', 'Grand Total'];
        $summarySheet->fromArray($summaryHeaders, null, "A{$summaryRow}");
        $summarySheet->getStyle("A{$summaryRow}:E{$summaryRow}")->getFont()->setBold(true);
        $summaryRow++;

        $totalSales = 0;
        foreach ($invoices->sortBy('invoice_number') as $invoice) {
            $drNumber = $invoice->invoice_number ?? 'N/A';
            $date = $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('F j, Y') : '';
            $customer = $invoice->customer->name ?? 'N/A';
            $salesman = optional($invoice->salesman_relation)->salesman_name ?? 'N/A';
            $grandTotal = $invoice->grand_total ?? 0;

            $summarySheet->setCellValue("A{$summaryRow}", $drNumber);
            $summarySheet->setCellValue("B{$summaryRow}", $date);
            $summarySheet->setCellValue("C{$summaryRow}", $customer);
            $summarySheet->setCellValue("D{$summaryRow}", $salesman);
            $summarySheet->setCellValue("E{$summaryRow}", $grandTotal);

            $summarySheet->getStyle("E{$summaryRow}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $totalSales += $grandTotal;
            $summaryRow++;
        }

        $summarySheet->setCellValue("E1", "Total Sales");
        $summarySheet->setCellValue("F1", $totalSales);
        $summarySheet->getStyle("E1:F1")->getFont()->setBold(true);
        $summarySheet->getStyle("F1")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $summarySheet->getStyle("E1:F1")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF99');

        foreach (range('A', 'E') as $col) {
            $summarySheet->getColumnDimension($col)->setAutoSize(true);
        }

        // --- LOCATION SHEETS ---
        $invoicesByLocation = $invoices->groupBy(fn($invoice) => $invoice->customer->location ?? 'N/A');

        foreach ($invoicesByLocation as $location => $locationInvoices) {

            $safeLocation = substr(preg_replace('/[:\\\\\/\?\*\[\]]/', '', $location), 0, 31);
            if (empty($safeLocation)) $safeLocation = 'Unknown';

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($safeLocation);

            $row = 1;

            // Title
            $sheet->setCellValue("A{$row}", "PRINTED INVOICE REPORT - {$location}");
            $sheet->mergeCells("A{$row}:L{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row += 2;

            // --- Total Grand Total for this sheet ---
            $locationGrandTotal = $locationInvoices->sum(fn($i) => $i->grand_total ?? 0);
            $sheet->setCellValue("K{$row}", "TOTAL GRAND TOTAL:");
            $sheet->setCellValue("L{$row}", $locationGrandTotal);
            $sheet->getStyle("K{$row}:L{$row}")->getFont()->setBold(true);
            $sheet->getStyle("L{$row}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle("K{$row}:L{$row}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFF99');

            $row += 2;

            // Headers
            $headers = [
                'Date',
                'Invoice #',
                'Customer',
                'Salesman',
                'Description',
                'Qty',
                'Price',
                'Total',
                'Discount %',
                'Discount Amount',
                'Sub Total',
                'Grand Total'
            ];

            $sheet->fromArray($headers, null, "A{$row}");
            $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
            $sheet->freezePane("A" . ($row + 1));
            $row++;

            $startDataRow = $row;

            foreach ($locationInvoices->sortBy('invoice_number') as $invoice) {

                $formattedDate = \Carbon\Carbon::parse($invoice->invoice_date)
                    ->format('F j, Y');

                $invoiceNumber = $invoice->invoice_number;
                $customer = $invoice->customer->name ?? 'N/A';
                $salesman = optional($invoice->salesman_relation)->salesman_name ?? 'N/A';
                $invoiceTotal = $invoice->grand_total ?? 0;

                $firstRowOfInvoice = true;

                foreach ($invoice->items ?? [] as $item) {

                    $product = $item->product->product_name
                        ?? $item->product->name
                        ?? 'Unknown';

                    $qty = $item->qty ?? 0;
                    $price = $item->price ?? 0;
                    $gross = $qty * $price;

                    $discounts = array_filter([
                        $item->discount_1 ?? 0,
                        $item->discount_2 ?? 0,
                        $item->discount_3 ?? 0
                    ], fn($d) => $d != 0);

                    $discountType = $item->discount_less_add ?? 'less';
                    $net = $item->amount;
                    $discountDisplay = '';
                    if (!empty($discounts)) {
                        $discountDisplay = implode(', ', array_map(function ($d) use ($discountType) {
                            $percent = floor($d) == $d ? number_format($d, 0) : number_format($d, 2);
                            return $percent . '% (' . ucfirst($discountType) . ')';
                        }, $discounts));
                    }
                    if ($discountType === 'add') {
                        $discountAmount = $net - $gross;
                    } else {
                        $discountAmount = $gross - $net;
                    }

                    if ($firstRowOfInvoice) {
                        $sheet->setCellValue("A{$row}", $formattedDate);
                        $sheet->setCellValue("B{$row}", $invoiceNumber);
                        $sheet->setCellValue("C{$row}", $customer);
                        $sheet->setCellValue("D{$row}", $salesman);
                        $firstRowOfInvoice = false;
                    }

                    $sheet->setCellValue("E{$row}", $product);
                    $sheet->setCellValue("F{$row}", $qty);
                    $sheet->setCellValue("G{$row}", $price);
                    $sheet->setCellValue("H{$row}", $gross);
                    $sheet->setCellValue("I{$row}", $discountDisplay);
                    $sheet->setCellValue("J{$row}", $discountAmount);
                    $sheet->setCellValue("K{$row}", $net);

                    $sheet->getStyle("F{$row}:K{$row}")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                    $row++;
                }

                // GRAND TOTAL ROW per invoice
                $sheet->setCellValue("K{$row}", "GRAND TOTAL:");
                $sheet->setCellValue("L{$row}", $invoiceTotal);
                $sheet->getStyle("K{$row}:L{$row}")->getFont()->setBold(true);
                $sheet->getStyle("L{$row}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("K{$row}:L{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF99');

                $row += 2;
            }

            // BORDERS
            $sheet->getStyle("A" . ($startDataRow - 1) . ":L" . ($row - 1))
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // Remove default empty sheet if exists
        if ($spreadsheet->getSheetCount() > 1 && $spreadsheet->getSheet(1)->getTitle() === 'Worksheet') {
            $spreadsheet->removeSheetByIndex(1);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'printed_invoices_' . now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'invoices_export_') . '.xlsx';
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

     /**
     * Export purchase orders.
     */
    public function exportPurchases()
    {
        $companyName = 'AVT Hardware Trading';

        $purchases = Purchase::with([
            'supplier',
            'items.supplierItem',
            'payments',
            'paymentMode'
        ])->orderByRaw('CAST(po_number AS UNSIGNED) ASC')->get();

        $spreadsheet = new Spreadsheet();

        // ===== SUMMARY SHEET =====
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Summary Purchases');

        $summaryHeaders = ['PO Number', 'Date', 'Supplier', 'Payment Method', 'Grand Total'];
        $summarySheet->fromArray($summaryHeaders, null, "A1");
        $summarySheet->getStyle("A1:E1")->getFont()->setBold(true);

        $summaryRow = 2;
        $totalPurchases = 0;

        foreach ($purchases->sortBy('po_number') as $purchase) {
            $poNumber = $purchase->po_number;
            $date = $purchase->created_at ? \Carbon\Carbon::parse($purchase->created_at)->format('F j, Y') : '';
            $supplier = $purchase->supplier->name ?? 'N/A';
            $paymentMethod = $purchase->paymentMode->name ?? 'N/A';
            $grandTotal = $purchase->grand_total ?? 0;

            $summarySheet->setCellValue("A{$summaryRow}", $poNumber);
            $summarySheet->setCellValue("B{$summaryRow}", $date);
            $summarySheet->setCellValue("C{$summaryRow}", $supplier);
            $summarySheet->setCellValue("D{$summaryRow}", $paymentMethod);
            $summarySheet->setCellValue("E{$summaryRow}", $grandTotal);

            $summarySheet->getStyle("E{$summaryRow}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

            $totalPurchases += $grandTotal;
            $summaryRow++;
        }

        // Total Purchases at top
        $summarySheet->setCellValue("D1", "Total Purchases");
        $summarySheet->setCellValue("E1", $totalPurchases);
        $summarySheet->getStyle("D1:E1")->getFont()->setBold(true);
        $summarySheet->getStyle("E1")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $summarySheet->getStyle("D1:E1")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF99');

        foreach (range('A', 'E') as $col) {
            $summarySheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ===== SUPPLIER SHEETS =====
        $purchasesBySupplier = $purchases->groupBy(fn($p) => $p->supplier->name ?? 'N/A');

        foreach ($purchasesBySupplier as $supplierName => $supplierPurchases) {
            $safeSupplier = substr(preg_replace('/[:\\\\\/\?\*\[\]]/', '', $supplierName), 0, 31);
            if (empty($safeSupplier)) $safeSupplier = 'Unknown';

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($safeSupplier);

            $row = 1;
            $supplierPurchases = $supplierPurchases->sortBy('po_number');
            $supplierGrandTotal = $supplierPurchases->sum('grand_total');

            // --- COMPANY NAME ---
            $sheet->setCellValue("A{$row}", $companyName);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
            $row++;

            // --- REPORT TITLE ---
            $sheet->setCellValue("A{$row}", "Purchases Report for Supplier: {$supplierName}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $row += 2;

            // --- Total Grand Total for Supplier ---
            $sheet->setCellValue("K{$row}", "TOTAL GRAND TOTAL:");
            $sheet->setCellValue("L{$row}", $supplierGrandTotal);
            $sheet->getStyle("K{$row}:L{$row}")->getFont()->setBold(true);
            $sheet->getStyle("L{$row}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle("K{$row}:L{$row}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFF99');
            $row += 2;

            // --- HEADERS ---
            $headers = [
                'Date', 'PO Number', 'Supplier', 'Payment Method', 'Description', 'Qty', 'Unit Price', 'Total', 'Discount %', 'Discount Amount', 'Sub Total', 'Grand Total'
            ];
            $sheet->fromArray($headers, null, "A{$row}");
            $sheet->getStyle("A{$row}:L{$row}")->getFont()->setBold(true);
            $sheet->freezePane("A" . ($row + 1));
            $row++;

            $startDataRow = $row;

            foreach ($supplierPurchases as $purchase) {
                $poNumber = $purchase->po_number;
                $date = $purchase->created_at ? \Carbon\Carbon::parse($purchase->created_at)->format('F j, Y') : '';
                $paymentMethod = $purchase->paymentMode->name ?? 'N/A';
                $grandTotal = $purchase->grand_total ?? 0;

                $firstRowOfPurchase = true;

                foreach ($purchase->items as $item) {
                    $product = $item->supplierItem->item_description ?? 'N/A';
                    $qty = $item->qty ?? 0;
                    $unitPrice = $item->unit_price ?? 0;
                    $gross = $qty * $unitPrice;

                    $discounts = array_filter([$item->discount_1 ?? 0, $item->discount_2 ?? 0, $item->discount_3 ?? 0], fn($d) => $d != 0);
                    $discountType = $item->discount_type ?? 'less';
                    $net = $item->total ?? $gross;
                    $discountAmount = $gross - $net;

                    $discountDisplay = '';
                    if (!empty($discounts)) {
                        $discountDisplay = implode(' + ', array_map(function ($d) use ($discountType) {
                            $percent = floor($d) == $d ? number_format($d, 0) : number_format($d, 2);
                            return $percent . '% (' . ucfirst($discountType) . ')';
                        }, $discounts));
                    }

                    // Only fill Date, PO Number, Supplier, Payment Method on first row of items
                    if ($firstRowOfPurchase) {
                        $sheet->setCellValue("A{$row}", $date);
                        $sheet->setCellValue("B{$row}", $poNumber);
                        $sheet->setCellValue("C{$row}", $supplierName);
                        $sheet->setCellValue("D{$row}", $paymentMethod);
                        $firstRowOfPurchase = false;
                    }

                    $sheet->setCellValue("E{$row}", $product);
                    $sheet->setCellValue("F{$row}", $qty);
                    $sheet->setCellValue("G{$row}", $unitPrice);
                    $sheet->setCellValue("H{$row}", $gross);
                    $sheet->setCellValue("I{$row}", $discountDisplay);
                    $sheet->setCellValue("J{$row}", $discountAmount);
                    $sheet->setCellValue("K{$row}", $net);

                    // Leave Grand Total column empty until the end
                    $sheet->setCellValue("L{$row}", '');

                    $sheet->getStyle("F{$row}:K{$row}")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                    $row++;
                }

                // --- GRAND TOTAL ROW per purchase ---
                $sheet->setCellValue("K{$row}", "GRAND TOTAL:");
                $sheet->setCellValue("L{$row}", $grandTotal);
                $sheet->getStyle("K{$row}:L{$row}")->getFont()->setBold(true);
                $sheet->getStyle("L{$row}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("K{$row}:L{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFF99');

                $row += 2;
            }


            // --- BORDERS ---
            $sheet->getStyle("A{$startDataRow}:L{$row}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            foreach (range('A', 'L') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // Remove default empty sheet if exists
        if ($spreadsheet->getSheetCount() > 1 && $spreadsheet->getSheet(0)->getTitle() === 'Worksheet') {
            $spreadsheet->removeSheetByIndex(0);
        }

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
