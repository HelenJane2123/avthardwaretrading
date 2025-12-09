<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(Product::class,'unit_id','id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'unit');
    }

    public function supplierItems()
    {
        return $this->hasMany(SupplierItem::class, 'unit_id');
    }
}
