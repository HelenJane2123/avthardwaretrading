<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // App\Product.php
    protected $fillable = [
        'product_code',
        'supplier_product_code', 
        'product_name', 
        'description',
        'serial_number', 
        'model',
        'category_id', 
        'sales_price', 
        'unit_id', 
        'quantity',
        'remaining_stock', 
        'discount_type',
        'discount_1',
        'discount_2',
        'discount_3', 
        'image', 
        'threshold', 
        'status',
        'volume_less',
        'regular_less',
    ];

    public function category(){
       return $this->belongsTo(Category::class);
    }

    public function unit(){
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function supplierItems()
    {
        return $this->hasMany(SupplierItem::class, 'item_code', 'supplier_product_code');
    }

    public function additionalProduct(){
        return $this->hasMany(ProductSupplier::class);
    }

    public function sales()
    {
        return $this->hasMany(InvoiceSales::class, 'product_id');
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
        return $this->hasMany(ProductSupplier::class, 'product_id');
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_items')
                    ->withPivot('quantity', 'price', 'discount')
                    ->withTimestamps();
    }

    public function adjustments()
    {
        return $this->hasMany(ProductAdjustment::class);
    }
}
