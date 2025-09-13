<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'customer_id',
        'payment_date',
        'amount_paid',
        'balance',
        'payment_status',
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
}
