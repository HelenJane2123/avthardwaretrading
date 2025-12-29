<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'id',
        'customer_code',
        'name',
        'address',
        'mobile',
        'email',
        'tax',
        'location',
        'details',
        'status'
    ];

    public function invoice()
    {
        return $this->hasMany(Invoice::class);
    }
}
