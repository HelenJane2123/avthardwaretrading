<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'invoice_date',
        'payment_mode_id',
        'discount_type',
        'discount_value',
        'subtotal',
        'shipping_fee',
        'other_charges',
        'grand_total',
        'status',
        'remarks',
        'discount_approved'
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(InvoiceSales::class, 'invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
