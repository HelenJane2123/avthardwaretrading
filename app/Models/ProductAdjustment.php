<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAdjustment extends Model
{
    protected $fillable = [
        'product_id',
        'adjustment',
        'adjustment_status',
        'remarks',
        'new_initial_qty',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
