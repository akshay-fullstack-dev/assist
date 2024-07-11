<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupons';
    protected $fillable = ['name', 'code', 'type', 'discount', 'minAmount', 'maxDiscountAmount','all_services','startDateTime', 'endDateTime', 'maxTotalUse', 'maxUseCustomer', 'status'];
    
    public function services()
    {
        return $this->belongsToMany('App\Service');
    }
}
