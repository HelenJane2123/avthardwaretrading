<?php

namespace App;

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
        'status'
    ];

    public function invoice()
    {
        return $this->hasMany(Invoice::class);
    }
}
