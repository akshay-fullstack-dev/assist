<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\BookingDetail;
use App\Http\Resources\Coupon as CouponResource;
use App\Coupon;
use App\ExtraHour;

class Invoice extends Resource {

    protected $dateFormat = 'Y-m-d';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        //return parent::toArray($request);
        $bDeatil = array();
        $bDeatil = BookingDetail::collection($this->bookingDetail);
        $coup = array();
        if ($bDeatil[0]) {

            if ($bDeatil[0]->code) {
                $coupon = Coupon::where(['code' => $bDeatil[0]->code])->get();
                $couData = CouponResource::collection($coupon);
                $coup = $couData[0];
            }
        }
        $data = array();
        if ($coup) {
            $data = [
                'couponDetail' => $coup
            ];
        }
        $exHour = 0;
        if ($this->selected_hours) {
            $extraHours = ExtraHour::where(['booking_id' => $this->id])->get();
            
            if ($extraHours) {
                foreach ($extraHours as $extraHour) {
                    $exHour = $exHour + $extraHour->extended_minutes;
                }
            }
             
        }


        return array_merge($data, [
            'bookingId' => $this->id,
            'vendorName' => $this->vender->firstname.' '.$this->vender->lastname,
            'billingName' => $this->user->firstname.' '.$this->user->lastname,
            'serviceName' => $this->service_name ? $this->service_name : '',
            'extraHours' => $exHour,
            'selectedHours' => $this->selected_hours ? $this->selected_hours : '',
            'price' => (float) $this->price ? $this->price : '',
            'bookingDetails' => $bDeatil ? $bDeatil[0] : (object) $bDeatil,
            'totalPrice' => (float) $this->total_price ? $this->total_price : '',
            'bookingDate' => $this->booking_date ? $this->booking_date : '',
            'startTime' => $this->slot_start_from ? $this->slot_start_from : '',
            'endTime' => $this->slot_start_end ? $this->slot_start_end : '',
        ]);
    }

}
