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
        'due_date',
        'payment_mode_id',
        'discount_type',
        'discount_value',
        'subtotal',
        'shipping_fee',
        'other_charges',
        'grand_total',
        'outstanding_balance',
        'invoice_status',
        'payment_status',
        'remarks',
        'discount_approved'
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceSales::class);
    }

    public function paymentMode()
    {
        return $this->belongsTo(ModeofPayment::class, 'payment_mode_id');
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function sales()
    {
        return $this->hasMany(InvoiceSales::class, 'invoice_id');
    }
}
