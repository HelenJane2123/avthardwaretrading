<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'supplier_code',
        'name',
        'mobile',
        'address',
        'details',
        'tax',
    ];

    /**
     * Get the supplier's items.
     */
    public function supplierItems()
    {
        return $this->hasMany(SupplierItem::class);
    }
}
