<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class BookedEquipment extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return  new Equipment($this->equipment);
        return [
            'id' => $this->id,
            'bookingId' => $this->booking_id,
            'equipmentId' => $this->equipment_id,
            'quantity' => (int) $this->quantity,
            'equipmentName' => $this->equipment_name ? $this->equipment_name : '',
            'equipmentPrice' => $this->price ? $this->price : '',
        ];
    }
}
