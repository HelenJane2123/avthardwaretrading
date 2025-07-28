<?php

namespace App;

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
        'email'
    ];

    /**
     * Get the supplier's items.
     */
    public function items()
    {
        return $this->hasMany(SupplierItem::class); // adjust namespace if needed
    }

    // Automatically delete items and images when supplier is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($supplier) {
            foreach ($supplier->items as $item) {
                // Delete item image if it exists
                if ($item->item_image) {
                    $path = 'public/' . $item->item_image;
                    if (Storage::exists($path)) {
                        Storage::delete($path);
                    }
                }

                // Delete the item
                $item->delete();
            }

            // Optionally: remove the supplier folder if empty
            $folderPath = 'public/items/' . $supplier->supplier_code;
            if (Storage::exists($folderPath)) {
                Storage::deleteDirectory($folderPath);
            }
        });
    }
}
