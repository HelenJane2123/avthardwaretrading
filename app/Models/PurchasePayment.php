<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchasePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'amount_paid',
        'outstanding_balance',
        'payment_date',
        'payment_status',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}