<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceSalesDiscount extends Model
{

    protected $fillable = [
        'invoice_sale_id',
        'discount_name',
        'discount_type',
        'discount_value',
    ];

    public function invoiceSale()
    {
        return $this->belongsTo(InvoiceSales::class, 'invoice_sale_id');
    }
}
