<?php
namespace App\Http\Controllers;

use App\Customer;
use App\Product;
use App\ProductSupplier;
use App\Invoice;
use App\Collection;
use App\Purchase;
use App\PurchaseItem;
use App\ModeofPayment;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Response;

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
                'Supplier', 'Price'
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
        // eager load relations - adjust relation names to your app (customer, items.product)
        $invoices = Invoice::with(['customer', 'items.product'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add header / logo (if exists)
        $this->addHeader($sheet, 'Invoice List with Details');

        $row = 6; // start row

        foreach ($invoices as $invoice) {
            // Invoice header block
            $sheet->setCellValue('A' . $row, 'Invoice #: ' . ($invoice->invoice_number ?? $invoice->id));
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('C' . $row, 'Customer: ' . ($invoice->customer->name ?? 'N/A'));
            $sheet->setCellValue('E' . $row, 'Date: ' . ($invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : ($invoice->created_at ? $invoice->created_at->format('Y-m-d') : '')));
            $row++;

            $sheet->setCellValue('A' . $row, 'Status: ' . ($invoice->invoice_status ?? ''));
            $sheet->setCellValue('C' . $row, 'Grand Total: ' . number_format($invoice->grand_total ?? 0, 2));
            $row += 1;

            // Items header
            $sheet->setCellValue('A' . $row, 'Product');
            $sheet->setCellValue('B' . $row, 'Qty');
            $sheet->setCellValue('C' . $row, 'Unit Price');
            $sheet->setCellValue('D' . $row, 'Discount');
            $sheet->setCellValue('E' . $row, 'Amount');
            // bold header row
            $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
            $row++;

            // Invoice items (guard if relation missing)
            $items = $invoice->items ?? collect();
            if ($items->isEmpty()) {
                $sheet->setCellValue('A' . $row, '(no items)');
                $row++;
            } else {
                foreach ($items as $item) {
                    // Adjust property names to your invoice item model (product->product_name or product->name)
                    $productName = $item->product->product_name ?? $item->product->name ?? 'Unknown Product';
                    $qty = $item->qty ?? $item->quantity ?? ($item->quantity_sold ?? 0);
                    $unitPrice = $item->price ?? $item->unit_price ?? 0;
                    $discount = $item->discount ?? 0;
                    $amount = $item->amount ?? ($qty * $unitPrice) - $discount;

                    $sheet->setCellValue('A' . $row, $productName);
                    $sheet->setCellValue('B' . $row, $qty);
                    $sheet->setCellValue('C' . $row, $unitPrice);
                    $sheet->setCellValue('D' . $row, $discount);
                    $sheet->setCellValue('E' . $row, $amount);
                    $row++;
                }
            }

            // space before next invoice
            $row += 2;
        }

        // Auto-size some columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Write file to temp and return download
        $writer = new Xlsx($spreadsheet);
        $fileName = 'invoices_export_' . now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'invoices_export_') . '.xlsx'; // ensure .xlsx
        $writer->save($tempFile);
        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

     /**
     * Export purchase orders.
     */
    public function exportPurchases()
    {
        $purchases = Purchase::with(['supplier', 'items.supplierItem'])->get();

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
        $row = 7;

        foreach ($purchases as $purchase) {
            foreach ($purchase->items as $item) {
                $sheet->setCellValue("A{$row}", $purchase->po_number);
                $sheet->setCellValue("B{$row}", $purchase->supplier->name ?? 'N/A');
                $sheet->setCellValue("C{$row}", $purchase->created_at->format('Y-m-d'));
                $sheet->setCellValue("D{$row}", $item->product_code ?? 'N/A');
                $sheet->setCellValue("E{$row}", $item->supplierItem->item_description ?? 'N/A');
                $sheet->setCellValue("F{$row}", $item->qty);
                $sheet->setCellValue("G{$row}", number_format($item->unit_price, 2));
                $sheet->setCellValue("H{$row}", number_format($item->total, 2));
                $row++;
            }
        }

        // Write file to temp and return download
        $writer = new Xlsx($spreadsheet);
        $fileName = 'purchases_export_' . now()->format('Ymd_His') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'purchase_export_') . '.xlsx'; // ensure .xlsx
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
