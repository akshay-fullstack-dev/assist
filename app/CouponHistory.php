<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CouponHistory extends Model
{
    protected $table = 'coupon_history';
    protected $fillable = ['user_id', 'coupon_id', 'booking_id', 'coupon_code', 'discount', 'description'];
    
    public function user(){
        $this->belongsTo('App\User');
    }
    
    public function booking(){
        $this->belongsTo('App\Booking', 'booking_id', 'id');
    }
}
