<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModeofPayment extends Model
{
    protected $table = 'mode_of_payment';

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];
    public function modeofpayment(){
        return $this->hasMany('App\ModeofPayment');
    }
}
