<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    // Relationship with Sales
    public function sale()
    {
        return $this->hasMany(Sales::class); // Make sure Sales model exists
    }

    // Relationship with Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class); // Customer model is in App namespace
    }
}
