<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class userCoupon extends Model
{
    protected $table = 'user_coupons';
    protected $fillable = ['user_id', 'name', 'code', 'type', 'discount', 'minAmount', 'maxTotalUse', '	totalUsed', 'status', '	startDateTime', 'endDateTime'];
}
