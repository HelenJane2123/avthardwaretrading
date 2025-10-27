<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    use HasFactory;

    protected $table = 'salesman';

    protected $fillable = [
        'salesman_code',
        'salesman_name',      
        'phone',
        'address',
        'email',
        'status',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'salesman_id');
    }
}
