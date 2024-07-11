<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Coupon extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'discountType' => $this->type ? $this->type : '',
            'discountAmount' => (float) $this->discount ? $this->discount : 0,
            'maxDiscountAmount' => $this->maxDiscountAmount ? $this->maxDiscountAmount : 0,
        ];
    }
}
