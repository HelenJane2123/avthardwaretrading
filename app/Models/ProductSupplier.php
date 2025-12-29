<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{

    protected $fillable = ['product_id', 'supplier_id', 'price','net_price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function productSuppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }

    public function supplierItem()
    {
        return $this->hasOne(
            SupplierItem::class,
            'supplier_id',
            'supplier_id'
        )->whereColumn(
            'supplier_items.item_code',
            'products.supplier_product_code'
        );
    }

}
