<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pdc extends Model
{
    use HasFactory;

    protected $table = 'pdc_collection';

    protected $fillable = [
        'id',
        'collection_number',
        'pdc_id',
        'payment_date',
        'amount_paid',
        'remarks',
    ];

    public function collections()
    {
        return $this->hasMany(PdcCollection::class, 'pdc_id');
    }

}
