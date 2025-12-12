<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $table = 'purchase_items';
    protected $fillable = [
        'purchase_id', 
        'supplier_item_id', 
        'product_code', 
        'qty', 
        'unit', 
        'discount_less_add', 
        'discount_1', 
        'discount_2', 
        'discount_3', 
        'unit_price', 
        'total'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplierItem()
    {
        return $this->belongsTo(SupplierItem::class, 'supplier_item_id'); 
        // assuming your purchase_items table has supplier_item_id
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_code','supplier_product_code');
    }
}

