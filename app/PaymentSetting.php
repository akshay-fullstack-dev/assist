<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
   
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['commission', 'currency_id'];
    
    public function currency() {
        return $this->belongsTo('App\Currency');
    }
}
