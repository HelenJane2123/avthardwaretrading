<?php
namespace App\Http\Controllers;

use App\Customer;
use App\Product;
use App\ProductSupplier;
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

        // Insert logo (placed on the left)
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Company Logo');
        $drawing->setPath(public_path('images/avt_logo.png')); // Adjust path if needed
        $drawing->setHeight(60); // Logo size
        $drawing->setCoordinates('A1'); // Set logo to A1
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);

        // Merge for company name, address, and subtitle
        $sheet->mergeCells('B1:F1');
        $sheet->mergeCells('B2:F2');
        $sheet->mergeCells('B3:F3');

        // Set cell values
        $sheet->setCellValue('B1', 'AVT Hardware Trading');
        $sheet->setCellValue('B2', '123 Main St., Calamba, Laguna');
        $sheet->setCellValue('B3', 'Customer List');

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
        $headers = ['Customer Code','Customer', 'Address', 'Contact', 'Email', 'Tax No.', 'Details', 'Credit Balance', 'Date Created', 'Date Updated'];
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
            $sheet->setCellValue('H' . $row, $customer->previous_balance);
            $sheet->setCellValue('I' . $row, $customer->created_at);
            $sheet->setCellValue('J' . $row, $customer->updated_at);
            $sheet->getStyle('G' . $row)->getNumberFormat()
                ->setFormatCode('#,##0.00');
            // Apply borders to each row
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
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

        // Logo and title
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('images/avt_logo.png'));
        $drawing->setHeight(80);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($sheet);

        $sheet->setCellValue('C1', 'AVT Hardware Trading');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);

        $sheet->setCellValue('C2', '123 Main St., Calamba, Laguna');
        $sheet->getStyle('C2')->getFont()->setSize(12);

        $sheet->setCellValue('C3', 'Product List Grouped by Category');
        $sheet->getStyle('C3')->getFont()->setBold(true)->setSize(14);

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
                        $product->name,
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


    // Shared function
    private function downloadExcel(Spreadsheet $spreadsheet, $filename)
    {
        $writer = new Xlsx($spreadsheet);
        $temp_file = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($temp_file);
        return response()->download($temp_file, $filename)->deleteFileAfterSend(true);
    }
}
