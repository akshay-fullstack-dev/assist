<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Review;
class Order extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $canReview = Review::where('booking_id', '=', $this->id)->first();
        
        return [
            'bookingId' => $this->id,
            'serviceId' => $this->service->id,
            'serviceType' => $this->service->ServiceCategory->cat_name,
            'serviceImage' => $this->service->image ? url('assets/services').'/'.$this->service->image : '',
            'venderId' => $this->vender_id ? $this->vender_id : 0,
            'serviceName' => $this->service_name ? $this->service_name : '',
            'canReview' => $canReview ? 0 : 1,
            'status' => $this->status->id,
            'bookingDate' => date('Y-m-d', strtotime( $this->booking_date)).' '.$this->slot_start_from
        ];
    }
}
