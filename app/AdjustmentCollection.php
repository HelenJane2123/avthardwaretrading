<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_no',
        'invoice_no',       
        'entry_type',       
        'collection_date', 
        'account_name',   
        'amount',  
        'remarks',           
        'amount'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_no', 'invoice_no');
    }
}
