<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdcCollection extends Model
{
    use HasFactory;

    protected $table = 'pdc_payments';

    protected $fillable = [
        'collection_number',
        'pdc_id',
        'payment_date',
        'amount_paid',
        'remarks',
    ];

    /**
     * Relationship: Each collection belongs to one PDC.
     */
    public function pdc()
    {
        return $this->belongsTo(Pdc::class, 'pdc_id');
    }
}
