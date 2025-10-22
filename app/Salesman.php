<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    use HasFactory;

    // ✅ Match the database table name
    protected $table = 'salesmen';

    protected $fillable = [
        'salesman_code',
        'salesman_name',      
        'phone',
        'address',
        'email',
        'status',
    ];
}
