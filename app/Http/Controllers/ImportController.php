<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Supplier;
use App\SupplierItem;
use App\Product;
use App\ProductSupplier;
use Carbon\Carbon;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());

        // --- SHEET 1: SUPPLIERS ---
        $suppliersSheet = $spreadsheet->getSheetByName('Suppliers');
        $suppliersData = $suppliersSheet->toArray(null, true, true, true);

        foreach ($suppliersData as $index => $row) {
            if ($index == 1) continue; // skip header

            // Skip if supplier already exists
            $existingSupplier = Supplier::where('supplier_code', $row['A'])->first();
            if ($existingSupplier) continue;

            Supplier::create([
                'supplier_code'    => $row['A'],
                'name'             => $row['B'],
                'mobile'           => $row['C'],
                'address'          => $row['D'],
                'details'          => $row['E'],
                'tax'              => $row['F'],
                'email'            => $row['G'],
                'previous_balance' => $row['H'],
                'status'           => $row['I'],
            ]);
        }

        // --- SHEET 2: SUPPLIER ITEMS ---
        $itemsSheet = $spreadsheet->getSheetByName('Supplier_Items');
        $itemsData = $itemsSheet->toArray(null, true, true, true);

        foreach ($itemsData as $index => $row) {
            if ($index == 1) continue; // skip header

            $supplierId = (int) $row['A']; // use supplier_id from Excel
            $supplier = Supplier::find($supplierId);
            if (!$supplier) continue; // skip if supplier doesn't exist

            $itemCode = trim(explode('/', $row['B'])[0]);

            // Skip if item exists for this supplier
            $existingItem = SupplierItem::where('supplier_id', $supplierId)
                ->where('item_code', $itemCode)
                ->first();
            if ($existingItem) continue;

            SupplierItem::create([
                'supplier_id'      => $supplierId,
                'item_code'        => $itemCode,
                'category_id'      => trim($row['C']),
                'item_description' => trim($row['D']),
                'item_price'       => trim($row['E']),
                'item_amount'      => trim($row['F']),
                'unit_id'          => trim($row['G']),
                'item_qty'         => trim($row['H']),
                'item_image'       => trim($row['I']),
                'volume_less'      => trim($row['J']),
                'regular_less'     => trim($row['K']),
            ]);
        }
        return back()->with('message', 'Suppliers and Supplier Items imported successfully!');
    }

    public function import_product(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());

        // =============================
        // SHEET 1: PRODUCTS
        // =============================
        $productSheet = $spreadsheet->getSheetByName('Products');
        $productData = $productSheet->toArray(null, true, true, true);

        foreach ($productData as $index => $row) {
            if ($index == 1) continue; // skip header row

            $productCode = trim($row['B']); // column B = product_code
            $supplierProductCode = trim($row['C']); // column C = supplier_product_code

            if (empty($productCode) || empty($supplierProductCode)) continue;

            // Skip existing product_code
            $existing = Product::where('product_code', $productCode)->first();
            if ($existing) continue;

            // Get supplier price from supplier item table
            $supplierItem = SupplierItem::where('item_code', $supplierProductCode)->first();
            $supplierPrice = $supplierItem ? $supplierItem->item_price : 0;

            // Create product
            $product = Product::create([
                'product_code'          => $productCode,
                'supplier_product_code' => $supplierProductCode,
                'product_name'          => $row['D'],
                'serial_number'         => $row['E'],
                'model'                 => $row['F'],
                'category_id'           => $row['G'],
                'sales_price'           => $row['H'],
                'unit_id'               => $row['I'],
                'quantity'              => $row['J'],
                'remaining_stock'       => $row['K'],
                'tax_id'                => $row['L'],
                'image'                 => $row['M'],
                'threshold'             => $row['N'],
                'status'                => $row['O'] ?? 'In Stock',
                'created_at'            => Carbon::now(),
                'updated_at'            => Carbon::now(),
            ]);

            // Save supplier price automatically
            if ($supplierItem) {
                ProductSupplier::create([
                    'product_id'  => $product->id,
                    'supplier_id' => $supplierItem->supplier_id,
                    'price'       => $supplierPrice,
                    'created_at'  => Carbon::now(),
                    'updated_at'  => Carbon::now(),
                ]);
            }
        }

        return back()->with('message', 'Products imported successfully with supplier prices!');
    }

}
