<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'item_code',
        'item_description',
        'item_price',
        'item_amount',
    ];

    /**
     * Get the supplier that owns the item.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
