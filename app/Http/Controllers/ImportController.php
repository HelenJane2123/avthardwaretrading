<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\Product;
use App\Models\ProductSupplier;
use Carbon\Carbon;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        try {
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

                $supplierId = (int) $row['A']; 
                $supplier = Supplier::find($supplierId);
                if (!$supplier) continue;

                $itemCode = trim(explode('/', $row['B'])[0]);

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
                    'net_price'        => trim($row['F']),
                    'item_amount'      => trim($row['G']),
                    'unit_id'          => trim($row['H']),
                    'item_qty'         => trim($row['I']),
                    'discount_less_add'=> trim($row['J']),
                    'discount_1'       => trim($row['K']),
                    'discount_2'       => trim($row['L']),
                    'discount_3'       => trim($row['M']),
                    'item_image'       => trim($row['N']),
                    'volume_less'      => trim($row['O']),
                    'regular_less'     => trim($row['P']),
                ]);
            }
            return back()->with('message', 'Suppliers and Supplier Items imported successfully!');
        } catch (\Exception $e) {
            \Log::error('Import failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
           return redirect()->back()->with('error', 'An error has occurred.');
        }
    }

    public function import_product(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls'
            ]);

            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());

            $productSheet = $spreadsheet->getSheetByName('Products');
            if (!$productSheet) {
                return back()->with('error', 'Products sheet not found.');
            }

            $rows = $productSheet->toArray(null, true, true, true);

            foreach ($rows as $index => $row) {
                if ($index === 1) continue;

                $productCode = trim($row['B']);
                $supplierProductCode = trim($row['C']);

                if (!$productCode || !$supplierProductCode) continue;

                if (Product::where('product_code', $productCode)->exists()) continue;

                $supplierItem = SupplierItem::where('item_code', $supplierProductCode)->first();
                if (!$supplierItem) continue;

                $discountType = strtolower(trim($row['M']));

                $product = Product::create([
                    'product_code'          => $productCode,
                    'supplier_product_code' => $supplierProductCode,
                    'product_name'          => $row['D'],
                    'description'           => $row['E'],
                    'serial_number'         => $row['F'],
                    'model'                 => $row['G'],
                    'category_id'           => $row['H'],
                    'sales_price'           => $row['I'],
                    'unit_id'               => $row['J'],
                    'quantity'              => $row['K'],
                    'remaining_stock'       => $row['L'],
                    'discount_type'         => in_array($discountType, ['less', 'add']) ? $discountType : null,
                    'discount_1'            => $row['N'],
                    'discount_2'            => $row['O'],
                    'discount_3'            => $row['P'],
                    'image'                 => $row['Q'],
                    'threshold'             => $row['R'],
                    'status'                => $row['S'] ?? 'In Stock',
                    'volume_less'           => $row['T'],
                    'regular_less'          => $row['U'],
                ]);

                ProductSupplier::updateOrCreate(
                    [
                        'product_id'  => $product->id,
                        'supplier_id' => $supplierItem->supplier_id,
                    ],
                    [
                        'price'     => $supplierItem->item_price,
                        'net_price' => $supplierItem->net_price,
                    ]
                );
            }

            return back()->with('message', 'Products imported successfully!');
        } catch (\Exception $e) {
            \Log::error('Import failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error has occurred.');
        }
    }


}
