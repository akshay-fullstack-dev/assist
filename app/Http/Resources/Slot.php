<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Slot extends Resource
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
            'day' => $this->day,
            'slotFrom' => $this->slot_from,
            'slotTo' => $this->slot_to,
            
        ];
    }
}
