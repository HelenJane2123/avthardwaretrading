<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
}

