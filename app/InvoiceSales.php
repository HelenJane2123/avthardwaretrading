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
        'dis',
        'amount',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

}