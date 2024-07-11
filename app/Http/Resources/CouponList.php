<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Auth;
use App\CouponHistory;
use App\userCoupon;

class CouponList extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $totalUsed = 0;
        $customerUsed = 0;
        if($this->id) {
            $user = Auth::User();
            $totalUsed = CouponHistory::where('coupon_id', $this->id)->get()->count();
            $customerUsed = CouponHistory::where([['coupon_id', '=', $this->id], ['user_id', '=', $user->id]])->get()->count();
        }
        $coupon_type = 1;
        if($this->code) {
            $checkCoupon_exist_in_usercoupon_table = userCoupon::where('code', $this->code)->get()->count();
            if($checkCoupon_exist_in_usercoupon_table) {
                $coupon_type = 0;
            }
        }
        
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'couponType' => $coupon_type,
            'discount' => $this->discount ? $this->discount : 0,
            'minAmount' => $this->minAmount ? $this->minAmount : 0,
            'maxDiscountAmount' => $this->maxDiscountAmount ? $this->maxDiscountAmount : 0,
            'startDateTime' => $this->startDateTime,
            'endDateTime' => $this->endDateTime,
            'maxTotalUse' => $this->maxTotalUse ? $this->maxTotalUse : 0,
            'totalUsed' => $totalUsed,
            'maxUseCustomer' => $this->maxUseCustomer ? $this->maxUseCustomer : 0,
            'customerUsed' => $customerUsed,
        ];
    }
}
