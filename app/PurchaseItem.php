<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $table = 'purchase_items';
    protected $fillable = ['purchase_id', 'supplier_item_id', 'product_code', 'qty', 'unit_price', 'discount', 'unit', 'total'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplierItem()
    {
        return $this->belongsTo(SupplierItem::class, 'supplier_item_id'); 
        // assuming your purchase_items table has supplier_item_id
    }
}

