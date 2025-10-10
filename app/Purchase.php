<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'purchases';
    protected $fillable = [
        'supplier_id',
        'po_number',
        'salesman',
        'payment_id',
        'date',
        'discount_type',
        'discount_value',
        'overall_discount',
        'subtotal',
        'shipping',
        'other_charges',
        'remarks',
        'grand_total'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id', 'id');
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseItem::class);
    }


    public function paymentMode()
    {
        return $this->belongsTo(ModeofPayment::class, 'id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function payment()
    {
        return $this->belongsTo(ModeOfPayment::class, 'payment_id');
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_id', 'id');
    }
}

