<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'collection_number',
        'invoice_id',
        'customer_id',
        'payment_date',
        'amount_paid',
        'remarks',
    ];

    // A collection belongs to an invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // A collection belongs to a customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class, 'payment_mode_id');
    }
}
