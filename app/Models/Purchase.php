<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'purchases';
    protected $fillable = [
        'supplier_id',
        'po_number',
        'salesman_id',
        'payment_id',
        'gcash_number',
        'gcash_name',
        'check_number',
        'date',
        'discount_type',
        'discount_value',
        'overall_discount',
        'subtotal',
        'shipping',
        'other_charges',
        'remarks',
        'grand_total',
        'is_approved',
        'is_completed'
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
        return $this->belongsTo(ModeofPayment::class, 'payment_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function payment()
    {
        return $this->belongsTo(ModeofPayment::class, 'payment_id');
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_id', 'id');
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class, 'salesman_id','id');
    }

    public function items_purchase()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id', 'id');
    }
}

