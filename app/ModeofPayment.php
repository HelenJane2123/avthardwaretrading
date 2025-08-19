<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModeofPayment extends Model
{
    protected $table = 'mode_of_payment';

    protected $fillable = [
        'name',
        'term',
        'description',
        'is_active'
    ];
    public function modeofpayment(){
        return $this->hasMany('App\ModeofPayment');
    }

     public function getDisplayNameAttribute()
    {
        if ($this->name === 'PDC/Check' && $this->term) {
            return $this->name . ' (' . $this->term . ' days)';
        }
        return $this->name;
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'payment_id');
    }
}
