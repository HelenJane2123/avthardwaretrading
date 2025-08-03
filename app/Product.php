<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // App\Product.php
    protected $fillable = [
        'product_code', 'name', 'serial_number', 'model',
        'category_id', 'sales_price', 'unit_id', 'quantity',
        'remaining_stock', 'tax_id', 'image', 'threshold', 'status',
    ];

    public function category(){
       return $this->belongsTo(Category::class);
    }

    public function unit(){
        return $this->belongsTo(Unit::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function additionalProduct(){
        return $this->hasMany(ProductSupplier::class);
    }

    public function sale(){
        return $this->hasMany(Sales::class);
    }

    public function invoice(){
        return $this->belongsToMany(Invoice::class);
    }

   public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_suppliers')
                    ->withPivot('price')
                    ->withTimestamps();
    }

    public function productSuppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }
}
