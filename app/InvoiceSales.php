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
}