<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'collection_number',
        'invoice_id',
        'customer_id',
        'check_number',
        'gcash_number',
        'gcash_name',
        'payment_date',
        'last_paid_amount',
        'amount_paid',
        'remarks',
        'is_approved'
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
        return $this->belongsTo(ModeofPayment::class, 'payment_mode_id');
    }
}
