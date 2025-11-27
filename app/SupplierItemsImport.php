<?php

namespace App;

use App\SupplierItem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SupplierItemsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new SupplierItem([
            'supplier_id'    => $row['supplier_id'],
            'item_code'      => $row['item_code'],
            'category_id'    => $row['category_id'],
            'item_description'=> $row['item_description'],
            'item_price'     => $row['item_price'],
            'item_amount'    => $row['item_amount'],
            'unit_id'        => $row['unit_id'],
            'item_qty'       => $row['item_qty'],
            'item_image'     => $row['item_image'],
            'volume_less'    => $row['volume_less'],
            'regular_less'   => $row['regular_less'],
        ]);
    }
}
