<?php

namespace App;

use App\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SuppliersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Supplier([
            'supplier_code'      => $row['supplier_code'],
            'name'               => $row['name'],
            'mobile'             => $row['mobile'],
            'address'            => $row['address'],
            'details'            => $row['details'],
            'tax'                => $row['tax'],
            'email'              => $row['email'],
            'previous_balance'   => $row['previous_balance'],
            'status'             => $row['status'],
        ]);
    }
}
