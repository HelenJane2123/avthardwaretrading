<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    public function products()
    {
        return $this->hasMany(Product::class, 'discount_1', 'name')
                    ->orWhere('discount_2', $this->name)
                    ->orWhere('discount_3', $this->name);
    }

    public function supplierItems()
    {
        return $this->hasMany(SupplierItem::class, 'discount_1', 'name')
                    ->orWhere('discount_2', $this->name)
                    ->orWhere('discount_3', $this->name);
    }
}
