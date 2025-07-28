<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Supplier;
use App\SupplierItem;

class ExportSupplierController extends Controller
{
    public function exportSupplierProducts($id)
    {
        $supplier = Supplier::findOrFail($id);
        $items = SupplierItem::where('supplier_id', $id)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Insert logo
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Company Logo');
        $drawing->setPath(public_path('images/avt_logo.png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(10);
        $drawing->setWorksheet($sheet);

        // Company info
        $sheet->mergeCells('B1:G1');
        $sheet->mergeCells('B2:G2');
        $sheet->mergeCells('B3:G3');

        $sheet->setCellValue('B1', 'AVT Hardware Trading');
        $sheet->setCellValue('B2', '123 Main St., Calamba, Laguna');
        $sheet->setCellValue('B3', 'Supplier Product List');

        $sheet->getStyle('B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('B2')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('B3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Supplier details
        $sheet->setCellValue('A5', 'Supplier Name:');
        $sheet->setCellValue('B5', $supplier->name);
        $sheet->setCellValue('A6', 'Supplier Code:');
        $sheet->setCellValue('B6', $supplier->supplier_code);
        $sheet->setCellValue('A7', 'Address:');
        $sheet->setCellValue('B7', $supplier->address);

        // Table headers
        $sheet->setCellValue('A9', 'Item Code');
        $sheet->setCellValue('B9', 'Category');
        $sheet->setCellValue('C9', 'Description');
        $sheet->setCellValue('D9', 'Unit');
        $sheet->setCellValue('E9', 'Quantity');
        $sheet->setCellValue('F9', 'Price');
        $sheet->setCellValue('G9', 'Amount');

        // Header style
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFEFEFEF']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A9:G9')->applyFromArray($headerStyle);

        // Fill data rows
        $row = 10;
        $totalQty = 0;
        $totalAmount = 0;

        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, $item->item_code);
            $sheet->setCellValue('B' . $row, $item->category ? $item->category->name : '');
            $sheet->setCellValue('C' . $row, $item->item_description);
            $sheet->setCellValue('D' . $row, $item->unit ? $item->unit->name : '');
            $sheet->setCellValue('E' . $row, $item->item_qty);
            $sheet->setCellValue('F' . $row, $item->item_price);
            $sheet->setCellValue('G' . $row, $item->item_amount);

            $totalQty += $item->item_qty;
            $totalAmount += $item->item_amount;

            $row++;
        }

        // Add total row
        $sheet->setCellValue('B' . $row, 'TOTAL:');
        $sheet->getStyle('B' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('G' . $row, $totalAmount);
        $sheet->setCellValue('E' . $row, $totalQty);

        $sheet->getStyle('G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('E' . $row)->getFont()->setBold(true);

        // Auto-size
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'supplier_' . $supplier->supplier_code . '_products.xlsx';
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }
}
