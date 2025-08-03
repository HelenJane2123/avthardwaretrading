<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{

    protected $fillable = ['product_id', 'supplier_id', 'price'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

}
