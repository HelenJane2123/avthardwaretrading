<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'item_code',
        'category_id',
        'item_description',
        'item_price',
        'item_amount',
        'item_qty',
        'unit_id',
        'item_image'
    ];

    /**
     * Get the supplier that owns the item.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the category details owns the item.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the unit details owns the item.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
