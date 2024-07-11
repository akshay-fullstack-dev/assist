<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Address extends Resource
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
            'userId' => $this->user_id,
            'placeId' => $this->place_id,
            'phone' => $this->phone ? $this->phone : $this->users->phone_number,
            'latitude' => $this->latitude ? $this->latitude : '',
            'longitude' => $this->longitude ? $this->longitude : '',
            'country' => $this->country ? $this->country : '',
            'city' => $this->city ? $this->city : '',
            'pincode' => $this->pincode ? $this->pincode : '',
            'addressType' => $this->address_type ? $this->address_type : '',
            'fullAddress' => $this->full_address ? $this->full_address : '',
        ];
    }
}
