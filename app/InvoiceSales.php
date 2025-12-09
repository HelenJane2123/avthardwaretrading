<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceSales extends Model
{
    protected $table = 'invoice_sales';
    protected $fillable = [
        'invoice_id',
        'product_id',
        'qty',
        'price',
        'disdiscount_less_add',
        'discount_1',
        'discount_2',
        'discount_3',
        'amount',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function discounts()
    {
        return $this->hasMany(InvoiceSalesDiscount::class, 'invoice_sale_id');
    }

}