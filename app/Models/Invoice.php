<?php

namespace App\Models;

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
        'salesman',
        'remarks',
        'discount_approved',
        'is_printed',
        'printed_at',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function salesman_relation()
    {
        return $this->belongsTo(Salesman::class, 'salesman', 'id');
    }

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

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
