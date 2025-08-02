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
       return $this->belongsTo('App\Category');
    }

    public function unit(){
        return $this->belongsTo('App\Unit');
    }

    public function tax()
    {
        return $this->belongsTo('App\Tax');
    }

    public function additionalProduct(){
        return $this->hasMany('App\ProductSupplier');
    }

    public function sale(){
        return $this->hasMany('App\Sale');
    }

    public function invoice(){
        return $this->belongsToMany('App\Invoice');
    }

    public function suppliers()
    {
        return $this->hasMany(ProductSupplier::class);
    }
}
