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
        'discount_1',
        'discount_2',
        'discount_3',
        'unit_id',
        'item_image',
        'volume_less',
        'regular_less'
    ];

    /**
     * Get the supplier that owns the item.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the category details.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the unit details.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * 🔹 Custom function: get item codes by supplier_id
     */
    public static function getItemCodesBySupplier($supplierId)
    {
        return self::where('supplier_id', $supplierId)
               ->get(['item_code', 'item_description', 'item_price', 'item_amount']);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
}
